<?php

namespace Snawbar\InvoiceTemplate;

use Snawbar\InvoiceTemplate\Traits\BladeOperations;
use Snawbar\InvoiceTemplate\Traits\DatabaseOperations;
use Snawbar\InvoiceTemplate\Traits\SnappyOperations;

class InvoiceTemplate
{
    use BladeOperations;
    use DatabaseOperations;
    use SnappyOperations;

    private static $template;

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

    private static function getTemplate()
    {
        return static::$template;
    }
}
