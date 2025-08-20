<?php

namespace Snawbar\InvoiceTemplate;

use Snawbar\InvoiceTemplate\Traits\DatabaseOperations;
use Snawbar\InvoiceTemplate\Traits\PlaceholderOperations;
use Snawbar\InvoiceTemplate\Traits\SnappyOperations;
use Snawbar\InvoiceTemplate\Traits\SnappyOptions;

class InvoiceTemplate
{
    use DatabaseOperations;
    use PlaceholderOperations;
    use SnappyOperations;
    use SnappyOptions;

    protected static $routeName;

    protected static $placeholderData = [];

    public static function route(string $routeName): self
    {
        static::$routeName = $routeName;

        return new static;
    }

    public static function withData($data)
    {
        static::$placeholderData = array_merge(static::$placeholderData, $data);

        return new static;
    }

    protected static function getRouteName(): string
    {
        return static::$routeName ?? request()->route()->getName();
    }
}
