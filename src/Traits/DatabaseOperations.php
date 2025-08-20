<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait DatabaseOperations
{
    public static function create(Request $request)
    {
        return DB::table(self::getTableName())->updateOrInsert(['route' => $request->route, 'lang' => $request->lang], [
            'header' => $request->header,
            'content' => $request->content,
            'footer' => $request->footer,
        ]);
    }

    public static function deleteTemplate($routeName, $lang = 'en')
    {
        return DB::table(self::getTableName())
            ->where('route', $routeName)
            ->where('lang', $lang)
            ->delete();
    }

    public static function getTemplate($routeName, $lang = 'en')
    {
        return DB::table(self::getTableName())
            ->where('route', $routeName)
            ->where('lang', $lang)
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
