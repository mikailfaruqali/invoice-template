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

    private static $routeName;

    private static $placeholderData = [];

    public static function route($routeName)
    {
        static::$routeName = $routeName;

        return new static;
    }

    public static function withData($data)
    {
        static::$placeholderData = array_merge(static::$placeholderData, $data);

        return new static;
    }

    private static function getRouteName()
    {
        return static::$routeName ?? request()->route()->getName();
    }

    private static function getPlaceholderData()
    {
        return static::$placeholderData;
    }
}
