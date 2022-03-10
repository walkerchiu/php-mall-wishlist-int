<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\MallWishlist\Models\Entities\Item;

$factory->define(Item::class, function (Faker $faker) {
    return [
        'user_id'  => 1,
        'stock_id' => 1
    ];
});
