<?php

namespace WalkerChiu\MallWishlist;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use WalkerChiu\MallWishlist\Models\Entities\Item;

class ItemTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');
    }

    /**
     * To load your package service provider, override the getPackageProviders.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return [\WalkerChiu\Core\CoreServiceProvider::class,
                \WalkerChiu\MallWishlist\MallWishlistServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * A basic functional test on Item.
     *
     * For WalkerChiu\MallWishlist\Models\Entities\Item
     * 
     * @return void
     */
    public function testItem()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-mall-wishlist.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-mall-wishlist.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-mall-wishlist.soft_delete', 1);

        $faker = \Faker\Factory::create();

        $user_id = 1;
        DB::table(config('wk-core.table.user'))->insert([
            'id'       => $user_id,
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);
        $stock_id_1 = 1;
        DB::table(config('wk-core.table.mall-shelf.stocks'))->insert([
            'id'       => $stock_id_1,
            'quantity' => 10
        ]);
        $stock_id_2 = 2;
        DB::table(config('wk-core.table.mall-shelf.stocks'))->insert([
            'id'       => $stock_id_2,
            'quantity' => 10
        ]);

        // Give
        factory(Item::class)->create(['user_id' => $user_id, 'stock_id' => $stock_id_1]);
        factory(Item::class)->create(['user_id' => $user_id, 'stock_id' => $stock_id_1]);
        factory(Item::class)->create(['user_id' => $user_id, 'stock_id' => $stock_id_2]);

        // Get records after creation
            // When
            $records = Item::all();
            // Then
            $this->assertCount(2, $records);

        // Delete someone
            // When
            $record_2 = Item::find(2);
            $record_2->delete();
            $records = Item::all();
            // Then
            $this->assertCount(1, $records);
    }
}
