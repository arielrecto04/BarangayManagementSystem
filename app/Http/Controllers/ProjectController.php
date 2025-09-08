<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;;

use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $projects = Project::paginate(9);
            return response()->json([
                'projects' => $projects
            ]);
        } catch (\Exception $e) {
            Log::error('Project index failed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to retrieve projects',
                'errors' => ['general' => ['An unexpected error occurred while retrieving projects.']]
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Log incoming request for debugging
            Log::info('Project store request:', [
                'has_files' => $request->hasFile('files'),
                'file_count' => $request->hasFile('files') ? count($request->file('files')) : 0,
                'all_keys' => array_keys($request->all())
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:255',
                'start_date' => 'nullable|date',
                'target_completion' => 'nullable|date|after_or_equal:start_date',
                'actual_completion' => 'nullable|date',
                'funding_source' => 'nullable|string|max:255',
                'barangay_zone' => 'nullable|string|max:255',
                'completion_percentage' => 'nullable|integer|min:0|max:100',
                'milestone_achieved' => 'nullable|string',
                'project_lead' => 'nullable|string|max:255',
                'assigned_organizations' => 'nullable|array',
                'assigned_organizations.*' => 'string|max:255',
                'number_of_members' => 'nullable|integer|min:0',
                'site_address' => 'nullable|string',
                'disbursement_schedule' => 'nullable|string',
                'challenges_encountered' => 'nullable|string',
                // Fixed: Make files completely optional and validate properly
                'files' => 'nullable|array',
                'files.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240', // 10MB max per file
            ]);

            // Handle multiple file uploads
            $fileUrls = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if ($file && $file->isValid()) {
                        try {
                            $originalName = $file->getClientOriginalName();
                            $extension = $file->getClientOriginalExtension();
                            $fileName = time() . '_' . uniqid() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.' . $extension;

                            $filePath = $file->storeAs('projects', $fileName, 'public');
                            $fileUrls[] = Storage::url($filePath);

                            Log::info('File uploaded successfully:', [
                                'original_name' => $originalName,
                                'stored_name' => $fileName,
                                'path' => $filePath
                            ]);
                        } catch (\Exception $e) {
                            Log::error('File upload failed:', [
                                'file' => $originalName ?? 'unknown',
                                'error' => $e->getMessage()
                            ]);
                            // Continue with other files instead of failing completely
                        }
                    }
                }
            }

            // Always set files, even if empty array
            $validated['files'] = $fileUrls;

            // Ensure assigned_organizations is properly handled
            if (isset($validated['assigned_organizations']) && is_array($validated['assigned_organizations'])) {
                $validated['assigned_organizations'] = array_filter($validated['assigned_organizations']);
            } else {
                $validated['assigned_organizations'] = [];
            }

            Log::info('Creating project with data:', [
                'title' => $validated['title'],
                'files_count' => count($validated['files']),
                'organizations_count' => count($validated['assigned_organizations'])
            ]);

            $project = Project::create($validated);

            return response()->json([
                'message' => 'Project created successfully',
                'data' => $project,
            ], 201);
        } catch (ValidationException $e) {
            Log::error('Validation failed:', $e->errors());

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Project creation failed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Project creation failed',
                'errors' => ['general' => ['An unexpected error occurred while creating the project.']]
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $project = Project::findOrFail($id);
            return response()->json([
                'data' => $project
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Project not found',
                'errors' => ['general' => ['The requested project could not be found.']]
            ], 404);
        } catch (\Exception $e) {
            Log::error('Project show failed:', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to retrieve project',
                'errors' => ['general' => ['An unexpected error occurred while retrieving the project.']]
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:255',
                'start_date' => 'nullable|date',
                'target_completion' => 'nullable|date|after_or_equal:start_date',
                'actual_completion' => 'nullable|date',
                'funding_source' => 'nullable|string|max:255',
                'barangay_zone' => 'nullable|string|max:255',
                'completion_percentage' => 'nullable|integer|min:0|max:100',
                'milestone_achieved' => 'nullable|string',
                'project_lead' => 'nullable|string|max:255',
                'assigned_organizations' => 'nullable|array',
                'assigned_organizations.*' => 'string|max:255',
                'number_of_members' => 'nullable|integer|min:0',
                'site_address' => 'nullable|string',
                'disbursement_schedule' => 'nullable|string',
                'challenges_encountered' => 'nullable|string',
                'files' => 'nullable|array',
                'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
                'existing_files' => 'nullable|array', // For keeping existing files
                'existing_files.*' => 'nullable|string',
            ]);

            $project = Project::findOrFail($id);

            // Get current files for comparison to delete removed files
            $currentFiles = $project->files ? (is_array($project->files) ? $project->files : []) : [];

            // Handle file updates - start with existing files that should be kept
            $fileUrls = [];
            if (isset($validated['existing_files']) && is_array($validated['existing_files'])) {
                $fileUrls = array_filter($validated['existing_files']);
            }

            // Determine which files to delete (were in current but not in existing_files)
            $filesToDelete = array_diff($currentFiles, $fileUrls);

            // Delete removed files from storage
            foreach ($filesToDelete as $fileUrl) {
                try {
                    // Extract file path from URL
                    $relativePath = str_replace('/storage/', '', parse_url($fileUrl, PHP_URL_PATH));

                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                        Log::info('Deleted file:', ['path' => $relativePath]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to delete file during update:', [
                        'project_id' => $id,
                        'file_url' => $fileUrl,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other files
                }
            }

            // Add new files
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if ($file && $file->isValid()) {
                        try {
                            $originalName = $file->getClientOriginalName();
                            $extension = $file->getClientOriginalExtension();
                            $fileName = time() . '_' . uniqid() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.' . $extension;

                            $filePath = $file->storeAs('projects', $fileName, 'public');
                            $fileUrls[] = Storage::url($filePath);

                            Log::info('File uploaded successfully during update:', [
                                'project_id' => $id,
                                'original_name' => $originalName,
                                'stored_name' => $fileName,
                                'path' => $filePath
                            ]);
                        } catch (\Exception $e) {
                            Log::error('File upload failed during update:', [
                                'project_id' => $id,
                                'file' => $originalName ?? 'unknown',
                                'error' => $e->getMessage()
                            ]);
                            // Continue with other files
                        }
                    }
                }
            }

            $validated['files'] = array_unique($fileUrls);

            // Ensure assigned_organizations is properly handled
            if (isset($validated['assigned_organizations']) && is_array($validated['assigned_organizations'])) {
                $validated['assigned_organizations'] = array_filter($validated['assigned_organizations']);
            }

            // Remove existing_files from validated data as it's not a model field
            unset($validated['existing_files']);

            $project->update($validated);

            return response()->json([
                'message' => 'Project updated successfully',
                'data' => $project->fresh(),
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Project update validation failed:', [
                'project_id' => $id,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Project not found',
                'errors' => ['general' => ['The requested project could not be found.']]
            ], 404);
        } catch (\Exception $e) {
            Log::error('Project update failed:', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Project update failed',
                'errors' => ['general' => ['An unexpected error occurred while updating the project.']]
            ], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $project = Project::findOrFail($id);

            // Delete uploaded files
            if ($project->files && is_array($project->files)) {
                foreach ($project->files as $fileUrl) {
                    try {
                        // Extract file path from URL
                        $relativePath = str_replace('/storage/', '', parse_url($fileUrl, PHP_URL_PATH));

                        if (Storage::disk('public')->exists($relativePath)) {
                            Storage::disk('public')->delete($relativePath);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to delete file during project deletion:', [
                            'project_id' => $id,
                            'file_url' => $fileUrl,
                            'error' => $e->getMessage()
                        ]);
                        // Continue deleting other files and the project
                    }
                }
            }

            $project->delete();

            return response()->json([
                'message' => 'Project deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Project not found',
                'errors' => ['general' => ['The requested project could not be found.']]
            ], 404);
        } catch (\Exception $e) {
            Log::error('Project deletion failed:', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Project deletion failed',
                'errors' => ['general' => ['An unexpected error occurred while deleting the project.']]
            ], 500);
        }
    }

    /**
     * Search projects by multiple fields.
     */
    public function search(Request $request)
    {
        try {
            $request->validate([
                'search' => 'required|string|min:1|max:255'
            ]);

            $search = $request->search;

            $projects = Project::where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('funding_source', 'like', "%{$search}%")
                    ->orWhere('barangay_zone', 'like', "%{$search}%")
                    ->orWhere('project_lead', 'like', "%{$search}%");
            })->paginate(9);

            return response()->json([
                'projects' => $projects
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Invalid search parameters',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Project search failed:', [
                'search_term' => $request->search ?? 'unknown',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Search failed',
                'errors' => ['general' => ['An unexpected error occurred during search.']]
            ], 500);
        }
    }
}
