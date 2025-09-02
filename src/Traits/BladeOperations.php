<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Illuminate\Support\Facades\Blade;

trait BladeOperations
{
    private static $headerTemplate;

    private static $disableHeaderTemplate;

    private static $contentTemplate;

    private static $footerTemplate;

    private static $disabledFooterTemplate;

    private static function getDisableHeaderTemplate()
    {
        return self::$disableHeaderTemplate;
    }

    private static function getHeaderTemplate()
    {
        return self::$headerTemplate;
    }

    private static function getContentTemplate()
    {
        return self::$contentTemplate;
    }

    private static function getDisabledFooterTemplate()
    {
        return self::$disabledFooterTemplate;
    }

    private static function getFooterTemplate()
    {
        return self::$footerTemplate;
    }

    private static function loadTemplate()
    {
        $template = self::getTemplate();

        self::$disableHeaderTemplate = $template->disable_header;
        self::$disabledFooterTemplate = $template->disable_footer;

        self::$headerTemplate = Blade::render($template->header, self::getHeaderData());
        self::$contentTemplate = Blade::render($template->content, self::getContentData());
        self::$footerTemplate = Blade::render($template->footer, self::getFooterData());
    }
}
