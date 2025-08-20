<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait DatabaseOperations
{
    public static function create(Request $request, $templateId = NULL)
    {
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
            'is_active' => $request->input('is_active', TRUE),
        ]);
    }

    public static function deleteTemplate($templateId)
    {
        return DB::table(self::getTableName())
            ->where('id', $templateId)
            ->delete();
    }

    public static function getTemplate($page)
    {
        return DB::table(self::getTableName())
            ->where('lang', app()->getLocale())
            ->where('is_active', TRUE)
            ->where('page', $page)
            ->firstOrFail();
    }

    public static function getTemplateById($templateId)
    {
        return DB::table(self::getTableName())
            ->where('id', $templateId)
            ->firstOrFail();
    }

    public static function getAllTemplates()
    {
        return DB::table(self::getTableName())->get();
    }

    private static function getTableName()
    {
        return config('snawbar-invoice-template.table');
    }
}
