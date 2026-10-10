<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait DatabaseOperations
{
    public static function create(Request $request, $templateId = NULL)
    {
        $values = [
            'disabled_smart_shrinking' => $request->boolean('disabled_smart_shrinking', FALSE),
            'disable_header' => $request->boolean('disable_header', FALSE),
            'disable_footer' => $request->boolean('disable_footer', FALSE),
            'disable_watermark' => $request->boolean('disable_watermark', FALSE),
            'disable_page_number' => $request->boolean('disable_page_number', FALSE),
            'header_first_page_only' => $request->boolean('header_first_page_only', FALSE),
            'footer_last_page_only' => $request->boolean('footer_last_page_only', FALSE),
            'page' => static::encodePages($request->page),
            'lang' => $request->lang,
            'header' => $request->header,
            'content' => $request->content,
            'footer' => $request->footer,
            'page_number' => $request->page_number,
            'watermark' => $request->watermark,
            'watermark_opacity' => $request->input('watermark_opacity', 0.3),
            'orientation' => $request->input('orientation', 'portrait'),
            'paper_size' => $request->input('paper_size', 'A4'),
            'margin_top' => $request->input('margin_top', 0),
            'margin_bottom' => $request->input('margin_bottom', 0),
            'margin_left' => $request->input('margin_left', 0),
            'margin_right' => $request->input('margin_right', 0),
            'header_space' => $request->input('header_space', 0),
            'footer_space' => $request->input('footer_space', 0),
            'page_number_space' => $request->input('page_number_space', 8),
        ];

        if (blank($templateId)) {
            return DB::table(static::getTableName())->insert($values);
        }

        return DB::table(static::getTableName())
            ->where('id', $templateId)
            ->update($values) >= 0;
    }

    public static function createDefault($page = ['*'], $options = [])
    {
        return DB::table(static::getTableName())->insert(array_merge([
            'page' => static::encodePages($page),
            'disabled_smart_shrinking' => TRUE,
            'disable_header' => FALSE,
            'disable_footer' => FALSE,
            'disable_watermark' => FALSE,
            'disable_page_number' => FALSE,
            'header_first_page_only' => FALSE,
            'footer_last_page_only' => FALSE,
            'orientation' => 'portrait',
            'lang' => '*',
            'paper_size' => 'A4',
            'margin_top' => 50,
            'margin_bottom' => 0,
            'margin_left' => 0,
            'margin_right' => 0,
            'header_space' => 0,
            'footer_space' => 0,
            'page_number_space' => 8,
            'is_active' => TRUE,
        ], $options));
    }

    public static function deleteTemplate($templateId)
    {
        return DB::table(static::getTableName())
            ->where('id', $templateId)
            ->delete();
    }

    public static function getTemplateById($templateId)
    {
        return DB::table(static::getTableName())
            ->where('id', $templateId)
            ->first();
    }

    public static function getAllTemplates()
    {
        return DB::table(static::getTableName())
            ->orderByDesc('id')
            ->get();
    }

    private static function getTableName()
    {
        return config('snawbar-invoice-template.table');
    }

    private static function encodePages($pages)
    {
        if (static::isJson($pages)) {
            return $pages;
        }

        if (is_array($pages)) {
            return json_encode($pages);
        }

        return json_encode([$pages]);
    }

    private static function isJson($string): bool
    {
        if (! is_string($string)) {
            return FALSE;
        }

        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    private function getTemplateFromDatabase($page = '*')
    {
        $locale = app()->getLocale();

        $builder = DB::table(static::getTableName())->where('is_active', TRUE)->orderByDesc('id');

        return $builder->clone()->whereJsonContains('page', $page)->where('lang', $locale)->first()
            ?? $builder->clone()->whereJsonContains('page', $page)->where('lang', '*')->first()
            ?? $builder->clone()->whereJsonContains('page', '*')->where('lang', $locale)->first()
            ?? $builder->clone()->whereJsonContains('page', '*')->where('lang', '*')->firstOrFail();
    }
}
