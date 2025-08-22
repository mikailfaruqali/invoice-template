<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\File;

trait SnappyOperations
{
    protected static $options = [];

    protected static $view;

    protected static $data = [];

    public static function inline()
    {
        $template = self::getTemplate();

        return self::render()
            ->setOption('margin-top', $template->margin_top)
            ->setOption('margin-right', $template->margin_right)
            ->setOption('margin-left', $template->margin_left)
            ->setOption('header-spacing', $template->header_space)
            ->setOption('footer-spacing', $template->footer_space)
            ->setOption('margin-bottom', $template->margin_bottom)
            ->setOption('page-size', $template->paper_size)
            ->setOption('orientation', $template->orientation)
            ->setOption('page-height', $template->height)
            ->setOption('page-width', $template->width)
            ->inline(self::generateSecureFilename());
    }

    public static function save()
    {
        self::ensureDirectoryExist(self::generatePath());

        $template = self::getTemplate();

        $fullPath = sprintf('%s/%s', self::generatePath(), self::generateSecureFilename());

        return self::render()
            ->setOption('margin-top', $template->margin_top)
            ->setOption('margin-right', $template->margin_right)
            ->setOption('margin-left', $template->margin_left)
            ->setOption('header-spacing', $template->header_space)
            ->setOption('footer-spacing', $template->footer_space)
            ->setOption('margin-bottom', $template->margin_bottom)
            ->setOption('page-size', $template->paper_size)
            ->setOption('orientation', $template->orientation)
            ->setOption('page-height', $template->height)
            ->setOption('page-width', $template->width)
            ->save($fullPath);
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

    public static function view($view, $data = [])
    {
        self::$view = $view;
        self::$data = $data;

        return new static;
    }

    private static function render()
    {
        self::setBinaryPath();
        self::loadTemplate();

        $pdfWrapper = SnappyPdf::loadHTML(self::prepareHtml());

        if ($headerTemplate = self::getHeaderTemplate()) {
            $pdfWrapper->setOption('header-html', $headerTemplate);
        }

        if ($footerTemplate = self::getFooterTemplate()) {
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
        return sprintf('%s_%s.pdf', self::getTemplate()->page, now()->format('Y-m-d_H-i-s'));
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

    private static function prepareHtml()
    {
        return self::getContentTemplate() ?: view(self::$view, self::$data)->render();
    }
}
