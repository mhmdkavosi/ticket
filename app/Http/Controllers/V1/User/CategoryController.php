<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        $categories = Cache::remember('categories', now()->addHour(), function () {
            // Use DB for Better Performance (Eloquent return laravel collection)
            return DB::table('categories')->select('id', 'title')->get();
        });

        return response()->json([
            'status' => 'OK',
            'data' => CategoryResource::collection($categories)
        ]);
    }

}
