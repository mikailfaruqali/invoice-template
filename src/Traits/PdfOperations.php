<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * @method static static newInstance()
 */
trait PdfOperations
{
    protected array $options = [];

    protected ?string $contentView = NULL;

    protected ?string $contentHtml = NULL;

    protected array $contentData = [];

    protected ?string $headerView = NULL;

    protected array $headerData = [];

    protected ?string $footerView = NULL;

    protected array $footerData = [];

    protected ?string $pageNumberView = NULL;

    protected array $pageNumberData = [];

    protected ?string $watermarkView = NULL;

    protected array $watermarkData = [];

    protected bool $useDefaultViewer = FALSE;

    protected array $cssFiles = [];

    protected array $cssVariables = [];

    protected int $copies = 1;

    public static function raw(string $view, array $data = [], array $options = [])
    {
        $instance = static::newInstance();

        $config = (object) array_merge([
            'disabled_smart_shrinking' => TRUE,
            'margin_top' => 0,
            'margin_right' => 0,
            'margin_left' => 0,
            'margin_bottom' => 0,
            'page_width' => NULL,
            'page_height' => '297',
            'paper_size' => 'A4',
            'orientation' => 'portrait',
            'copies' => 1,
        ], $options);

        $view = Blade::render($view, $data);
        $contentTitle = $instance->extractTitleFromHtml($view);

        $pdf = $instance->generatePdf(['content' => $view], [
            'disable-smart-shrinking' => (bool) $config->disabled_smart_shrinking,
            'margin-top' => $config->margin_top,
            'margin-right' => $config->margin_right,
            'margin-left' => $config->margin_left,
            'margin-bottom' => $config->margin_bottom,
            'orientation' => $config->orientation,
            'copies' => $config->copies,
            ...($config->page_width
                ? ['page-width' => $config->page_width, 'page-height' => $config->page_height]
                : ['page-size' => $config->paper_size]),
        ]);

        return $instance->renderViewer($pdf, $contentTitle);
    }

    public static function binaryPath(): string
    {
        $binary = config('snawbar-invoice-template.binary');

        return match (TRUE) {
            is_string($binary) && filled($binary) => $binary,
            default => storage_path(sprintf('invoice-template/%s', PHP_OS_FAMILY === 'Windows' ? 'invoice-pdf.exe' : 'invoice-pdf')),
        };
    }

    public static function theme(): string
    {
        $theme = config('snawbar-invoice-template.theme', 'dark');

        if ($theme instanceof Closure || is_array($theme)) {
            $theme = call_user_func($theme, auth()->user());
        }

        return match (TRUE) {
            $theme === TRUE, $theme === 'dark' => 'dark',
            default => 'light',
        };
    }

    public static function favicon(): ?string
    {
        $favicon = config('snawbar-invoice-template.favicon');

        if (blank($favicon)) {
            return NULL;
        }

        if (! is_file($favicon) || ! is_readable($favicon)) {
            return $favicon;
        }

        $mime = match (strtolower(pathinfo($favicon, PATHINFO_EXTENSION))) {
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/png',
        };

        return sprintf('data:%s;base64,%s', $mime, base64_encode(file_get_contents($favicon)));
    }

    public function inline()
    {
        $pdf = $this->render();

        if ($this->useDefaultViewer) {
            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', sprintf('inline; filename="%s"', $this->generateSecureFilename()));
        }

        return $this->renderViewer($pdf, $this->getContentTitle());
    }

    public function save()
    {
        $this->ensureDirectoryExist($this->generatePath());

        $fullPath = sprintf('%s/%s', $this->generatePath(), $this->generateSecureFilename());

        File::put($fullPath, $this->render());

        return $fullPath;
    }

