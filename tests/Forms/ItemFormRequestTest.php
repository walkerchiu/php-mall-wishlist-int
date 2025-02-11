<?php

namespace WalkerChiu\MallWishlist;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use WalkerChiu\MallWishlist\Models\Entities\Item;
use WalkerChiu\MallWishlist\Models\Forms\ItemFormRequest;

class ItemFormRequestTest extends \Orchestra\Testbench\TestCase
{
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

        $this->request  = new ItemFormRequest();
        $this->rules    = $this->request->rules();
        $this->messages = $this->request->messages();
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
     * Unit test about Authorize.
     *
     * For WalkerChiu\MallWishlist\Models\Forms\CartFormRequest
     * 
     * @return void
     */
    public function testAuthorize()
    {
        $this->assertEquals(true, 1);
    }

    /**
     * Unit test about Rules.
     *
     * For WalkerChiu\MallWishlist\Models\Forms\CartFormRequest
     * 
     * @return void
     */
    public function testRules()
    {
        Config::set('wk-mall-wishlist.onoff.mall-shelf', 1);

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
            'id'         => $stock_id_2,
            'quantity'   => 10,
            'is_enabled' => 1
        ]);


        // No enabled record
            // Give
            $attributes = [
                'user_id'  => $user_id,
                'stock_id' => $stock_id_1
            ];
            // When
            $validator = Validator::make($attributes, $this->rules, $this->messages); $this->request->withValidator($validator);
            $fails = $validator->fails();
            // Then
            $this->assertEquals(true, $fails);

        // Has enabled record
            // Give
            $attributes = [
                'user_id'  => $user_id,
                'stock_id' => $stock_id_2
            ];
            // When
            $validator = Validator::make($attributes, $this->rules, $this->messages); $this->request->withValidator($validator);
            $fails = $validator->fails();
            // Then
            $this->assertEquals(false, $fails);

        // Constraint
            // Give
            $attributes = [
                'user_id'  => $faker->randomElement([null, 0, 2]),
                'stock_id' => $stock_id_2
            ];
            // When
            $validator = Validator::make($attributes, $this->rules, $this->messages); $this->request->withValidator($validator);
            $fails = $validator->fails();
            // Then
            $this->assertEquals(true, $fails);

            // Give
            $attributes = [
                'user_id'  => $user_id,
                'stock_id' => $faker->randomElement([null, 0, 3])
            ];
            // When
            $validator = Validator::make($attributes, $this->rules, $this->messages); $this->request->withValidator($validator);
            $fails = $validator->fails();
            // Then
            $this->assertEquals(true, $fails);
    }
}
