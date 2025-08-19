<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementEventController extends Controller
{
    public function index()
    {
        return AnnouncementEvent::all();
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
            $validated['image'] = 'storage/' . $path; // store public path
        }

        $record = AnnouncementEvent::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return AnnouncementEvent::findOrFail($id);
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
            // Delete old image if exists
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

        // Delete stored image if exists
        if ($record->image && Storage::disk('public')->exists(str_replace('storage/', '', $record->image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $record->image));
        }

        $record->delete();

        return response()->json(null, 204);
    }
}
