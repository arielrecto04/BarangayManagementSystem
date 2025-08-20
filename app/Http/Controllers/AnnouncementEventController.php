<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AnnouncementEventController extends Controller
{
    public function index(Request $request)
    {
        // Get all events and update their status in real-time
        $events = AnnouncementEvent::all();

        // Update statuses for all events before returning
        foreach ($events as $event) {
            $currentStatus = $event->calculateStatus();
            if ($event->status !== $currentStatus) {
                $event->update(['status' => $currentStatus]);
            }
        }

        // Refresh the collection to get updated statuses
        $events = AnnouncementEvent::all();

        // Apply status filter if provided
        if ($request->has('status')) {
            $events = $events->where('status', $request->status);
        }

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:announcement,event',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:10240',
        ]);

        // Handle file upload if present
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('announcement_events', 'public');
            $validated['image'] = 'storage/' . $path;
        }

        $record = AnnouncementEvent::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        $event = AnnouncementEvent::findOrFail($id);

        // Update status before returning
        $currentStatus = $event->calculateStatus();
        if ($event->status !== $currentStatus) {
            $event->update(['status' => $currentStatus]);
            $event->refresh();
        }

        return response()->json($event);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:announcement,event',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:10240',
        ]);

        $record = AnnouncementEvent::findOrFail($id);

        // Handle file upload if present
        if ($request->hasFile('image')) {
            if ($record->image && Storage::disk('public')->exists(str_replace('storage/', '', $record->image))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $record->image));
            }

            $path = $request->file('image')->store('announcement_events', 'public');
            $validated['image'] = 'storage/' . $path;
        }

        $record->update($validated);

        return response()->json($record, 200);
    }

    public function destroy($id)
    {
        $record = AnnouncementEvent::findOrFail($id);

        if ($record->image && Storage::disk('public')->exists(str_replace('storage/', '', $record->image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $record->image));
        }

        $record->delete();

        return response()->json(null, 204);
    }

    // Add a dedicated endpoint to refresh all statuses
    public function refreshStatuses()
    {
        $events = AnnouncementEvent::all();
        $updated = 0;

        foreach ($events as $event) {
            $currentStatus = $event->calculateStatus();
            if ($event->status !== $currentStatus) {
                $event->update(['status' => $currentStatus]);
                $updated++;
            }
        }

        return response()->json([
            'message' => "Updated {$updated} event statuses",
            'updated_count' => $updated
        ]);
    }
}
