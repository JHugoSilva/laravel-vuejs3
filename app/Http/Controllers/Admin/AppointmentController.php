<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request) {
        $status = (int)$request->status;
        return Appointment::query()
            ->with('client:id,first_name,last_name')
            ->when($status, function($query) use($status){
                return $query->where('status', AppointmentStatus::from($status));
            })
            ->latest()
            ->paginate()
            ->through(fn ($appoinment) => [
                'id' => $appoinment->id,
                'start_time' => $appoinment->start_time->format('Y-m-d h:i A'),
                'end_time' => $appoinment->end_time->format('Y-m-d h:i A'),
                'status' => [
                    'name' => $appoinment->status->name,
                    'color' => $appoinment->status->color(),
                ],
                'client' => $appoinment->client,
            ]);
    }

    public function store() {

        $validated = request()->validate([
            'client_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ],[
            'client_id.required' => 'The name field os required'
        ]);

        Appointment::create([
            'title' => $validated['title'],
            'clients_id' => $validated['client_id'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'description' => $validated['description'],
            'status' => AppointmentStatus::SCHEDULED,
        ]);

        return response()->json(['message' => 'success']);
    }

    public function edit($id) {
        return Appointment::find($id);
    }

    public function update($id) {

        $appoinment = Appointment::find($id);

        $validated = request()->validate([
            'clients_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ],[
            'client_id.required' => 'The name field os required'
        ]);

        $appoinment->update($validated);

        return response()->json(['message' => 'success', 'success' => true]);
    }

    public function destroy($id) {
        $appoinment = Appointment::find($id);
        $appoinment->delete();
        if ($appoinment == '1') {
            $success = true;
            $message = "Usuario excluído com sucesso";
        } else {
            $success = true;
            $message = "Usuario não encontrado";
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }
}
