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
            $blueprint->json('page');
            $blueprint->longText('header')->nullable();
            $blueprint->longText('content')->nullable();
            $blueprint->longText('footer')->nullable();
            $blueprint->longText('page_number')->nullable();
            $blueprint->longText('watermark')->nullable();
            $blueprint->double('watermark_opacity')->default(0.3);
            $blueprint->tinyText('logo')->nullable();
            $blueprint->double('margin_top')->default(0);
            $blueprint->double('margin_bottom')->default(0);
            $blueprint->double('margin_left')->default(0);
            $blueprint->double('margin_right')->default(0);
            $blueprint->double('header_space')->default(0);
            $blueprint->double('footer_space')->default(0);
            $blueprint->double('page_number_space')->default(8);
            $blueprint->enum('orientation', ['portrait', 'landscape'])->default('portrait');
            $blueprint->enum('paper_size', ['A4', 'A5', 'A3', 'letter', 'legal'])->default('A4');
            $blueprint->string('lang')->default('en');
            $blueprint->boolean('disabled_smart_shrinking')->default(FALSE);
            $blueprint->boolean('disable_header')->default(FALSE);
            $blueprint->boolean('disable_footer')->default(FALSE);
            $blueprint->boolean('disable_watermark')->default(FALSE);
            $blueprint->boolean('disable_page_number')->default(FALSE);
            $blueprint->boolean('header_first_page_only')->default(FALSE);
            $blueprint->boolean('footer_last_page_only')->default(FALSE);
            $blueprint->boolean('is_active')->default(TRUE);
        });
    }
};
