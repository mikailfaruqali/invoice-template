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
            $blueprint->longText('header');
            $blueprint->longText('content');
            $blueprint->longText('footer');
            $blueprint->tinyText('logo')->nullable();
            $blueprint->tinyText('watermark')->nullable();
            $blueprint->double('margin_top')->default(0);
            $blueprint->double('margin_bottom')->default(0);
            $blueprint->double('margin_left')->default(0);
            $blueprint->double('margin_right')->default(0);
            $blueprint->double('header_space')->default(0);
            $blueprint->double('footer_space')->default(0);
            $blueprint->enum('orientation', ['portrait', 'landscape'])->default('portrait');
            $blueprint->enum('paper_size', ['A4', 'A5', 'A3'])->default('A4');
            $blueprint->double('width')->default('210');
            $blueprint->double('height')->default('297');
            $blueprint->string('lang')->default('en');
            $blueprint->boolean('is_active')->default(TRUE);
            $blueprint->unique(['page', 'lang', 'is_active']);
        });
    }
};
