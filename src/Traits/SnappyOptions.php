<?php

namespace Snawbar\InvoiceTemplate\Traits;

trait SnappyOptions
{
    protected static $options = [];

    public static function setOption($option, $value)
    {
        self::$options[$option] = $value;

        return new static;
    }

    public static function setOptions(array $options)
    {
        self::$options = array_merge(self::$options, $options);

        return new static;
    }

    public static function portrait()
    {
        self::$options['orientation'] = 'Portrait';

        return new static;
    }

    public static function landscape()
    {
        self::$options['orientation'] = 'Landscape';

        return new static;
    }

    public static function letter()
    {
        self::$options['page-size'] = 'Letter';

        return new static;
    }

    public static function legal()
    {
        self::$options['page-size'] = 'Legal';

        return new static;
    }

    public static function width($width)
    {
        self::$options['page-width'] = $width;

        return new static;
    }

    public static function height($height)
    {
        self::$options['page-height'] = $height;

        return new static;
    }

    public static function marginTop($margin)
    {
        self::$options['margin-top'] = $margin;

        return new static;
    }

    public static function marginRight($margin)
    {
        self::$options['margin-right'] = $margin;

        return new static;
    }

    public static function marginBottom($margin)
    {
        self::$options['margin-bottom'] = $margin;

        return new static;
    }

    public static function marginLeft($margin)
    {
        self::$options['margin-left'] = $margin;

        return new static;
    }

    public static function headerSpacing($spacing)
    {
        self::$options['header-spacing'] = $spacing;

        return new static;
    }

    public static function noHeaderSpacing()
    {
        self::$options['header-spacing'] = '0';

        return new static;
    }

    public static function footerSpacing($spacing)
    {
        self::$options['footer-spacing'] = $spacing;

        return new static;
    }

    public static function noFooterSpacing()
    {
        self::$options['footer-spacing'] = '0';

        return new static;
    }

    public static function headerLine()
    {
        self::$options['header-line'] = TRUE;

        return new static;
    }

    public static function noHeaderLine()
    {
        self::$options['header-line'] = FALSE;

        return new static;
    }

    public static function footerLine()
    {
        self::$options['footer-line'] = TRUE;

        return new static;
    }

    public static function noFooterLine()
    {
        self::$options['footer-line'] = FALSE;

        return new static;
    }

    public static function highQuality()
    {
        self::$options['lowquality'] = FALSE;
        self::$options['dpi'] = 300;
        self::$options['image-dpi'] = 300;
        self::$options['image-quality'] = 94;

        return new static;
    }

    public static function mediumQuality()
    {
        self::$options['lowquality'] = FALSE;
        self::$options['dpi'] = 150;
        self::$options['image-dpi'] = 150;
        self::$options['image-quality'] = 75;

        return new static;
    }

    public static function lowQuality()
    {
        self::$options['lowquality'] = TRUE;
        self::$options['dpi'] = 72;
        self::$options['image-dpi'] = 72;
        self::$options['image-quality'] = 50;

        return new static;
    }

    private static function setBinaryPath()
    {
        config(['snappy.pdf.binary' => config('snawbar-invoice-template.binary')[PHP_OS_FAMILY === 'Windows' ? 'windows' : 'linux']]);
    }

    private static function configureOptions()
    {
        return array_merge(config('snawbar-invoice-template.options'), self::$options);
    }
}
