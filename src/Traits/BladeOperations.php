<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Illuminate\Support\Facades\Blade;

/**
 * @method static static newInstance()
 * @method object|null getTemplateFromDatabase(string $page = '*')
 */
trait BladeOperations
{
    private ?string $headerTemplate = NULL;

    private ?string $contentTemplate = NULL;

    private ?string $footerTemplate = NULL;

    private bool $disableHeaderTemplate = FALSE;

    private bool $disabledFooterTemplate = FALSE;

    public static function directPrint($templateName, $fallbackView, $data = [])
    {
        $instance = static::newInstance();

        return $instance->renderTemplate(optional($instance->getTemplateFromDatabase($templateName))->content ?: $fallbackView, $data);
    }

    private function getDisableHeaderTemplate()
    {
        return $this->disableHeaderTemplate;
    }

    private function getHeaderTemplate()
    {
        return $this->headerTemplate;
    }

    private function getContentTemplate()
    {
        return $this->contentTemplate;
    }

    private function getDisabledFooterTemplate()
    {
        return $this->disabledFooterTemplate;
    }

    private function getFooterTemplate()
    {
        return $this->footerTemplate;
    }

    private function loadTemplate()
    {
        $template = $this->getTemplate();

        $this->disableHeaderTemplate = $template->disable_header;
        $this->disabledFooterTemplate = $template->disable_footer;

        $this->headerTemplate = $this->renderTemplate($template->header, $this->getHeaderData());
        $this->contentTemplate = $this->renderTemplate($template->content, $this->getContentData());
        $this->footerTemplate = $this->renderTemplate($template->footer, $this->getFooterData());
    }

    private function renderTemplate($template, $data = [])
    {
        return Blade::render($template, $data);
    }
}
