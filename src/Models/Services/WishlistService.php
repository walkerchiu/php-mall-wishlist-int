<?php

namespace WalkerChiu\MallWishlist\Models\Services;

use Illuminate\Support\Facades\App;

class WishlistService
{
    protected $repository;



    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->repository = App::make(config('wk-core.class.mall-wishlist.itemRepository'));
    }

    /**
     * @param Int  $user_id
     * @return Bool
     */
    public function clear(int $user_id)
    {
        return $this->repository->where('user_id', '=', $user_id)
                                ->delete();
    }
}
