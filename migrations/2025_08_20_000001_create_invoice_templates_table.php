<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(config('snawbar-invoice-template.table'), function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('page');
            $blueprint->text('header');
            $blueprint->text('content');
            $blueprint->text('footer');
            $blueprint->double('margin_top')->default(0);
            $blueprint->double('margin_bottom')->default(0);
            $blueprint->double('margin_left')->default(0);
            $blueprint->double('margin_right')->default(0);
            $blueprint->double('header_space')->default(0);
            $blueprint->double('footer_space')->default(0);
            $blueprint->string('lang')->default('en');
            $blueprint->boolean('is_active')->default(TRUE);
            $blueprint->unique(['id', 'page', 'lang']);
        });
    }
};
