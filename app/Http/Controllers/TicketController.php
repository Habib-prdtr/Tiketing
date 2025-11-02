<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Resources\TicketResource;

class TicketController extends Controller
{
    public function index(Request $request){
        $query = Ticket::query();
        $tickets = $query->where('user_id', auth()->user()->id)->get();
    }

    public function store(TicketStoreRequest $request){
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $ticket = new Ticket;
            $ticket->user_id = auth()->user()->id;
            $ticket->code = 'TIC-' . rand(100000, 999999);
            $ticket->title = $data['title'];
            $ticket->description = $data['description'];
            $ticket->priority = $data['priority'];
            $ticket->save();

            DB::commit();

            return response()->json([
                'massage' => 'Ticket Berhasil Dibuat',
                'data' => new TicketResource($ticket)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'massage' => 'Terjadi Kesalahan',
                'data' => null,
            ], 500);
        }
    }
}
