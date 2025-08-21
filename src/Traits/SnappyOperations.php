<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\File;

trait SnappyOperations
{
    public static function inline()
    {
        $template = self::getTemplate();

        return self::generate()
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

        self::generate()
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

        return $fullPath;
    }

    private static function generate()
    {
        self::setBinaryPath();
        self::loadTemplate();

        $pdfWrapper = SnappyPdf::loadHTML(self::getContentTemplate());

        $pdfWrapper->setOption('header-html', self::getHeaderTemplate());
        $pdfWrapper->setOption('footer-html', self::getFooterTemplate());

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
        return config('snawbar-invoice-template.options');
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
}
