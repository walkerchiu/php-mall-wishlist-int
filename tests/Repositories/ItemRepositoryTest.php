<?php

namespace WalkerChiu\MallWishlist;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use WalkerChiu\MallWishlist\Models\Entities\Item;
use WalkerChiu\MallWishlist\Models\Repositories\ItemRepository;

class ItemRepositoryTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected $repository;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        //$this->loadLaravelMigrations(['--database' => 'mysql']);
        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');

        $this->repository = $this->app->make(ItemRepository::class);
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
     * A basic functional test on CartRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\Repository
     *
     * @return void
     */
    public function testCartRepository()
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

        // Give
        for ($i=1; $i<=3; $i++) {
            $stock_id = $i;
            DB::table(config('wk-core.table.mall-shelf.stocks'))->insert([
                'id'       => $stock_id,
                'quantity' => 10
            ]);

            $this->repository->save([
                'user_id'  => $user_id,
                'stock_id' => $stock_id
            ]);
        }

        // Get and Count records after creation
            // When
            $records = $this->repository->get();
            $count   = $this->repository->count();
            // Then
            $this->assertCount(3, $records);
            $this->assertEquals(3, $count);

        // Find someone
            // When
            $record = $this->repository->find(1);
            // Then
            $this->assertNotNull($record);

            // When
            $record = $this->repository->find(4);
            // Then
            $this->assertNull($record);

        // Delete someone
            // When
            $this->repository->deleteByIds([1]);
            $count = $this->repository->count();
            // Then
            $this->assertEquals(2, $count);

            // When
            $this->repository->deleteByExceptIds([3]);
            $count = $this->repository->count();
            $record = $this->repository->find(3);
            // Then
            $this->assertEquals(1, $count);
            $this->assertNotNull($record);

            // When
            $count = $this->repository->where('id', '>', 0)->count();
            // Then
            $this->assertEquals(1, $count);
    }

    /**
     * Unit test about Query List on CartRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryTrait
     *     WalkerChiu\MallWishlist\Models\Repositories\CartRepository
     *
     * @return void
     */
    public function testQueryList()
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

        // Give
        for ($i=1; $i<=4; $i++) {
            $stock_id = $i;
            DB::table(config('wk-core.table.mall-shelf.stocks'))->insert([
                'id'       => $stock_id,
                'quantity' => 10
            ]);

            $this->repository->save([
                'user_id'  => $user_id,
                'stock_id' => $stock_id
            ]);
        }

        // Get query
            // When
            sleep(1);
            $this->repository->find(3)->touch();
            $records = $this->repository->get()
                                        ->sortByDESC('updated_at');
            // Then
            $this->assertCount(4, $records);

            // When
            $record = $records->first();
            // Then
            $this->assertEquals(3, $record->id);
    }
}
