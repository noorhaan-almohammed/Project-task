<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Services\UserService;
use App\Http\Requests\UserRequest\UpdateUserForm;
use App\Http\Requests\UserRequest\StoreUserRequest;

class UserController extends Controller
{
    /**
     * User service instance
     */
    protected $userService;

    /**
     * Constructor to inject UserService
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Show all users
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->userService->show();
    }

    /**
     * Create a new user
     *
     * @param StoreUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUserRequest $request)
    {
        // Validate the request
        $validated = $request->validated();

        // Call user service to add a new user
        return $this->userService->addUser($validated);
    }

    /**
     * Show a specific user by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->userService->showUser($id);
    }

    /**
     * Update a user's information
     *
     * @param UpdateUserForm $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserForm $request, User $user)
    {
        // Validate the request
        $data = $request->validated();

        // Call user service to update the user's information
        return $this->userService->updateUser($data, $user);
    }

    /**
     * Delete a user
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        // Call user service to delete the user
        return $this->userService->delete($id);
    }

    public function detachUser(User $user){
        return $this->userService->detachUser($user);
    }
    /**
     * Retrieve all tasks associated with the projects that the authenticated user is involved in.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tasksOfUserProjects()
    {
        // Delegate the logic to the UserService to retrieve the tasks of the user's projects
        return $this->userService->tasksOfUserProjects();
    }

    /**
     * Retrieve all tasks that are specifically assigned to the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userTasks()
    {
        // Delegate the logic to the UserService to retrieve tasks assigned to the user
        return $this->userService->userTasks();
    }

    /**
     * Retrieve the most recently created task for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLatestestTask()
    {
        // Delegate the logic to the UserService to retrieve the latest task
        return $this->userService->getLatestestTask();
    }

    /**
     * Retrieve the oldest task (based on creation date) for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOldestTask()
    {
        // Delegate the logic to the UserService to retrieve the oldest task
        return $this->userService->getOldestTask();
    }

    /**
     * Retrieve the most important task (based on priority) for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImportantTask()
    {
        // Delegate the logic to the UserService to retrieve the most important task
        return $this->userService->getImportantTask();
    }


}
