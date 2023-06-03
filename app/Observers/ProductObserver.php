<?php

namespace App\Observers;

use App\Models\Product;
use App\Notifications\ProductCreated;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function created(Product $product)
    {
        $this->clearPaginateCache();
//        $product->owner->notify(new ProductCreated($product));
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function updated(Product $product)
    {
        $this->clearPaginateCache();
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function deleted(Product $product)
    {
        $this->clearPaginateCache();
    }

    /**
     * Handle the Product "restored" event.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function restored(Product $product)
    {
        $this->clearPaginateCache();
    }

    /**
     * Handle the Product "force deleted" event.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function forceDeleted(Product $product)
    {
        $this->clearPaginateCache();
    }

    /**
     * Clear All cashed pagination pages for products
     */
    private function clearPaginateCache()
    {
        for ($i = 1; $i <= 100; $i++) {
            $key = 'products-page-' . $i;
            if (Cache::has($key)) {
                Cache::forget($key);
            } else {
                break;
            }
        }
    }
}
