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
            ->marginTop($template->margin_top)
            ->marginRight($template->margin_right)
            ->marginLeft($template->margin_left)
            ->headerSpacing($template->header_space)
            ->mediumQuality()
            ->footerSpacing($template->footer_space)
            ->marginBottom($template->margin_bottom)
            ->inline(self::generateSecureFilename());
    }

    public static function save()
    {
        self::ensureDirectoryExist(self::generatePath());

        $template = self::getTemplate();

        $fullPath = sprintf('%s/%s', self::generatePath(), self::generateSecureFilename());

        self::generate()
            ->marginTop($template->margin_top)
            ->marginRight($template->margin_right)
            ->marginLeft($template->margin_left)
            ->headerSpacing($template->header_space)
            ->mediumQuality()
            ->footerSpacing($template->footer_space)
            ->marginBottom($template->margin_bottom)
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

    private static function ensureDirectoryExist($path)
    {
        if (File::missing($path)) {
            File::makeDirectory($path, 0755, TRUE, TRUE);
        }
    }
}
