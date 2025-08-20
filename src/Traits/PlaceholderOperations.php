<?php

namespace Snawbar\InvoiceTemplate\Traits;

trait PlaceholderOperations
{
    protected static $placeholderData = [];

    protected static $headerTemplate;

    protected static $contentTemplate;

    protected static $footerTemplate;

    private static function getHeaderTemplate()
    {
        return self::$headerTemplate;
    }

    private static function getContentTemplate()
    {
        return self::$contentTemplate;
    }

    private static function getFooterTemplate()
    {
        return self::$footerTemplate;
    }

    private static function loadTemplate($lang = 'en')
    {
        $template = self::getTemplate(self::getRouteName(), $lang);

        self::$headerTemplate = self::replacePlaceholders($template->header);
        self::$contentTemplate = self::replacePlaceholders($template->content);
        self::$footerTemplate = self::replacePlaceholders($template->footer);
    }

    private static function replacePlaceholders($htmlPart)
    {
        foreach (static::$placeholderData as $key => $value) {
            $htmlPart = str_replace(sprintf('{%s}', $key), $value, $htmlPart);
        }

        return $htmlPart;
    }
}
