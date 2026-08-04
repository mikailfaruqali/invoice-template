<?php

namespace Snawbar\InvoiceTemplate;

use Illuminate\Support\Traits\Conditionable;
use Snawbar\InvoiceTemplate\Traits\BladeOperations;
use Snawbar\InvoiceTemplate\Traits\DatabaseOperations;
use Snawbar\InvoiceTemplate\Traits\SnappyOperations;

class InvoiceTemplate
{
    use BladeOperations;
    use Conditionable;
    use DatabaseOperations;
    use SnappyOperations;

    private object $template;

    public static function make($template = '*')
    {
        $instance = static::newInstance();

        $instance->template = $instance->getTemplateFromDatabase($template);

        return $instance;
    }

    protected static function newInstance(): static
    {
        return new static;
    }

    private function getTemplate()
    {
        return $this->template;
    }
}
