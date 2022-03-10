<?php

namespace WalkerChiu\MallWishlist\Console\Commands;

use WalkerChiu\Core\Console\Commands\Cleaner;

class MallWishlistCleaner extends Cleaner
{
    /**
     * The name and signature of the console command.
     *
     * @var String
     */
    protected $signature = 'command:MallWishlistCleaner';

    /**
     * The console command description.
     *
     * @var String
     */
    protected $description = 'Truncate tables';

    /**
     * Execute the console command.
     *
     * @return Mixed
     */
    public function handle()
    {
        parent::clean('mall-wishlist');
    }
}
