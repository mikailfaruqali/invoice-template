<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Illuminate\Support\Facades\Blade;

trait BladeOperations
{
    private static $headerTemplate;

    private static $contentTemplate;

    private static $footerTemplate;

    private static $disableHeaderTemplate;

    private static $disabledFooterTemplate;

    public static function directPrint($templateName, $fallbackView, $data = [])
    {
        return self::renderTemplate(optional(self::getTemplateFromDatabase($templateName))->content ?: $fallbackView, $data);
    }

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

        self::$headerTemplate = self::renderTemplate($template->header, self::getHeaderData());
        self::$contentTemplate = self::renderTemplate($template->content, self::getContentData());
        self::$footerTemplate = self::renderTemplate($template->footer, self::getFooterData());
    }

    private static function renderTemplate($template, $data = [])
    {
        return Blade::render($template, $data);
    }
}
