<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait DatabaseOperations
{
    public static function create(Request $request, $templateId = NULL)
    {
        if ($request->boolean('is_active', FALSE) && self::templateExists($request->page, $request->lang)) {
            DB::table(self::getTableName())
                ->where('page', $request->page)
                ->where('lang', $request->lang)
                ->update([
                    'is_active' => FALSE,
                ]);
        }

        return DB::table(self::getTableName())->updateOrInsert(['id' => $templateId], [
            'page' => $request->page,
            'lang' => $request->lang,
            'header' => $request->header,
            'content' => $request->content,
            'footer' => (string) ($request->footer),
            'orientation' => $request->orientation,
            'paper_size' => $request->paper_size,
            'width' => $request->width,
            'height' => $request->height,
            'margin_top' => $request->margin_top,
            'margin_bottom' => $request->margin_bottom,
            'margin_left' => $request->margin_left,
            'margin_right' => $request->margin_right,
            'header_space' => $request->header_space,
            'footer_space' => $request->footer_space,
            'is_active' => $request->boolean('is_active', FALSE),
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