    public function useDefaultViewer(): static
    {
        $this->useDefaultViewer = TRUE;

        return $this;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    public function renderContent($view)
    {
        $this->contentView = $view;

        return $this;
    }

    public function contentData($data = [])
    {
        $this->contentData = $data;

        return $this;
    }

    public function renderHeader($view)
    {
        $this->headerView = $view;

        return $this;
    }

    public function headerData($data = [])
    {
        $this->headerData = $data;

        return $this;
    }

    public function renderFooter($view)
    {
        $this->footerView = $view;

        return $this;
    }

    public function footerData($data = [])
    {
        $this->footerData = $data;

        return $this;
    }

    public function renderPageNumber($view)
    {
        $this->pageNumberView = $view;

        return $this;
    }

    public function pageNumberData($data = [])
    {
        $this->pageNumberData = $data;

        return $this;
    }

    public function renderWatermark($view)
    {
        $this->watermarkView = $view;

        return $this;
    }

    public function watermarkData($data = [])
    {
        $this->watermarkData = $data;

        return $this;
    }

    public function cssFile(string $path)
    {
        $path = $this->normalizePath($path);

        if (! in_array($path, $this->cssFiles, TRUE)) {
            $this->cssFiles[] = $path;
        }

        return $this;
    }

    public function cssFiles(array $paths)
    {
        foreach ($paths as $path) {
            $this->cssFile($path);
        }

        return $this;
    }

    public function copies(int $copies)
    {
        $this->copies = max(1, $copies);

        return $this;
    }

    public function cssVariable(string $name, $value)
    {
        $name = $this->normalizeCssVariableName($name);

        if (blank($name)) {
            return $this;
        }

        if (blank($value)) {
            unset($this->cssVariables[$name]);

            return $this;
        }

        $this->cssVariables[$name] = $this->normalizeCssVariableValue((string) $value);

        return $this;
    }

    public function cssVariables(array $variables)
    {
        foreach ($variables as $name => $value) {
            $this->cssVariable((string) $name, $value);
        }

        return $this;
    }

    private function renderViewer(string $pdfBytes, $title): mixed
    {
        $fontDetails = $this->resolveFontDetails();

        $html = Blade::render('snawbar-invoice-template::pdf-viewer', [
            'font' => $fontDetails['base64'],
            'fontFamily' => $fontDetails['family'],
            'fontStack' => $fontDetails['stack'],
            'filename' => $this->generateSecureFilename(),
            'base64' => base64_encode($pdfBytes),
            'dir' => $this->getLocaleDirection(),
            'title' => $title,
            'favicon' => static::favicon(),
            'theme' => static::theme(),
        ]);

        return response($html)->header('Content-Type', 'text/html');
    }

    private function render()
    {
        $this->loadTemplate();

        $this->contentHtml = $this->prepareContentHtml();

        $template = $this->getTemplate();

        return $this->generatePdf(array_map(fn ($html) => $this->injectCssVariables($this->injectSharedCss($html)), [
            'content' => $this->contentHtml,
            'header-html' => $this->prepareHeaderHtml(),
            'footer-html' => $this->prepareFooterHtml(),
            'page-number-html' => $this->preparePageNumberHtml(),
            'watermark-html' => $this->prepareWatermarkHtml(),
        ]), array_merge($this->options, [
            'disable-smart-shrinking' => (bool) $template->disabled_smart_shrinking,
            'margin-top' => $template->margin_top,
            'margin-right' => $template->margin_right,
            'margin-left' => $template->margin_left,
            'header-spacing' => $template->header_space,
            'footer-spacing' => $template->footer_space,
            'margin-bottom' => $template->margin_bottom,
            'page-size' => $template->paper_size,
            'orientation' => request()->input('orientation', $template->orientation),
            'watermark-opacity' => data_get($template, 'watermark_opacity'),
            'header-first-page-only' => (bool) data_get($template, 'header_first_page_only', FALSE),
            'footer-last-page-only' => (bool) data_get($template, 'footer_last_page_only', FALSE),
            'page-number-height' => data_get($template, 'page_number_space', 8),
            'copies' => $this->copies,
        ]));
    }

    private function generatePdf(array $documents, array $options): string
    {
        $temporaryFiles = [];

        try {
            $command = [$this->resolveBinaryPath(), '--output', '-', '--quiet'];

            if (filled($timeout = $this->getTimeout())) {
                $command[] = '--timeout';
                $command[] = (string) $timeout;
            }

            if (filled($chrome = config('snawbar-invoice-template.chrome'))) {
                $command[] = '--chrome';
                $command[] = $chrome;
            }

            foreach (array_filter($documents, 'filled') as $document => $html) {
                $temporaryFiles[] = $path = $this->createTemporaryFile($html);
                $command[] = sprintf('--%s', $document);
                $command[] = $path;
            }

            foreach (Arr::only($options, $this->engineOptions()) as $option => $value) {
                if (is_bool($value)) {
                    if ($value) {
                        $command[] = sprintf('--%s', $option);
                    }

                    continue;
                }

                if (filled($value)) {
                    $command[] = sprintf('--%s', $option);
                    $command[] = (string) $value;
                }
            }

            $process = new Process($command);
            $process->setTimeout($timeout);
            $process->run();

            throw_unless($process->isSuccessful(), RuntimeException::class, sprintf('Failed to generate PDF: %s', mb_trim($process->getErrorOutput())));

            return $process->getOutput();
        } finally {
            File::delete($temporaryFiles);
        }
    }

    private function engineOptions(): array
    {
        return [
            'page-size',
            'page-width',
            'page-height',
            'orientation',
            'margin-top',
            'margin-bottom',
            'margin-left',
            'margin-right',
            'header-spacing',
            'footer-spacing',
            'disable-smart-shrinking',
            'zoom',
            'watermark-opacity',
            'header-first-page-only',
            'footer-last-page-only',
            'page-number-height',
            'copies',
        ];
    }

    private function resolveBinaryPath(): string
    {
        $binary = static::binaryPath();

        throw_unless(is_file($binary), RuntimeException::class, sprintf('PDF engine not found at [%s]. Run "php artisan invoice-template:install".', $binary));

        return $binary;
    }

    private function injectSharedCss($html)
    {
        if (blank($html) || blank($this->cssFiles)) {
            return $html;
        }

        $style = sprintf('<style>%s</style>', implode("\n", array_map(fn ($path) => $this->readCssFile($path), $this->cssFiles)));

        return match (TRUE) {
            preg_match('/<head\b[^>]*>/i', $html) === 1 => preg_replace('/<head\b[^>]*>/i', '$0' . addcslashes($style, '\\$'), $html, 1),
            preg_match('/<html\b[^>]*>/i', $html) === 1 => preg_replace('/<html\b[^>]*>/i', '$0<head>' . addcslashes($style, '\\$') . '</head>', $html, 1),
            default => $style . $html,
        };
    }

    private function injectCssVariables($html)
    {
        if (blank($html) || blank($this->cssVariables)) {
            return $html;
        }

        $declarations = collect($this->cssVariables)->map(fn ($value, $name) => sprintf('%s:%s !important;', $name, $value))->implode('');

        $style = sprintf('<style>:root{%1$s}:root:root:root{%1$s}</style>', $declarations);

        return match (TRUE) {
            preg_match('/<\/body\s*>/i', $html) === 1 => preg_replace('/<\/body\s*>/i', addcslashes($style, '\\$') . '$0', $html, 1),
            preg_match('/<\/html\s*>/i', $html) === 1 => preg_replace('/<\/html\s*>/i', addcslashes($style, '\\$') . '$0', $html, 1),
            default => $html . $style,
        };
    }

    private function normalizeCssVariableName(string $name): ?string
    {
        $name = mb_trim(preg_replace('/[^A-Za-z0-9_-]+/', '-', ltrim(mb_trim($name), '-')), '-');

        return filled($name) ? sprintf('--%s', $name) : NULL;
    }

    private function normalizeCssVariableValue(string $value): string
    {
        return mb_trim(preg_replace('/[\r\n]+/', ' ', str_replace(['</', '{', '}', ';'], '', $value)));
    }

    private function readCssFile(string $path): string
    {
        throw_unless(is_file($path) && is_readable($path), RuntimeException::class, sprintf('CSS file [%s] does not exist or is not readable.', $path));

        $directory = dirname($path);

        return preg_replace_callback('/url\(\s*([\'"]?)([^\'")]+)\1\s*\)/i', function ($matches) use ($directory) {
            $url = mb_trim($matches[2]);

            if (preg_match('#^(?:[a-z][a-z0-9+.-]*:|/|\\\\|\#)#i', $url) === 1) {
                return $matches[0];
            }

            return sprintf('url("%s/%s")', $directory, preg_replace('#^(\./)+#', '', $url));
        }, file_get_contents($path));
    }

    private function createTemporaryFile(string $html): string
    {
        $path = tempnam(sys_get_temp_dir(), 'invoice-template-');

        File::put($path, $html);

        return $path;
    }

    private function getTimeout()
    {
        return config('snawbar-invoice-template.timeout', 300);
    }

    private function generatePath()
    {
        return public_path(sprintf('files/%s/pdf', request()->getHost()));
    }

    private function generateSecureFilename()
    {
        return sprintf('%s_%s.pdf', now()->format('Y-m-d_H-i-s'), $this->getContentTitle() ?: bin2hex(random_bytes(8)));
    }

    private function resolveLocale(): string
    {
        return match (TRUE) {
            property_exists($this, 'template') && isset($this->template->lang) && $this->template->lang !== '*' => (string) $this->template->lang,
            default => (string) app()->getLocale(),
        };
    }

    private function resolveFontValue($configValue, string $locale)
    {
        return match (is_array($configValue)) {
            TRUE => $configValue[$locale] ?? $configValue['default'] ?? $configValue['*'] ?? NULL,
            FALSE => $configValue,
        };
    }

    private function resolveFontDetails(): array
    {
        $locale = $this->resolveLocale();
        $fontPath = $this->getFont();
        $fontBase64 = NULL;

        $explicitStack = $this->resolveFontValue(config('snawbar-invoice-template.font-family'), $locale);
        $fontVal = $this->resolveFontValue(config('snawbar-invoice-template.font'), $locale);

        $primaryFamily = match (TRUE) {
            filled($explicitStack) => trim(explode(',', $explicitStack)[0], " '\""),
            filled($fontPath) => pathinfo($fontPath, PATHINFO_FILENAME),
            is_string($fontVal) && filled($fontVal) => $fontVal,
            default => 'system-ui',
        };

        $fontStack = match (TRUE) {
            filled($explicitStack) => $explicitStack,
            default => sprintf("'%s', system-ui, sans-serif", $primaryFamily),
        };

        if (filled($fontPath) && is_file($fontPath) && is_readable($fontPath)) {
            $fontBase64 = base64_encode(file_get_contents($fontPath));
        }

        return [
            'base64' => $fontBase64,
            'family' => $primaryFamily,
            'stack' => $fontStack,
        ];
    }

    private function getFont(): ?string
    {
        $locale = $this->resolveLocale();
        $fontDir = $this->resolveFontValue(config('snawbar-invoice-template.font-dir'), $locale);
        $font = $this->resolveFontValue(config('snawbar-invoice-template.font'), $locale);

        if (blank($font)) {
            return NULL;
        }

        if (is_string($font) && (is_file($font) || file_exists($font))) {
            return $this->normalizePath($font);
        }

        if (blank($fontDir)) {
            return NULL;
        }

        $fullPath = $this->normalizePath(sprintf('%s/%s', rtrim((string) $fontDir, '/\\'), ltrim((string) $font, '/\\')));

        return match (is_file($fullPath)) {
            TRUE => $fullPath,
            FALSE => NULL,
        };
    }

    private function getLocaleDirection(): string
    {
        $direction = config('snawbar-invoice-template.locale-direction-key');

        if ($direction instanceof Closure || is_array($direction)) {
            return (string) call_user_func($direction, $this->resolveLocale());
        }

        return (string) session($direction, 'ltr');
    }

    private function normalizePath($path)
    {
        return str_replace('\\', '/', $path);
    }

    private function ensureDirectoryExist($path)
    {
        if (File::missing($path)) {
            File::makeDirectory($path, 0755, TRUE, TRUE);
        }
    }

    private function prepareContentHtml()
    {
        abort_if(blank($this->getContentTemplate()) && blank($this->contentView), 500, 'Content view or data must be provided to generate PDF.');

        return $this->getContentTemplate() ?: view($this->contentView, $this->getContentData())->render();
    }

    private function prepareHeaderHtml()
    {
        if ($this->getDisableHeaderTemplate() || (blank($this->getHeaderTemplate()) && blank($this->headerView))) {
            return NULL;
        }

        return $this->getHeaderTemplate() ?: view($this->headerView, $this->getHeaderData())->render();
    }

    private function prepareFooterHtml()
    {
        if ($this->getDisabledFooterTemplate() || (blank($this->getFooterTemplate()) && blank($this->footerView))) {
            return NULL;
        }

        return $this->getFooterTemplate() ?: view($this->footerView, $this->getFooterData())->render();
    }

    private function preparePageNumberHtml()
    {
        if ($this->getDisabledPageNumberTemplate() || (blank($this->getPageNumberTemplate()) && blank($this->pageNumberView))) {
            return NULL;
        }

        return $this->getPageNumberTemplate() ?: view($this->pageNumberView, $this->getPageNumberData())->render();
    }

    private function prepareWatermarkHtml()
    {
        if ($this->getDisabledWatermarkTemplate() || (blank($this->getWatermarkTemplate()) && blank($this->watermarkView))) {
            return NULL;
        }

        return $this->getWatermarkTemplate() ?: view($this->watermarkView, $this->getWatermarkData())->render();
    }

    private function getContentData()
    {
        return array_merge($this->contentData, $this->getTemplateDefaultData());
    }

    private function getHeaderData()
    {
        return array_merge($this->headerData, $this->getTemplateDefaultData());
    }

    private function getFooterData()
    {
        return array_merge($this->footerData, $this->getTemplateDefaultData());
    }

    private function getPageNumberData()
    {
        return array_merge($this->pageNumberData, $this->getTemplateDefaultData());
    }

    private function getWatermarkData()
    {
        return array_merge($this->contentData, $this->watermarkData, $this->getTemplateDefaultData());
    }

    private function getTemplateDefaultData()
    {
        $template = $this->getTemplate();

        return [
            'marginTop' => $template->margin_top,
            'marginRight' => $template->margin_right,
            'marginLeft' => $template->margin_left,
            'headerSpace' => $template->header_space,
            'footerSpace' => $template->footer_space,
            'marginBottom' => $template->margin_bottom,
            'pageSize' => $template->paper_size,
            'orientation' => $template->orientation,
        ];
    }

    private function getContentTitle()
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $this->contentHtml, $matches)) {
            $title = html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return preg_replace('/[^\p{L}\p{N}\s_-]+/u', '', mb_trim($title));
        }

        return NULL;
    }

    private function extractTitleFromHtml(string $html): ?string
    {
        preg_match('/<title>(.*?)<\/title>/i', $html, $matches);

        return $matches[1] ?? NULL;
    }
}
