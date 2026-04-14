<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppointmentReason;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentApiController extends Controller
{
    public function __construct(protected AppointmentService $service) {}

    public function availability(Request $request)
    {
        $validated = $request->validate([
            'reason_id' => 'required|exists:appointment_reasons,id',
            'date' => 'required|date|after_or_equal:today',
            'time_from' => 'required|date_format:H:i',
            'time_to' => 'required|date_format:H:i',
            'doctor_id' => 'nullable|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
        ]);

        $reason = AppointmentReason::with('speciality')->findOrFail($validated['reason_id']);

        try {
            $preview = $this->service->getAvailabilityPreview(
                $reason->speciality_id,
                $validated['date'],
                $validated['time_from'],
                $validated['time_to'],
                $validated['doctor_id'] ?? null,
                $validated['appointment_id'] ?? null
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        $availableDoctors = collect($preview)->where('available', true)->values()->all();

        return response()->json([
            'speciality' => $reason->speciality->name,
            'has_availability' => !empty($availableDoctors),
            'doctors' => $availableDoctors,
        ]);
    }
}
