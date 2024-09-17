<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Services\TaskService;
use App\Http\Requests\TaskRequest\storeTaskRequest;
use App\Http\Requests\TaskRequest\AssignTaskRequest;
use App\Http\Requests\TaskRequest\UpdateTaskRequest;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

        // السماح فقط للمطورين بتحديث حالة المهام
        // $this->middleware('check.role:developer')->only('updateStatus');

        // السماح للمديرين بالوصول الكامل
        // $this->middleware('check.role:manager')->only('update');

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->taskService->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(storeTaskRequest $request)
    {
        $validatedData = $request->validated();
        return $this->taskService->createTask($validatedData);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $task)
    {
        return $this->taskService->show($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $validatedData = $request->validated();
        return $this->taskService->updateTask($task , $validatedData);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->taskService->delete($id);
    }
    public function assignTask(AssignTaskRequest $request, $taskId)
    {
        $validated = $request->validated();
        return $this->taskService->assignTask($validated, $taskId);
    }
    public function unAssignTask($taskId)
    {
        return $this->taskService->unAssignTask( $taskId);
    }
    public function updateStatusTask(Request $request, $taskId){
        return $this->taskService->updateStatusTask($request,$taskId);
    }

    public function updateStatusTaskTester(Request $request, $taskId){
        return $this->taskService->updateStatusTaskTester($request,$taskId);
    }
}
