<?php

namespace App\Http\Services;

use Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ProjectService
{
    public function addProject(array $data)
    {
        try {
            $project = Project::create($data);
            if ($project->wasRecentlyCreated) {
                $msg = "";
                if (isset($data['user_id']) && $data['user_id']) {
                    $userId = $data['user_id'];
                   $this->assignMnager($userId , $project);
                   $user = User::find($userId);
                   $msg = "And Project Assigned To {$user->name}";
                }
                return response()->json([
                    'message' => "Project created successfully {$msg}",
                    'project' => $project
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating project',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function assignMnager($userId, Project $project)
    {
        try {
            $role = 'Manager';
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'messages' => "User with ID " . $userId . " not found"
                ], 404);
            }
            // Attach user to project
            $project->users()->syncWithoutDetaching([
                $userId => [
                    'role' => $role,
                    'contribution_hours' => 0,
                    'last_activity' => now()
                ]
            ]);
            return response()->json([
                'messages' => 'User ' . $user->name . ' assigned to project ' . $project->name . ' as ' . $role . ' successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error assigning users to project',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function assignUsersToProject(array $data, Project $project)
    {
        try {
            $userId = $data['user_id'];
            $role = $data['role'];
            if ($role == 'Mnager') {
                return response()->json([
                    'messages' => "Project can has one Manager"
                ], 404);
            }
            // Validate user
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'messages' => "User with ID " . $userId . " not found"
                ], 404);
            }
            // Attach user to project
            $project->users()->syncWithoutDetaching([
                $userId => [
                    'role' => $role,
                    'contribution_hours' => 0,
                    'last_activity' => now()
                ]
            ]);
            // $manager_id = 10;
            $manager_id = Auth::user()->id;
            // $manager = $project->users();
            $manager_u = User::find($manager_id);
            // dd($manager_u);
            $this->updateManagerContributionHours($project, $manager_u);

            // Add success message
            return response()->json([
                'messages' => 'User ' . $user->name . ' assigned to project ' . $project->name . ' as ' . $role . ' successfully'
            ], 200);

        } catch (UnauthorizedException $e) {
            return response()->json([
                'message' => 'User does not have the right roles'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error assigning users to project',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function assignManyUsersToProject(array $data, Project $project)
    {
        $responses = [];
        try {
            // Check if data contains a single user or an array of users
            $usersData = isset($data['users']) ? $data['users'] : [$data];

            // Validate that the users data is an array
            if (!is_array($usersData)) {
                return response()->json([
                    'message' => 'Invalid users data'
                ], 400);
            }

            // Loop through each user-role pair
            foreach ($usersData as $userData) {
                // Validate the presence of user_id and role
                if (!isset($userData['user_id']) || !isset($userData['role'])) {
                    $responses[] = 'Missing user_id or role for one or more users';
                    continue; // Skip to the next iteration
                }

                $userId = $userData['user_id'];
                $role = $userData['role'];
                if ($role == 'Mnager') {
                    return response()->json([
                        'messages' => "Project can has one Manager"
                    ], 404);
                }
                // Validate user
                $user = User::find($userId);
                if (!$user) {
                    $responses[] = "User with ID {$userId} not found";
                    continue; // Skip to the next iteration
                }

                // Attach user to project
                $project->users()->syncWithoutDetaching([
                    $userId => [
                        'role' => $role,
                        'contribution_hours' => 0,
                        'last_activity' => now()
                    ]
                ]);
                // $manager_id = 10;
                $manager_id = Auth::user()->id;
                // $manager = $project->users();
                $manager_u = User::find($manager_id);
                // dd($manager_u);
                $this->updateManagerContributionHours($project, $manager_u);


                // Add success message
                $responses[] = "User {$user->name} assigned to project {$project->name} as {$role} successfully";
            }

            if (empty($responses)) {
                return response()->json([
                    'message' => 'No users were processed'
                ], 400);
            }

            return response()->json([
                'messages' => $responses
            ], 201);

        } catch (UnauthorizedException $e) {
            return response()->json([
                'message' => 'User does not have the right roles'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error assigning users to project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
          return response()->json([
                'message' => 'Project not found',
            ], 404);
        }
       $project->load([
            'users' => function ($query) {
                $query->withPivot('role', 'contribution_hours', 'last_activity');
            }
        ]);

        return response()->json([
            'project' => $project,
        ], 200);
    }

    public function update(array $data, Project $project)
    {
        $project->name = $data['name'] ? $data['name']:$project->name;
        if (isset($data['description'])) {
            $project->description = $data['description'];
        }
// dd(  $data );
        $project->save();

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project
        ], 200);
    }
    public function delete(int $id)
    {
        try {
            // Find the project by ID or fail
            $project = Project::findOrFail($id);
            $project->users()->detach();
            $project->delete();
            return response()->json([
                'message' => 'Project deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Project not found'
            ], 404); // Use 404 for not found
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors (e.g., foreign key constraint violations)
            return response()->json([
                'message' => 'An error occurred while deleting the project'
            ], 500);
        } catch (\Exception $e) {
            // Handle other types of exceptions
            return response()->json([
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }
    public function detachProject(Project $project)
    {
        try {
            if (!$project->users()->exists()) {
                return response()->json(['message' => 'No user Work at this Project'], 404);
            }
            $project->users()->detach();
            return response()->json([
                'message' => 'Project detached successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Project not found'
            ], 404); // Use 404 for not found
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors (e.g., foreign key constraint violations)
            return response()->json([
                'message' => 'An error occurred while detaching the project'
            ], 500);
        } catch (\Exception $e) {
            // Handle other types of exceptions
            return response()->json([
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }
    public function updateManagerContributionHours(Project $project, User $manager)
    {
        try {
            $managerRole = $project->users()->where('user_id', $manager->id)->where('role', 'Manager')->first();
            if (!$managerRole) {
                throw new \Exception('User is not a Manager in this project');
            }

            $users = $project->users;
            $totalContributionHours = 0;

            foreach ($users as $user) {
                $userContributionHours = $user->pivot->contribution_hours;
                $totalContributionHours += $userContributionHours;
            }


            $project->users()->syncWithoutDetaching([
                $manager->id => [
                    'contribution_hours' => $totalContributionHours + 55,
                    'last_activity' => now()
                ]
            ]);
            return response()->json([
                'message' => 'Manager contribution hours updated successfully',
                'total_contribution_hours' => $totalContributionHours
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating manager contribution hours',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
