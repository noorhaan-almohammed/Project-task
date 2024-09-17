<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Services\ProjectService;
use App\Http\Requests\ProjectRequest\storeRequest;
use App\Http\Requests\ProjectRequest\updateRequest;
use App\Http\Requests\ProjectRequest\assignUsresRequest;
use App\Http\Requests\ProjectRequest\assignManagerRequest;
use App\Http\Requests\ProjectRequest\assignManyUsresRequest;

class ProjectController extends Controller
{
    /**
     * project service instance
     */
    protected $projectService;

    /**
     * Constructor to inject ProjectService
     *
     * @param ProjectService $projectService
     */
    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }
    public function getTasksWithFilter(Request $request)
    {
        $priority = $request->query('priority');
        $status = $request->query('status');
        $project_id = $request->query('project_id');
        $project = new Project();

        $tasks = $project->tasksWithFilter($priority, $status, $project_id);

        if ($tasks->isEmpty()) {
            return response()->json(['message' => 'No tasks found matching the criteria'], 404);
        }

        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(storeRequest $request)
    {
        // Validate the request
        $validated = $request->validated();

        // Call user service to add a new user
        return $this->projectService->addProject($validated);
    }
    public function assignMnager(assignManagerRequest $request, Project $project){
        $userId = $request->validated();

        return $this->projectService->assignMnager($userId['user_id'],$project);
    }
    public function assignUsersToProject(assignUsresRequest $request, Project $project){
        $validated = $request->validated();
        return $this->projectService->assignUsersToProject($validated,$project);
    }
    public function assignManyUsersToProject(assignManyUsresRequest $request, Project $project){
        $validated = $request->validated();
        return $this->projectService->assignManyUsersToProject($validated,$project);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return $this->projectService->show($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(updateRequest $request, Project $project)
    {
        $validated = $request->validated();
        return $this->projectService->update($validated,$project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->projectService->delete($id);
    }

    public function detachProject(Project $project){
        return $this->projectService->detachProject($project);
    }
}
