<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Admin\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     * @author  mhmdkavosi.dev@gmaill.com
     */
    public function index(): JsonResponse
    {
        $categories = Category::select('id', 'title')->paginate();

        return response()->json([
            'status' => 'OK',
            'data' => CategoryResource::collection($categories)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @author  mhmdkavosi.dev@gmaill.com
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:2|max:50'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }

        Category::create($request->only('title'));
        Cache::forget('categories');

        return response()->json([
            'status' => 'OK'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @author  mhmdkavosi.dev@gmaill.com
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $request->merge([
            'id' => $id
        ]);
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:categories,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }
        $category = Category::find($id);

        return response()->json([
            'status' => 'OK',
            'data' => new CategoryResource($category)
        ]);
    }
}
