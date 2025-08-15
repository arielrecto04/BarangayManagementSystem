<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementEvent;
use Illuminate\Http\Request;

class AnnouncementEventController extends Controller
{
    public function index()
    {
        return AnnouncementEvent::all();
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'type' => 'required|in:announcement,event',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:10240'
        ]);

        $record = AnnouncementEvent::create($validate);

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
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:10240'
        ]);

        $record = AnnouncementEvent::findOrFail($id);
        $record->update($validated);

        return response()->json($record, 200);
    }

    public function destroy($id)
    {
        AnnouncementEvent::destroy($id);
        return response()->json(null, 204);
    }
}
