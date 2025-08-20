<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(config('snawbar-invoice-template.table'), function (Blueprint $blueprint) {
            $blueprint->string('route');
            $blueprint->text('header');
            $blueprint->text('content');
            $blueprint->text('footer');
            $blueprint->string('lang')->default('en');
            $blueprint->primary(['route', 'lang']);
        });
    }
};
