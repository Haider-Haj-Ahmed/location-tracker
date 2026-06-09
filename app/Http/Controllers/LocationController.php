
<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

// app/Http/Controllers/LocationController.php
class LocationController extends Controller
{
    public function store(Request $request)
    {
        // Authenticate by token in header
        $device = Device::where('token', $request->header('X-Device-Token'))
            ->where('is_active', true)
            ->firstOrFail();

        $data = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'nullable|numeric',
            'speed'     => 'nullable|numeric',
            'heading'   => 'nullable|numeric',
            'altitude'  => 'nullable|numeric',
        ]);

        $location = $device->locations()->create($data);

        // Update last seen
        $device->update(['last_seen_at' => now()]);

        return response()->json(['status' => 'ok', 'id' => $location->id], 201);
    }

    // Called by dashboard to poll latest positions
    public function latest()
    {
        $devices = Device::with('latestLocation')
            ->where('is_active', true)
            ->get()
            ->map(fn($d) => [
                'id'        => $d->id,
                'name'      => $d->name ?? 'Device #' . $d->id,
                'last_seen' => $d->last_seen_at?->diffForHumans(),
                'online'    => $d->last_seen_at?->gt(now()->subSeconds(30)),
                'location'  => $d->latestLocation ? [
                    'lat' => $d->latestLocation->latitude,
                    'lng' => $d->latestLocation->longitude,
                    'accuracy' => $d->latestLocation->accuracy,
                    'speed'    => $d->latestLocation->speed,
                    'time'     => $d->latestLocation->created_at->toISOString(),
                ] : null,
            ]);

        return response()->json($devices);
    }
}
