<?php

namespace Snawbar\InvoiceTemplate;

use Snawbar\InvoiceTemplate\Traits\DatabaseOperations;
use Snawbar\InvoiceTemplate\Traits\SnappyOperations;
use Snawbar\InvoiceTemplate\Traits\TwigOperations;

class InvoiceTemplate
{
    use DatabaseOperations;
    use SnappyOperations;
    use TwigOperations;

    private static $template;

    private static $extension = [];

    private static $placeholderData = [];

    public static function make($template = '*')
    {
        static::$template = self::getTemplateFromDatabase($template);

        return new static;
    }

    public static function makeTest($templateId)
    {
        static::$template = self::getTemplateById($templateId);

        return new static;
    }

    public static function withData($data)
    {
        static::$placeholderData = array_merge(static::$placeholderData, $data);

        return new static;
    }

    public static function setExtension($extension)
    {
        $extensions = is_array($extension) ? $extension : [$extension];

        static::$extension = $extensions;

        return new static;
    }

    private static function getTemplate()
    {
        return static::$template;
    }

    private static function getPlaceholderData()
    {
        return static::$placeholderData;
    }

    private static function getExtension()
    {
        return static::$extension;
    }
}
