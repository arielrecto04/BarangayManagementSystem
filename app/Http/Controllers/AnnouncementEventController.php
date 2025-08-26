<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        Log::info("UPDATE REQUEST", [
            'id' => $id,
            'all_data' => $request->all(),
            'files' => $request->allFiles(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type')
        ]);

        $record = AnnouncementEvent::findOrFail($id);

        Log::info("BEFORE UPDATE", [
            'record' => $record->toArray()
        ]);

        // Get all the request data first
        $requestData = $request->all();
        Log::info("REQUEST DATA", ['request_data' => $requestData]);

        // Build validation rules dynamically based on what fields are present
        $rules = [];

        if ($request->has('type')) {
            $rules['type'] = 'required|in:announcement,event';
        }

        if ($request->has('title')) {
            $rules['title'] = 'required|string|max:255';
        }

        if ($request->has('description')) {
            $rules['description'] = 'nullable|string';
        }

        if ($request->has('start_date')) {
            $rules['start_date'] = 'nullable|date';
        }

        if ($request->has('end_date')) {
            $rules['end_date'] = 'nullable|date|after_or_equal:start_date';
        }

        if ($request->has('location')) {
            $rules['location'] = 'nullable|string|max:255';
        }

        if ($request->has('author')) {
            $rules['author'] = 'nullable|string|max:255';
        }

        if ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpg,jpeg,png,gif|max:10240';
        }

        Log::info("VALIDATION RULES", ['rules' => $rules]);

        // Validate only the fields that are present
        $validated = $request->validate($rules);

        Log::info("VALIDATED DATA", ['validated' => $validated]);

        // Handle file upload if present
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($record->image && Storage::disk('public')->exists(str_replace('storage/', '', $record->image))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $record->image));
            }

            $path = $request->file('image')->store('announcement_events', 'public');
            $validated['image'] = 'storage/' . $path;
        }

        // Convert dates to proper format if they exist
        if (isset($validated['start_date']) && $validated['start_date']) {
            $validated['start_date'] = Carbon::parse($validated['start_date'])->format('Y-m-d H:i:s');
        }

        if (isset($validated['end_date']) && $validated['end_date']) {
            $validated['end_date'] = Carbon::parse($validated['end_date'])->format('Y-m-d H:i:s');
        }

        Log::info("FINAL DATA TO UPDATE", ['final_validated' => $validated]);

        // Update the record
        $updated = $record->update($validated);

        Log::info("UPDATE RESULT", [
            'success' => $updated,
            'record_after' => $record->fresh()->toArray()
        ]);

        // Refresh the record to get the latest data
        $record->refresh();

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
