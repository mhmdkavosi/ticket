<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\SingleTicketResource;
use App\Http\Resources\V1\User\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     * @author  mhmdkavosi.dev@gmaill.com
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'department' => 'nullable|string|in:MARKETING,FINANCIAL,TECHNICAL',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }

        $tickets = Ticket::where('user_id', Auth::id())
            ->when($request->input('category_id'), function ($query) use ($request) {
                return $query->where('category_id', $request->input('category_id'));
            })
            ->when($request->input('department'), function ($query) use ($request) {
                return $query->where('department', $request->input('department'));
            })
            ->latest()
            ->paginate();


        return response()->json([
            'status' => 'OK',
            'data' => TicketResource::collection($tickets)
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
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|min:2|max:100',
            'message' => 'required|string|min:2|max:500',
            'department' => 'required|string|in:MARKETING,FINANCIAL,TECHNICAL',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }

        Ticket::create([
            'state' => 'SEND',
            'user_id' => Auth::id(),
            'category_id' => $request->input('category_id'),
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'department' => $request->input('department')
        ]);


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
            'id' => ['required', 'integer', Rule::exists('tickets', 'id')->where(function ($query) {
                return $query->where('user_id', Auth::id());
            })],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }
        $ticket = Ticket::with(['category', 'user:id,name', 'replies', 'replies.user:id,name'])->find($id);

        return response()->json([
            'status' => 'OK',
            'data' => new SingleTicketResource($ticket)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @author  mhmdkavosi.dev@gmaill.com
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->merge([
            'id' => $id
        ]);
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', Rule::exists('tickets', 'id')->where(function ($query) {
                return $query->where('user_id', Auth::id());
            })],
            'title' => 'required|string|min:2|max:100',
            'message' => 'required|string|min:2|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }
        $ticket = Ticket::withCount('replies')->find($id);
        if ($ticket->replies_count) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'امکان ویرایش تیکتی که پاسخ داده شده است وجود ندارد.'
            ], 403);
        }
        $ticket->update([
            'title' => $request->input('title'),
            'message' => $request->input('message')
        ]);

        return response()->json([
            'status' => 'OK'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @author  mhmdkavosi.dev@gmaill.com
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $request->merge([
            'id' => $id
        ]);
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', Rule::exists('tickets', 'id')->where(function ($query) {
                return $query->where('user_id', Auth::id());
            })]
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }
        $ticket = Ticket::with('replies')->find($id);
        $ticket->replies()->delete();
        $ticket->delete();

        return response()->json([
            'status' => 'OK'
        ]);

    }
}
