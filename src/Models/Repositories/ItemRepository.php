<?php

namespace WalkerChiu\MallWishlist\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;
use WalkerChiu\MallShelf\Models\Services\StockService;

class ItemRepository extends Repository
{
    use FormTrait;
    use RepositoryTrait;

    protected $instance;



    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->instance = App::make(config('wk-core.class.mall-wishlist.item'));
    }

    /**
     * @param String  $code
     * @param Array   $data
     * @param Bool    $auto_packing
     * @return Array|Collection|Eloquent
     */
    public function list(string $code, array $data, $auto_packing = false)
    {
        $instance = $this->instance;

        $data = array_map('trim', $data);
        $repository = $instance->when($data, function ($query, $data) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['user_id']), function ($query) use ($data) {
                                                return $query->where('user_id', $data['user_id']);
                                            })
                                            ->unless(empty($data['stock_id']), function ($query) use ($data) {
                                                return $query->where('stock_id', $data['stock_id']);
                                            });
                                })
                                ->orderBy('updated_at', 'DESC');

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-mall-wishlist.output_format'), config('wk-mall-wishlist.pagination.pageName'), config('wk-mall-wishlist.pagination.perPage'));

            if (in_array(config('wk-mall-wishlist.output_format'), ['array', 'array_pagination'])) {
                switch (config('wk-mall-wishlist.output_format')) {
                    case "array":
                        $entities = $factory->toCollection($repository);
                        // no break
                    case "array_pagination":
                        $entities = $factory->toCollectionWithPagination($repository);
                        // no break
                    default:
                        $output = [];
                        foreach ($entities as $instance) {
                            array_push($output, $this->show($instance, $code));
                        }
                }
                return $output;
            } else {
                return $factory->output($repository);
            }
        }

        return $repository;
    }

    /**
     * @param Item    $instance
     * @param String  $code
     * @return Array
     */
    public function show($instance, string $code): array
    {
        $data = [
            'id'         => $instance->id,
            'stock_id'   => $instance->stock_id,
            'created_at' => $instance->created_at,
            'updated_at' => $instance->updated_at,
            'stock'      => []
        ];

        if (
            config('wk-mall-wishlist.onoff.mall-shelf')
            && !empty(config('wk-core.class.mall-shelf.stock'))
        ) {
            $service = \WalkerChiu\MallShelf\Models\Services\StockService();
            $data['stock'] = $service->showForItem($instance->stock, $code);
        }

        return $data;
    }

    /**
     * @param String  $code
     * @param Int     $user_id
     * @param Int     $stock_id
     * @return Array
     */
    public function add(string $code, int $user_id, int $stock_id): array
    {
        $record = $this->where('user_id', '=', $user_id)
                       ->where('stock_id', '=', $stock_id)
                       ->first();

        if (empty($record))
            $record = $this->save([
                'user_id'  => $user_id,
                'stock_id' => $stock_id
            ]);
        else
            $record->touch();

        return $this->show($record, $code);
    }

    /**
     * @param Int    $user_id
     * @param Array  $stock_id
     * @return Bool
     */
    public function remove(int $user_id, array $stock_id): bool
    {
        return $this->where('user_id', '=', $user_id)
                    ->whereIn('stock_id', $stock_id)
                    ->delete();
    }
}
