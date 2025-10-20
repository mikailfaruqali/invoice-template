<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\File;

trait SnappyOperations
{
    protected static $options = [];

    protected static $contentView;

    protected static $contentHtml;

    protected static $contentData = [];

    protected static $headerView;

    protected static $headerData = [];

    protected static $footerView;

    protected static $footerData = [];

    public static function inline()
    {
        $template = self::getTemplate();

        $orientation = request()->input('orientation', $template->orientation);

        return self::render()
            ->setOption('disable-smart-shrinking', (bool) $template->disabled_smart_shrinking)
            ->setOption('margin-top', $template->margin_top)
            ->setOption('margin-right', $template->margin_right)
            ->setOption('margin-left', $template->margin_left)
            ->setOption('header-spacing', $template->header_space)
            ->setOption('footer-spacing', $template->footer_space)
            ->setOption('margin-bottom', $template->margin_bottom)
            ->setOption('page-size', $template->paper_size)
            ->setOption('orientation', $orientation)
            ->inline(self::generateSecureFilename());
    }

    public static function save()
    {
        self::ensureDirectoryExist(self::generatePath());

        $template = self::getTemplate();

        $orientation = request()->input('orientation', $template->orientation);

        $fullPath = sprintf('%s/%s', self::generatePath(), self::generateSecureFilename());

        self::render()
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

    public static function setOption($key, $value)
    {
        self::$options[$key] = $value;

        return new static;
    }

    public static function setOptions($options)
    {
        self::$options = $options;

        return new static;
    }

    public static function renderContent($view)
    {
        self::$contentView = $view;

        return new static;
    }

    public static function contentData($data = [])
    {
        self::$contentData = $data;

        return new static;
    }

    public static function renderHeader($view)
    {
        self::$headerView = $view;

        return new static;
    }

    public static function headerData($data = [])
    {
        self::$headerData = $data;

        return new static;
    }

    public static function renderFooter($view)
    {
        self::$footerView = $view;

        return new static;
    }

    public static function footerData($data = [])
    {
        self::$footerData = $data;

        return new static;
    }

    private static function render()
    {
        self::setBinaryPath();
        self::loadTemplate();

        self::$contentHtml = self::prepareContentHtml();

        $pdfWrapper = SnappyPdf::loadHTML(self::$contentHtml);

        if ($headerTemplate = self::prepareHeaderHtml()) {
            $pdfWrapper->setOption('header-html', $headerTemplate);
        }

        if ($footerTemplate = self::prepareFooterHtml()) {
            $pdfWrapper->setOption('footer-html', $footerTemplate);
        }

        foreach (self::configureOptions() as $option => $value) {
            $pdfWrapper->setOption($option, $value);
        }

        return $pdfWrapper;
    }

    private static function generatePath()
    {
        return public_path(sprintf('files/%s/pdf', request()->getHost()));
    }

    private static function generateSecureFilename()
    {
        return sprintf('%s_%s.pdf', now()->format('Y-m-d_H-i-s'), self::getContentTitle() ?: bin2hex(random_bytes(8)));
    }

    private static function getFont()
    {
        return self::normalizePath(sprintf('%s/%s', config('snawbar-invoice-template.font-dir'), config('snawbar-invoice-template.font')));
    }

    private static function getLocaleDirection()
    {
        return session(config('snawbar-invoice-template.locale-direction-key'));
    }

    private static function setBinaryPath()
    {
        config(['snappy.pdf.binary' => config('snawbar-invoice-template.binary')[PHP_OS_FAMILY === 'Windows' ? 'windows' : 'linux']]);
    }

    private static function configureOptions()
    {
        return array_merge(self::$options, config('snawbar-invoice-template.options'));
    }

    private static function normalizePath($path)
    {
        return str_replace('\\', '/', $path);
    }

    private static function ensureDirectoryExist($path)
    {
        if (File::missing($path)) {
            File::makeDirectory($path, 0755, TRUE, TRUE);
        }
    }

    private static function prepareContentHtml()
    {
        abort_if(blank(self::getContentTemplate()) && blank(self::$contentView), 500, 'Content view or data must be provided to generate PDF.');

        return self::getContentTemplate() ?: view(self::$contentView, self::getContentData())->render();
    }

    private static function prepareHeaderHtml()
    {
        if (self::getDisableHeaderTemplate() || (blank(self::getHeaderTemplate()) && blank(self::$headerView))) {
            return NULL;
        }

        return self::getHeaderTemplate() ?: view(self::$headerView, self::getHeaderData())->render();
    }

    private static function prepareFooterHtml()
    {
        if (self::getDisabledFooterTemplate() || (blank(self::getFooterTemplate()) && blank(self::$footerView))) {
            return NULL;
        }

        return self::getFooterTemplate() ?: view(self::$footerView, self::getFooterData())->render();
    }

    private static function getContentData()
    {
        return array_merge(self::$contentData, self::getTemplateDefaultData());
    }

    private static function getHeaderData()
    {
        return array_merge(self::$headerData, self::getTemplateDefaultData());
    }

    private static function getFooterData()
    {
        return array_merge(self::$footerData, self::getTemplateDefaultData());
    }

    private static function getTemplateDefaultData()
    {
        $template = self::getTemplate();

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

    private static function getContentTitle()
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', self::$contentHtml, $matches)) {
            $title = html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return preg_replace('/[^\p{L}\p{N}\s_-]+/u', '', trim($title));
        }

        return NULL;
    }
}
