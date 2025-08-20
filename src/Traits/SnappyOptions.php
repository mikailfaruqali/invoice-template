<?php

namespace Snawbar\InvoiceTemplate\Traits;

trait SnappyOptions
{
    protected static $pdfOptions = [];

    public static function setPdfOption($option, $value)
    {
        self::$pdfOptions[$option] = $value;

        return new static;
    }

    public static function setPdfOptions(array $options)
    {
        self::$pdfOptions = array_merge(self::$pdfOptions, $options);

        return new static;
    }

    public static function a4()
    {
        self::$pdfOptions['page-size'] = 'a4';

        return new static;
    }

    public static function a5()
    {
        self::$pdfOptions['page-size'] = 'a5';

        return new static;
    }

    public static function a3()
    {
        self::$pdfOptions['page-size'] = 'a3';

        return new static;
    }

    public static function a11()
    {
        self::$pdfOptions['page-size'] = 'a11';

        return new static;
    }

    public static function portrait()
    {
        self::$pdfOptions['orientation'] = 'Portrait';

        return new static;
    }

    public static function landscape()
    {
        self::$pdfOptions['orientation'] = 'Landscape';

        return new static;
    }

    public static function letter()
    {
        self::$pdfOptions['page-size'] = 'Letter';

        return new static;
    }

    public static function legal()
    {
        self::$pdfOptions['page-size'] = 'Legal';

        return new static;
    }

    public static function width($width)
    {
        self::$pdfOptions['page-width'] = $width;

        return new static;
    }

    public static function height($height)
    {
        self::$pdfOptions['page-height'] = $height;

        return new static;
    }

    public static function marginTop($margin)
    {
        self::$pdfOptions['margin-top'] = $margin;

        return new static;
    }

    public static function marginRight($margin)
    {
        self::$pdfOptions['margin-right'] = $margin;

        return new static;
    }

    public static function marginBottom($margin)
    {
        self::$pdfOptions['margin-bottom'] = $margin;

        return new static;
    }

    public static function marginLeft($margin)
    {
        self::$pdfOptions['margin-left'] = $margin;

        return new static;
    }

    public static function headerSpacing($spacing)
    {
        self::$pdfOptions['header-spacing'] = $spacing;

        return new static;
    }

    public static function noHeaderSpacing()
    {
        self::$pdfOptions['header-spacing'] = '0';

        return new static;
    }

    public static function footerSpacing($spacing)
    {
        self::$pdfOptions['footer-spacing'] = $spacing;

        return new static;
    }

    public static function noFooterSpacing()
    {
        self::$pdfOptions['footer-spacing'] = '0';

        return new static;
    }

    public static function headerLine()
    {
        self::$pdfOptions['header-line'] = TRUE;

        return new static;
    }

    public static function noHeaderLine()
    {
        self::$pdfOptions['header-line'] = FALSE;

        return new static;
    }

    public static function footerLine()
    {
        self::$pdfOptions['footer-line'] = TRUE;

        return new static;
    }

    public static function noFooterLine()
    {
        self::$pdfOptions['footer-line'] = FALSE;

        return new static;
    }

    public static function highQuality()
    {
        self::$pdfOptions['lowquality'] = FALSE;
        self::$pdfOptions['dpi'] = 300;
        self::$pdfOptions['image-dpi'] = 300;
        self::$pdfOptions['image-quality'] = 94;

        return new static;
    }

    public static function mediumQuality()
    {
        self::$pdfOptions['lowquality'] = FALSE;
        self::$pdfOptions['dpi'] = 150;
        self::$pdfOptions['image-dpi'] = 150;
        self::$pdfOptions['image-quality'] = 75;

        return new static;
    }

    public static function lowQuality()
    {
        self::$pdfOptions['lowquality'] = TRUE;
        self::$pdfOptions['dpi'] = 72;
        self::$pdfOptions['image-dpi'] = 72;
        self::$pdfOptions['image-quality'] = 50;

        return new static;
    }

    private static function setBinaryPath()
    {
        config(['snappy.pdf.binary' => config('snawbar-invoice-template.binary')[PHP_OS_FAMILY === 'Windows' ? 'windows' : 'linux']]);
    }

    private static function configurePdfOptions()
    {
        return array_merge(config('snawbar-invoice-template.options'), self::$pdfOptions);
    }
}
