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
        self::$template = self::getTemplateFromDatabase($template);

        return new static;
    }

    private static function getTemplate()
    {
        return self::$template;
    }
}
