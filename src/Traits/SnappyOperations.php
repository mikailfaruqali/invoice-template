<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

/**
 * @method static static newInstance()
 */
trait SnappyOperations
{
    protected array $options = [];

    protected ?string $contentView = NULL;

    protected ?string $contentHtml = NULL;

    protected array $contentData = [];

    protected ?string $headerView = NULL;

    protected array $headerData = [];

    protected ?string $footerView = NULL;

    protected array $footerData = [];

    protected bool $useDefaultViewer = FALSE;

    public static function raw(string $view, array $data = [], array $options = [])
    {
        $instance = static::newInstance();

        $instance->setTimeout();
        $instance->setBinaryPath();

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
        ], $options);

        $view = Blade::render($view, $data);
        $contentTitle = $instance->extractTitleFromHtml($view);

        $pdfWrapper = SnappyPdf::loadHTML($view)
            ->setOption('disable-smart-shrinking', (bool) $config->disabled_smart_shrinking)
            ->setOption('margin-top', $config->margin_top)
            ->setOption('margin-right', $config->margin_right)
            ->setOption('margin-left', $config->margin_left)
            ->setOption('margin-bottom', $config->margin_bottom)
            ->setOption('orientation', $config->orientation);

        when(
            condition: $config->page_width,
            value: function () use ($pdfWrapper, $config) {
                $pdfWrapper->setOption('page-width', $config->page_width);
                $pdfWrapper->setOption('page-height', $config->page_height);
            },
            default: function () use ($pdfWrapper, $config) {
                $pdfWrapper->setOption('page-size', $config->paper_size);
            },
        );

        return $instance->renderViewer($pdfWrapper->output(), $contentTitle);
    }

    public function inline()
    {
        $template = $this->getTemplate();

        $orientation = request()->input('orientation', $template->orientation);

        $pdf = $this->render()
            ->setOption('disable-smart-shrinking', (bool) $template->disabled_smart_shrinking)
            ->setOption('margin-top', $template->margin_top)
            ->setOption('margin-right', $template->margin_right)
            ->setOption('margin-left', $template->margin_left)
            ->setOption('header-spacing', $template->header_space)
            ->setOption('footer-spacing', $template->footer_space)
            ->setOption('margin-bottom', $template->margin_bottom)
            ->setOption('page-size', $template->paper_size)
            ->setOption('orientation', $orientation);

        if ($this->useDefaultViewer) {
            return $pdf->inline($this->generateSecureFilename());
        }

        return $this->renderViewer($pdf->output(), $this->getContentTitle());
    }

    public function save()
    {
        $this->ensureDirectoryExist($this->generatePath());

        $template = $this->getTemplate();

        $orientation = request()->input('orientation', $template->orientation);

        $fullPath = sprintf('%s/%s', $this->generatePath(), $this->generateSecureFilename());

        $this->render()
            ->setOption('disable-smart-shrinking', (bool) $template->disabled_smart_shrinking)
            ->setOption('margin-top', $template->margin_top)
            ->setOption('margin-right', $template->margin_right)
            ->setOption('margin-left', $template->margin_left)
            ->setOption('header-spacing', $template->header_space)
            ->setOption('footer-spacing', $template->footer_space)
            ->setOption('margin-bottom', $template->margin_bottom)
            ->setOption('page-size', $template->paper_size)
            ->setOption('orientation', $orientation)
            ->save($fullPath);

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
        ]);

        return response($html)->header('Content-Type', 'text/html');
    }

    private function render()
    {
        $this->setTimeout();
        $this->setBinaryPath();
        $this->loadTemplate();

        $this->contentHtml = $this->prepareContentHtml();

        $pdfWrapper = SnappyPdf::loadHTML($this->contentHtml);

        if ($headerTemplate = $this->prepareHeaderHtml()) {
            $pdfWrapper->setOption('header-html', $headerTemplate);
        }

        if ($footerTemplate = $this->prepareFooterHtml()) {
            $pdfWrapper->setOption('footer-html', $footerTemplate);
        }

        foreach ($this->configureOptions() as $option => $value) {
            $pdfWrapper->setOption($option, $value);
        }

        return $pdfWrapper;
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

    private function getLocaleDirection()
    {
        $direction = config('snawbar-invoice-template.locale-direction-key');

        if (is_callable($direction)) {
            return value($direction, $this->resolveLocale());
        }

        return session($direction);
    }

    private function setBinaryPath()
    {
        config(['snappy.pdf.binary' => config('snawbar-invoice-template.binary')[PHP_OS_FAMILY === 'Windows' ? 'windows' : 'linux']]);
    }

    private function setTimeout()
    {
        config(['snappy.pdf.timeout' => config('snawbar-invoice-template.timeout', 300)]);
    }

    private function configureOptions()
    {
        return array_merge($this->options, config('snawbar-invoice-template.options'));
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
