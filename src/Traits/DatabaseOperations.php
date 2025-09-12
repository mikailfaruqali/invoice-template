<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait DatabaseOperations
{
    public static function create(Request $request, $templateId = NULL)
    {
        return DB::table(self::getTableName())->updateOrInsert(['id' => $templateId], [
            'disabled_smart_shrinking' => $request->boolean('disabled_smart_shrinking', FALSE),
            'disable_header' => $request->boolean('disable_header', FALSE),
            'disable_footer' => $request->boolean('disable_footer', FALSE),
            'page' => self::encodePages($request->page),
            'lang' => $request->lang,
            'header' => $request->header,
            'content' => $request->content,
            'footer' => $request->footer,
            'orientation' => $request->orientation,
            'paper_size' => $request->paper_size,
            'margin_top' => $request->margin_top,
            'margin_bottom' => $request->margin_bottom,
            'margin_left' => $request->margin_left,
            'margin_right' => $request->margin_right,
            'header_space' => $request->header_space,
            'footer_space' => $request->footer_space,
        ]);
    }

    public static function createDefault($page = ['*'], $options = [])
    {
        return DB::table(self::getTableName())->insert(array_merge([
            'page' => self::encodePages($page),
            'disabled_smart_shrinking' => TRUE,
            'disable_header' => FALSE,
            'disable_footer' => FALSE,
            'orientation' => 'portrait',
            'lang' => '*',
            'paper_size' => 'A4',
            'margin_top' => 50,
            'margin_bottom' => 0,
            'margin_left' => 0,
            'margin_right' => 0,
            'header_space' => 0,
            'footer_space' => 0,
            'is_active' => TRUE,
        ], $options));
    }

    public static function deleteTemplate($templateId)
    {
        return DB::table(self::getTableName())
            ->where('id', $templateId)
            ->delete();
    }

    public static function getTemplateById($templateId)
    {
        return DB::table(self::getTableName())
            ->where('id', $templateId)
            ->firstOrFail();
    }

    public static function getAllTemplates()
    {
        return DB::table(self::getTableName())
            ->orderByDesc('id')
            ->get();
    }

    private static function getTemplateFromDatabase($page = '*')
    {
        $locale = app()->getLocale();

        $builder = DB::table(self::getTableName())->where('is_active', TRUE)->orderByDesc('id');

        return $builder->clone()->whereJsonContains('page', $page)->where('lang', $locale)->first()
            ?? $builder->clone()->whereJsonContains('page', $page)->where('lang', '*')->first()
            ?? $builder->clone()->whereJsonContains('page', '*')->where('lang', $locale)->first()
            ?? $builder->clone()->whereJsonContains('page', '*')->where('lang', '*')->firstOrFail();
    }

    private static function getTableName()
    {
        return config('snawbar-invoice-template.table');
    }

    private static function encodePages($pages)
    {
        if (self::isJson($pages)) {
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
}
