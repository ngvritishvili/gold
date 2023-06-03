<?php

namespace App\Services;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductService
{

    public function index(Request $request)
    {
        return cache()->remember('products-page-' . request('page', 1),
            60 * 60,
            function () use ($request) {
                return Product::with(
                    ['likes' => function ($q) {
                        $q->select('star_rating', 'comment', 'likable_type', 'likable_id');
                    }, 'owner' => function ($q) {
                        $q->select('id', 'first_name', 'last_name');
                    }])
                    ->withCount('likes')
                    ->withAvg('likes', 'likables.star_rating')
//                    ->select('id', 'title', 'price', 'quantity', 'user_id', 'feedback_avg_star_rating', 'feedback_count')
                    ->simplePaginate($request->get('pagination') ?? 20);
            }
        );
    }

    /**
     * @param StoreProductRequest $request
     * @return Response|JsonResponse|Application|ResponseFactory
     */
    public function store(StoreProductRequest $request): Response|JsonResponse|Application|ResponseFactory
    {
        try {
            return response([
                'product' => Product::create([
                    'title' => $request->title,
                    'price' => $request->price,
                    'quantity' => $request->quantity,
                    'user_id' => auth()->id(),
                ]),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 501);
        }

    }

    public function update($request, $product): JsonResponse
    {
        try {
            $product->update($request);
            return response()->json([
                'updated_product' => $product,
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 501);
        }
    }

    public function delete($product): JsonResponse
    {
        try {
            $product->delete();
            return response()->json([
                'message' => 'Deleted Successfully!'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 501);
        }
    }
}
