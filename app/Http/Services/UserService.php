<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{

    /**
     * Retrieve a paginated list of users.
     *
     * @return \Illuminate\Http\JsonResponse JSON response containing the paginated users.
     */
    public function show()
    {
        $users = User::get();
        return response()->json(['users' => $users], 200);
    }

    /**
     * Add a new user with the provided data.
     *
     * @param array $data The user data including 'name', 'email', and 'password'.
     * @return \Illuminate\Http\JsonResponse JSON response with the result of the user creation.
     */
    public function addUser(array $data)
    {

        try {
            $data['is_admin'] = $data['is_admin'] ?? false;
            $user = User::create($data);
            return response()->json([
                'message' => 'User created successfully',
                'user' => $user
            ], 201);
        } catch (UnauthorizedException $e) {
            return response()->json([
                'message' => 'User does not have the right roles'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating User',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve a specific user by ID.
     *
     * @param int $id The ID of the user to retrieve.
     * @return \Illuminate\Http\JsonResponse JSON response containing the user data or an error message.
     */
    public function showUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User Not Exist'], 404);
        }
        return response()->json(['user' => $user], 200);
    }

    /**
     * Update the specified user with the provided data.
     *
     * @param array $data The data to update the user with.
     * @param User $user The user instance to update.
     * @return \Illuminate\Http\JsonResponse JSON response with the result of the update.
     */
    public function updateUser(array $data, User $user)
    {
        if (!$user) {
            return response()->json(['message' => 'User Not Exist'], 404);
        }
        try {
            $user->update($data);
            return response()->json([
                'message' => 'User Info Updated Successfully',
                'user' => $user
            ], 200);
        } catch (UnauthorizedException $e) {
            return response()->json([
                'message' => 'User does not have the right roles'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during the update process',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the specified user.
     *
     * @param User $user The user instance to delete.
     * @return \Illuminate\Http\JsonResponse JSON response with the result of the deletion.
     */
    public function delete(int $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->projects()->detach();
            $user->delete();
            return response()->json(['message' => 'User Deleted Successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found'
            ], 404); // Use 404 for not found
        } catch (UnauthorizedException $e) {
            return response()->json([
                'message' => 'User does not have the right roles'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during the delete process',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Retrieve all tasks associated with the projects that the authenticated user is involved in.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tasksOfUserProjects()
    {
        // Get the authenticated user
        $user = auth()->user();

        // Fetch all tasks related to the projects that the user is working on
        $tasks = $user->projectTasks;

        // If tasks are found, return them with a 200 OK response
        if ($tasks) {
            return response()->json($tasks, 200); // Return all tasks in projects the user is working on
        }

        // If no tasks are found, return a 404 response with a message
        return response()->json(['message' => 'No Tasks Working on!'], 404);
    }

    /**
     * Retrieve all tasks that are specifically assigned to the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userTasks()
    {
        // Get the authenticated user
        $user = auth()->user();

        // Fetch all tasks assigned directly to the user
        $tasks = $user->userTasks;

        // If no tasks are found, return a 404 response
        if ($tasks->count() == 0) {
            return response()->json(['message' => 'No tasks found for you'], 404);
        }

        // Return the found tasks with a 200 OK response
        return response()->json($tasks);
    }

    /**
     * Retrieve the most recently created task for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLatestestTask()
    {
        // Get the authenticated user
        $user = auth()->user();

        // Fetch the latest task created for the user
        $tasks = $user->latestTask;

        // If no tasks are found, return a 404 response
        if (!$tasks) {
            return response()->json(['message' => 'No tasks found for you'], 404);
        }

        // Return the latest task with a 200 OK response
        return response()->json($tasks);
    }

    /**
     * Retrieve the oldest task (based on creation date) for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOldestTask()
    {
        // Get the authenticated user
        $user = auth()->user();

        // Fetch the oldest task assigned to the user
        $tasks = $user->oldestTask;

        // If no tasks are found, return a 404 response
        if (!$tasks) {
            return response()->json(['message' => 'No tasks found for you'], 404);
        }

        // Return the oldest task with a 200 OK response
        return response()->json($tasks);
    }

    /**
     * Retrieve the most important task for the authenticated user based on priority and duration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImportantTask()
    {
        // Get the authenticated user
        $user = auth()->user();

        // Fetch the highest-priority task with the longest duration
        $tasks = $user->longTimeHigPriorityhTask;

        // If no tasks are found, return a 404 response
        if (!$tasks) {
            return response()->json(['message' => 'No tasks found for you'], 404);
        }

        // Return the most important task with a 200 OK response
        return response()->json($tasks);
    }
    public function detachUser(User $user)
    {
        try {
            if (!$user->projects()->exists()) {
                return response()->json(['message' => 'User not attach any project'], 404);
            }
            $user->projects()->detach();
            return response()->json([
                'message' => 'User detached successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found'
            ], 404); // Use 404 for not found
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors (e.g., foreign key constraint violations)
            return response()->json([
                'message' => 'An error occurred while detaching the user'
            ], 500);
        } catch (\Exception $e) {
            // Handle other types of exceptions
            return response()->json([
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }
}
