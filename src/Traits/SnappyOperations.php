<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\File;

trait SnappyOperations
{
    public static function inline()
    {
        return self::generate()->inline(self::generateSecureFilename());
    }

    public static function save()
    {
        self::ensureDirectoryExist(self::generatePath());

        $fullPath = sprintf('%s/%s.pdf', self::generatePath(), self::generateSecureFilename());

        self::generate()->save($fullPath);

        return $fullPath;
    }

    private static function generate()
    {
        self::setBinaryPath();
        self::loadTemplate(app()->getLocale());

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

    private static function generateSecureFilename(): string
    {
        return sprintf('%s_%s.pdf', self::getRouteName(), now()->format('Y-m-d_H-i-s'));
    }

    private static function ensureDirectoryExist($path)
    {
        if (File::missing($path)) {
            File::makeDirectory($path, 0755, TRUE, TRUE);
        }
    }
}
