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

    private ?string $pageNumberTemplate = NULL;

    private ?string $watermarkTemplate = NULL;

    private bool $disableHeaderTemplate = FALSE;

    private bool $disabledFooterTemplate = FALSE;

    private bool $disabledWatermarkTemplate = FALSE;

    private bool $disabledPageNumberTemplate = FALSE;

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

    private function getDisabledPageNumberTemplate()
    {
        return $this->disabledPageNumberTemplate;
    }

    private function getPageNumberTemplate()
    {
        return $this->pageNumberTemplate;
    }

    private function getDisabledWatermarkTemplate()
    {
        return $this->disabledWatermarkTemplate;
    }

    private function getWatermarkTemplate()
    {
        return $this->watermarkTemplate;
    }

    private function loadTemplate()
    {
        $template = $this->getTemplate();

        $this->disableHeaderTemplate = $template->disable_header;
        $this->disabledFooterTemplate = $template->disable_footer;
        $this->disabledWatermarkTemplate = (bool) data_get($template, 'disable_watermark', FALSE);
        $this->disabledPageNumberTemplate = (bool) data_get($template, 'disable_page_number', FALSE);

        $this->headerTemplate = $this->renderTemplate($template->header, $this->getHeaderData());
        $this->contentTemplate = $this->renderTemplate($template->content, $this->getContentData());
        $this->footerTemplate = $this->renderTemplate($template->footer, $this->getFooterData());
        $this->pageNumberTemplate = $this->renderTemplate(data_get($template, 'page_number'), $this->getFooterData());
        $this->watermarkTemplate = $this->renderTemplate(data_get($template, 'watermark'), $this->getContentData());
    }

    private function renderTemplate($template, $data = [])
    {
        return Blade::render($template, $data);
    }
}
