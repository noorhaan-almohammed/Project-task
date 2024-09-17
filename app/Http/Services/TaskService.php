<?php

namespace App\Http\Services;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskService
{

    public function createTask(array $data)
    {
        try {
            $project = Project::find($data['project_id']);
            if (!$project) {
                return response()->json(['error' => 'Project not found'], 404);
            }

            $taskData = [
                'project_id' => $data['project_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'execute_time' => $data['execute_time'],
                'priority' => $data['priority'],
                'user_id' => null,
            ];

            if (!isset($data['user_id']) || empty($data['user_id'])) {
                $taskData['status'] = 'New';
            }  else {
                $user = User::find($data['user_id']);
                if (!$user) {
                    return response()->json(['error' => 'User not found'], 404);
                }
                $projectUser = $project->users()->where('users.id', $data['user_id'])->first();
                if (!$projectUser) {
                    return response()->json(['error' => 'User is not assigned to this project'], 403);
                }

                if ($projectUser->pivot->role !== 'Developer') {
                    return response()->json(['error' => 'User is not a developer in this project'], 403);
                }

                $taskData['user_id'] = $data['user_id'];
                $taskData['status'] = 'Pending';
                $taskData['due_date'] = Carbon::now()->addDays($data['execute_time']);
            }

            $task = Task::create($taskData);

            return response()->json([
                'message' => 'Task created successfully',
                'task' => $task
            ], 201);

        } catch (UnauthorizedException $e) {
            return response()->json([
                'message' => 'User does not have the right roles'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating Task',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateTask(Task $task, array $data)
    {
        try {
            if ($task->status === 'New') {
                if (isset($data['project_id'])) {
                    $project = Project::find($data['project_id']);
                    if (!$project) {
                        return response()->json(['error' => 'Project not found'], 404);
                    }

                    if (isset($data['user_id'])) {
                        $user = User::find($data['user_id']);
                        if (!$user) {
                            return response()->json(['error' => 'User not found'], 404);
                        }

                        $projectUser = $project->users()->where('users.id', $data['user_id'])->first();
                        if (!$projectUser) {
                            return response()->json(['error' => 'User is not assigned to this project'], 403);
                        }

                        // تحقق مما إذا كان المستخدم لديه دور "developer"
                        if ($projectUser->pivot->role !== 'Developer') {
                            return response()->json(['error' => 'User is not a developer in this project'], 403);
                        }
                    }

                    $taskData = [
                        'project_id' => $data['project_id'],
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'execute_time' => $data['execute_time'],
                        'priority' => $data['priority'],
                        'status' => 'Pending', // تغيير الحالة إلى "Pending"
                        'due_date' => isset($data['user_id']) ? Carbon::now()->addDays($data['execute_time']) : null,
                    ];

                    if (isset($data['user_id'])) {
                        $taskData['user_id'] = $data['user_id'];
                    }

                    $task->update($taskData);

                    return response()->json([
                        'message' => 'Task updated successfully',
                        'task' => $task
                    ], 200);
                } else {
                    $task->update($data);

                    return response()->json([
                        'message' => 'Task updated successfully',
                        'task' => $task
                    ], 200);
                }
            } else {
                if (isset($data['project_id'])) {
                    return response()->json(['error' => 'Cannot change project_id when task status is not New'], 403);
                }

                $task->update($data);

                return response()->json([
                    'message' => 'Task updated successfully',
                    'task' => $task
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating Task',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function index($request)
    {
        try {

            $query = Task::query();

            if ($request->has('project_id')) {
                $projectId = $request->query('project_id');
                $project = Project::find($projectId);
                if (!$project) {
                    return response()->json(['error' => 'Project not found'], 404);
                }
                $query->where('project_id', $projectId);
            }

            if ($request->has('status')) {
                $status = $request->query('status');
                $query->where('status', $status);
            }

            if ($request->has('priority')) {
                $priority = $request->query('priority');
                $query->where('priority', $priority);
            }

            $tasks = $query->get();

            if ($tasks->isEmpty()) {
                return response()->json(['message' => 'No tasks found matching the criteria'], 404);
            }

            return response()->json($tasks);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            $task = Task::findOrFail($id);
            return response()->json($task);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching task',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function delete($id)
    {
        try {
            $task = Task::findOrFail($id);
            if ($task->user_id) {
                return response()->json(['message' => 'Task cannot be deleted as it is assigned to a user'], 403);
            }
            $task->delete();
            return response()->json(['message' => 'Task Deleted Successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching task',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function assignTask($validated, $taskId)
    {

        try {
            $task = Task::findOrFail($taskId);

            if ($task->user_id) {
                return response()->json(['error' => 'Task already assigned'], 403);
            }

            $user = User::findOrFail($validated['user_id']);

            $project = $task->project;

            if (!$project->users()->where('users.id', $user->id)->exists()) {
                return response()->json(['error' => 'User is not assigned to this project'], 403);
            }

            $userRole = $project->users()->where('users.id', $user->id)->first()->pivot->role;
            if ($userRole !== 'Developer') {
                return response()->json(['error' => 'User is not a developer in this project'], 403);
            }

            $task->update([
                'user_id' => $user->id,
                'status' => 'Pending',
                'due_date' => Carbon::now()->addDays($validated['execute_time'] ?? $task['execute_time']),
            ]);

            return response()->json([
                'message' => 'Task assigned successfully',
                'task' => $task
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task or User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error assigning task', 'details' => $e->getMessage()], 500);
        }
    }

    public function unAssignTask($taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            if (in_array($task->status, ['Completed', 'In Testing', 'Successed', 'Failed'])) {
                return response()->json(['error' => 'Cannot unassign user from a task with status: Completed ,In Testing , Successed , Failed'], 403);
            }
            if (!$task->user_id) {
                return response()->json(['error' => 'No user assigned to this task'], 403);
            }
            $task->user_id = null;
            $task->status = 'New';
            $task->due_date = null;
            $task->save();

            return response()->json([
                'message' => 'User unassigned successfully',
                'task' => $task
            ], 200);

            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Task not found'], 404);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Error unassigning user', 'details' => $e->getMessage()], 500);
        }
    }
    public function updateStatusTask($request, $taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            $user = $request->user();
            // $user = User::findOrFail(3);
            $project = $task->project;
            if (!$project->users()->where('users.id', $user->id)->exists()) {
                return response()->json(['error' => 'User is not assigned to this project'], 403);
            }
            $userRole = $project->users()->where('users.id', $user->id)->first()->pivot->role;

            if ($task->user_id != $user->id || $userRole != 'Developer') {
                return response()->json(['error' => 'You are not authorized to update this task'], 403);
            }

            $notAllowedStatuses = ['Completed', 'In Testing', 'Successed', 'Failed'];
            if (in_array($task->status, $notAllowedStatuses)) {
                return response()->json(['error' => 'Cannot update task with the current status'], 403);
            }

            $newStatus = $request->input('status');
            if (!in_array($newStatus, ['Pending', 'Completed', 'In Progress'])) {
                return response()->json(['error' => 'Invalid status'], 400);
            }

            if ($newStatus == 'In Progress') {
                $task->start_date = now();
            }

            if (in_array($newStatus, ['Pending', 'Completed'])) {
                $projectUser = $user->projects()->where('projects.id', $task->project_id)->first();
                    $contributionHours = $projectUser->pivot->contribution_hours;
                    $startDate = $task->start_date;
                    $updateDate = now();
                    $hours = $updateDate->diffInHours($startDate);
                    $projectUser->pivot->contribution_hours = $hours + $contributionHours;
                    $projectUser->pivot->save();

            }
            $task->status =$newStatus;
            $task->save();
            return response()->json([
                'message' => 'Task status updated successfully',
                'task' => $task
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error updating task status', 'details' => $e->getMessage()], 500);
        }
    }
    public function updateStatusTaskTester($request, $taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            $user = $request->user();
            // $user = User::findOrFail(2);
            $tester_note = $request->tester_note;
            $project = $task->project;
            if (!$project->users()->where('users.id', $user->id)->exists()) {
                return response()->json(['error' => 'User is not assigned to this project'], 403);
            }
            $userRole = $project->users()->where('users.id', $user->id)->first()->pivot->role;

            if ($task->user_id != $user->id || $userRole != 'Tester') {
                return response()->json(['error' => 'You are not authorized to update this task'], 403);
            }

            $notAllowedStatuses = ['New','Successed', 'Failed', 'Pending','In Progress'];
            if (in_array($task->status, $notAllowedStatuses)) {
                return response()->json(['error' => 'Cannot update task with the current status'], 403);
            }

            $newStatus = $request->input('status');
            if (!in_array($newStatus, ['Successed', 'Failed', 'In Testing'])) {
                return response()->json(['error' => 'Invalid status'], 400);
            }

            if ($newStatus == 'In Testing') {
                $task->start_date = now();
            }

            if (in_array($newStatus, ['Successed', 'Failed'])) {
                $projectUser = $user->projects()->where('projects.id', $task->project_id)->first();
                    $contributionHours = $projectUser->pivot->contribution_hours;
                    $startDate = $task->start_date;
                    $updateDate = now();
                    $hours = $updateDate->diffInHours($startDate);
                    $projectUser->pivot->contribution_hours = $hours + $contributionHours;
                    $projectUser->pivot->save();
            }
            $task->status =  $newStatus;
            $task->save();
            return response()->json([
                'message' => 'Task status updated successfully',
                'task' => $task
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error updating task status', 'details' => $e->getMessage()], 500);
        }
    }


}
