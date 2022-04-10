<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Admin\TicketResource;
use App\Http\Resources\V1\Admin\SingleTicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'category_id' => 'nullable|integer|exists:categories,id',
            'state' => 'nullable|string|in:SEND,ANSWERED'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }

        $tickets = Ticket::when($request->input('category_id'), function ($query) use ($request) {
            return $query->where('category_id', $request->input('category_id'));
        })->when($request->input('state'), function ($query) use ($request) {
            return $query->where('state', $request->input('state'));
        })
            ->where('state', '!=', 'ANSWERING')
            ->adminDepartment()
            ->latest()
            ->paginate();

        return response()->json([
            'status' => 'OK',
            'data' => TicketResource::collection($tickets)
        ]);
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
                return $query->where('department', Auth::user()->department);
            })],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }
        $ticket = Ticket::with(['category', 'user:id,name', 'replies', 'replies.user:id,name'])->find($id);
        $ticket->state = 'ANSWERING';
        $ticket->save();

        return response()->json([
            'status' => 'OK',
            'data' => new SingleTicketResource($ticket)
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
                return $query->where('department', Auth::user()->department);
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
