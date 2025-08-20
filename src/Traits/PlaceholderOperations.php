<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Twig\Environment;
use Twig\Loader\ArrayLoader;

trait PlaceholderOperations
{
    private static $headerTemplate;

    private static $contentTemplate;

    private static $footerTemplate;

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

    private static function loadTemplate()
    {
        $template = self::getTemplate(self::getRouteName());

        $arrayLoader = new ArrayLoader([
            'header' => $template->header,
            'content' => $template->content,
            'footer' => $template->footer,
        ]);

        $twigEnvironment = new Environment($arrayLoader);

        if ($extension = self::getExtension()) {
            array_map([$twigEnvironment, 'addExtension'], $extension);
        }

        $data = self::preparePlaceholders();

        self::$headerTemplate = $twigEnvironment->render('header', $data);
        self::$contentTemplate = $twigEnvironment->render('content', $data);
        self::$footerTemplate = $twigEnvironment->render('footer', $data);
    }

    private static function preparePlaceholders()
    {
        return array_merge(self::getPlaceholderData(), array_filter([
            'direction' => self::getLocaleDirection(),
            'font' => self::getFont(),
        ]));
    }
}
