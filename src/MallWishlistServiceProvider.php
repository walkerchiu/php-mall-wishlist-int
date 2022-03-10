<?php

namespace WalkerChiu\MallWishlist;

use Illuminate\Support\ServiceProvider;
use WalkerChiu\MallWishlist\Models\Entities\Item;
use WalkerChiu\MallWishlist\Models\Observers\ItemObserver;

class MallWishlistServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
           __DIR__ .'/config/mall-wishlist.php' => config_path('wk-mall-wishlist.php'),
        ], 'config');

        // Publish migration files
        $from = __DIR__ .'/database/migrations/';
        $to   = database_path('migrations') .'/';
        $this->publishes([
            $from .'create_wk_mall_wishlist_table.php'
                => $to .date('Y_m_d_His', time()) .'_create_wk_mall_wishlist_table.php'
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/translations', 'php-mall-wishlist');
        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/php-mall-wishlist'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                config('wk-mall-wishlist.command.cleaner')
            ]);
        }

        config('wk-core.class.mall-wishlist.item')::observe(config('wk-core.class.mall-wishlist.itemObserver'));
    }

    /**
     * Register the blade directives
     *
     * @return void
     */
    private function bladeDirectives()
    {
    }

    /**
     * Merges user's and package's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        if (!config()->has('wk-mall-wishlist')) {
            $this->mergeConfigFrom(
                __DIR__ .'/config/mall-wishlist.php', 'wk-mall-wishlist'
            );
        }

        $this->mergeConfigFrom(
            __DIR__ .'/config/mall-wishlist.php', 'mall-wishlist'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param String  $path
     * @param String  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (
            !(
                $this->app instanceof CachesConfiguration
                && $this->app->configurationIsCached()
            )
        ) {
            $config = $this->app->make('config');
            $content = $config->get($key, []);

            $config->set($key, array_merge(
                require $path, $content
            ));
        }
    }
}
