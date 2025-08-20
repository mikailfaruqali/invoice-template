<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait DatabaseOperations
{
    public static function create(Request $request, $templateId = NULL)
    {
        if ($request->boolean('is_active', TRUE) && self::templateExists($request->input('page'), $request->input('lang'))) {
            DB::table(self::getTableName())
                ->where('page', $request->input('page'))
                ->where('lang', $request->input('lang'))
                ->update(['is_active' => FALSE]);
        }

        return DB::table(self::getTableName())->updateOrInsert(['id' => $templateId], [
            'page' => $request->input('page'),
            'lang' => $request->input('lang'),
            'header' => $request->input('header', ''),
            'content' => $request->input('content', ''),
            'footer' => $request->input('footer', ''),
            'margin_top' => $request->input('margin_top'),
            'margin_bottom' => $request->input('margin_bottom'),
            'margin_left' => $request->input('margin_left'),
            'margin_right' => $request->input('margin_right'),
            'header_space' => $request->input('header_space'),
            'footer_space' => $request->input('footer_space'),
            'is_active' => $request->boolean('is_active', TRUE),
        ]);
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

    private static function getTemplateFromDatabase($page)
    {
        return DB::table(self::getTableName())
            ->where('lang', app()->getLocale())
            ->where('is_active', TRUE)
            ->where('page', $page)
            ->firstOrFail();
    }

    private static function templateExists($page, $lang)
    {
        return DB::table(self::getTableName())
            ->where('page', $page)
            ->where('lang', $lang)
            ->where('is_active', TRUE)
            ->exists();
    }

    private static function getTableName()
    {
        return config('snawbar-invoice-template.table');
    }
}
