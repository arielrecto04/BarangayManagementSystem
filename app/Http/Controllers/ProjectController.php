<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::paginate(9);
        return response()->json([
            'projects' => $projects
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'status' => 'nullable|string',
            'start_date' => 'nullable|date',
            'target_completion' => 'nullable|date',
            'actual_completion' => 'nullable|date',
            'funding_source' => 'nullable|string',
            'barangay_zone' => 'nullable|string',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'milestone_achieved' => 'nullable|string',
            'project_lead' => 'nullable|string',
            'assigned_organizations' => 'nullable|array',
            'number_of_members' => 'nullable|integer|min:0',
            'site_address' => 'nullable|string',
            'disbursement_schedule' => 'nullable|string',
            'challenges_encountered' => 'nullable|string',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        // Handle multiple file uploads
        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $file_name = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('projects', $file_name, 'public');
                $files[] = asset('storage/projects/' . $file_name);
            }
        }

        $validated['files'] = $files;
        $project = Project::create($validated);

        return response()->json([
            'message' => 'Project created successfully',
            'data' => $project,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::findOrFail($id);
        return response()->json($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            // ... your validation rules ...
        ]);

        $project = Project::findOrFail($id);

        // Handle file updates
        $files = $project->files ?? [];

        // Handle existing files
        if ($request->has('existing_files')) {
            $files = $request->existing_files;
        }

        // Add new files
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $file_name = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('projects', $file_name, 'public');
                $files[] = asset('storage/projects/' . $file_name);
            }
        }

        $validated['files'] = $files;

        // Handle assigned_organizations if it's a JSON string
        if ($request->has('assigned_organizations') && is_string($request->assigned_organizations)) {
            $validated['assigned_organizations'] = json_decode($request->assigned_organizations, true);
        }

        $project->update($validated);

        return response()->json([
            'message' => 'Project updated successfully',
            'data' => $project,
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);

        // Optionally delete uploaded files
        if ($project->files) {
            foreach ($project->files as $file) {
                $path = str_replace(asset('storage/'), '', $file);
                Storage::disk('public')->delete($path);
            }
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully',
        ], 200);
    }

    /**
     * Search projects by multiple fields.
     */
    public function search(Request $request)
    {
        $search = $request->search;

        $projects = Project::where('title', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->orWhere('status', 'like', "%{$search}%")
            ->orWhere('category', 'like', "%{$search}%")
            ->orWhere('funding_source', 'like', "%{$search}%")
            ->orWhere('barangay_zone', 'like', "%{$search}%")
            ->paginate(9);

        return response()->json([
            'projects' => $projects
        ]);
    }
}
