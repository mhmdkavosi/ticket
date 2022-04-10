<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReplay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReplayTicketController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param $ticket_id
     * @param Request $request
     * @return JsonResponse
     * @author  mhmdkavosi.dev@gmaill.com
     */
    public function store($ticket_id, Request $request): JsonResponse
    {
        $request->merge([
            'ticket_id' => $ticket_id
        ]);
        $validator = Validator::make($request->all(), [
            'ticket_id' => ['required', 'integer', Rule::exists('tickets', 'id')->where('user_id', Auth::id())],
            'message' => 'required|string|min:5|max:500'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }

        $ticket = Ticket::with('replies')->find($ticket_id);
        $ticket->replies()->create([
            'message' => $request->input('message'),
            'user_id' => Auth::id()
        ]);
        $ticket->state = 'SEND';
        $ticket->save();

        return response()->json([
            'status' => 'OK'
        ], 201);
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
            'id' => ['required', 'integer', Rule::exists('ticket_replays', 'id')->where('user_id', Auth::id())],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }

        TicketReplay::find($id)->delete();

        return response()->json([
            'status' => 'OK'
        ]);
    }
}
