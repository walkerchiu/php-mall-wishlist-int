<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWkMallWishlistTable extends Migration
{
    public function up()
    {
        Schema::create(config('wk-core.table.mall-wishlist.items'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('stock_id');

            $table->timestampsTz();

            $table->foreign('user_id')->references('id')
                  ->on(config('wk-core.table.user'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
        if (
            config('wk-mall-wishlist.onoff.mall-shelf')
            && Schema::hasTable(config('wk-core.table.mall-shelf.stocks'))
        ) {
            Schema::table(config('wk-core.table.mall-wishlist.items'), function (Blueprint $table) {
                $table->foreign('stock_id')->references('id')
                      ->on(config('wk-core.table.mall-shelf.stocks'))
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            });
        }
    }

    public function down() {
        Schema::dropIfExists(config('wk-core.table.mall-wishlist.items'));
    }
}
