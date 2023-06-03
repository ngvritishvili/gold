<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Like;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{

    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'products' => $this->productService->index($request)
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            return response()->json([
                'product' => $this->productService->store($request)
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     * @return Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Product $product
     * @return Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        return $this->productService->update($request->validated(), $product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        return $this->productService->delete($product);
    }

    public function test(Request $request)
    {

//        dd($request->all(), auth()->user());
//        dd(Product::find(1));

//       $prod = Product::find(9);

        $user = Auth::user();

        $user->likes()->attach(User::find(3), $request->all());

//        $prod->like()->attach(\auth()->user(),[
//            'star_rating' => $request->star_rating,
//            'comment'  => $request->comment,
//        ]);


        dd(User::find(1)->likes);
//       dd(Product::find(5)->likes);


//        $users = User::withWhereHas('products', function ($query){
//            $query->withWhereHas('feedback');
//        })->get();
//

//        $products = Product::with(
//            ['feedback' => function ($q) {
//                $q->select('star_rating', 'status','feedbackable_type','feedbackable_id');
//            }, 'owner' => function ($q) {
//                $q->select('id', 'first_name', 'last_name');
//            }])
//            ->select('id', 'title', 'price', 'quantity', 'user_id')
//            ->get();
    }
}
