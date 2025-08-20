<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(config('snawbar-invoice-template.table'), function (Blueprint $table) {
            $table->string('route')->primary();
            $table->string('name');
            $table->text('header');
            $table->text('content');
            $table->text('footer')->nullable();
            $table->json('styles')->nullable();
        });
    }
};
