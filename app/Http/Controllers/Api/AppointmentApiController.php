<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppointmentReason;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class AppointmentApiController extends Controller
{
    public function __construct(protected AppointmentService $service) {}

    /**
     * GET /api/availability?reason_id=&from=&to=
     */
    public function availability(Request $request)
    {
        $request->validate([
            'reason_id' => 'required|exists:appointment_reasons,id',
            'from'      => 'required|date',
            'to'        => 'required|date|after:from',
        ]);

        $reason = AppointmentReason::with('speciality')->findOrFail($request->reason_id);

        $preview = $this->service->getAvailabilityPreview(
            $reason->speciality_id,
            $request->from,
            $request->to
        );

        $hasAvailability = collect($preview)->where('available', true)->isNotEmpty();

        return response()->json([
            'speciality'        => $reason->speciality->name,
            'has_availability' => $hasAvailability,
            'doctors'          => $preview,
        ]);
    }
}
