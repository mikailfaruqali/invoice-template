<?php

namespace Snawbar\InvoiceTemplate\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait DatabaseOperations
{
    public static function create(Request $request)
    {
        return DB::table(self::getTableName())->updateOrInsert(['route' => $request->route], [
            'name' => $request->name,
            'header' => $request->header,
            'content' => $request->content,
            'footer' => $request->footer,
            'styles' => $request->styles,
        ]);
    }

    public static function deleteTemplate($routeName)
    {
        return DB::table(self::getTableName())->where('route', $routeName)->delete();
    }

    public static function getTemplate($routeName)
    {
        return DB::table(self::getTableName())->where('route', $routeName)->firstOrFail();
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
