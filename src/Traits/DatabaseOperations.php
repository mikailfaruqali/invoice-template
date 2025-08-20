<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait DatabaseOperations
{
    public static function create(Request $request)
    {
        return DB::table(self::getTableName())->updateOrInsert(['route' => $request->route, 'lang' => $request->lang], [
            'header' => $request->input('header', ''),
            'content' => $request->input('content', ''),
            'footer' => $request->input('footer', ''),
            'margin_top' => $request->input('margin_top'),
            'margin_bottom' => $request->input('margin_bottom'),
            'margin_left' => $request->input('margin_left'),
            'margin_right' => $request->input('margin_right'),
            'header_space' => $request->input('header_space'),
            'footer_space' => $request->input('footer_space'),
        ]);
    }

    public static function deleteTemplate($routeName, $lang = 'en')
    {
        return DB::table(self::getTableName())
            ->where('route', $routeName)
            ->where('lang', $lang)
            ->delete();
    }

    public static function getTemplate($routeName)
    {
        return DB::table(self::getTableName())
            ->where('route', $routeName)
            ->where('lang', app()->getLocale())
            ->firstOrFail();
    }

    private static function getTableName()
    {
        return config('snawbar-invoice-template.table');
    }

    private static function getAllTemplates()
    {
        return DB::table(self::getTableName())->get();
    }
}
