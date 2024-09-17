
# Laravel Project Manager

## Overview

This Laravel Project Manager application allows users to manage tasks with role-based access control. It supports task creation, assignment, status updates, and deletion. The application has three main user roles: admin, manager, ttester and developer, each with specific permissions.

## Features

- **Role-based Access Control**: Admins, managers, tester and developers have different permissions.
- **Task and project Management**: Create, update, assign, and delete tasks.
- **Task and project Filtering**: Filter tasks by priority, status, or whether they are deleted.
- **Task Assignment**: Assign tasks to developers and tester and track their progress.
- **Status Updates**: Update task status and calculate completion contribution houres.
and more things

## Requirements

- PHP 8.0 or higher
- Laravel 10.x
- Composerhttps://github.com/noorhaan-almohammed/Project-task.git
- MySQL or another supported database

## Installation

1. **Clone the repository:**

```
https://github.com/noorhaan-almohammed/Project-task.git
cd laravel-task-manager
```

2. **Install dependencies:**
 ```
 composer install
 ```

3. **Create a .env file:**
```
     cp .env.example .env
```

4. **Generate the application key:**
```
php artisan key:generate
```

5. **Run database migrations and seed the database:**
```
php artisan migrate
php artisan db:seed
```

6. **Start the development server:**
```
php artisan serve
```

The application will be available at http://localhost:8000. or 8001

<h2>API Endpoints</h2>

 **Route Breakdown**
Public Routes:

POST /login: Allows users to log in.

    Admin Routes (Authenticated and Admin Middleware):
POST /project/addProject: Add a new project.
PUT /project/update/{project}: Update a project.
DELETE /project/delete/{project}: Delete a project.
PUT /users/{user}: Update a user.
GET /users: List all users.
GET /users/{id}: Show a specific user.
POST /users: Create a new user.
DELETE /users/{user}: Delete a user.

    Manager Routes (Authenticated and Role: Manager):
POST /project/assignManyUsers/{project}: Assign multiple users to a project.
POST /project/assignUsers/{project}: Assign single user to a project.
PATCH /project/detach/{user}: Detach a user from a project.
PATCH /users/detach/{user}: Detach a user from a project.
GET /user/project/allTasks: List all tasks for the user's projects.
POST /task: Create a new task.
PATCH /task/{taskid}/assign: Assign a task to a user.
PATCH /task/{taskid}/unAssign: Unassign a task from a user.
PUT /task/{task}: Update a task.
DELETE /task/{task}: Delete a task.

    Developer Routes (Authenticated and Role: Developer):
PATCH /task/{taskId}/status: Update the status of a task.

    Tester Routes (Authenticated and Role: Tester):
PATCH /task/{taskId}/tester/status: Update the status of a task (for testing).

    Authenticated User Routes (Auth Middleware):
POST /logout: Log out the current user.
GET /profile: Get the current user's profile.

GET /user/tasks: List tasks assigned to the user.
GET /users/tasks/latest: Get the latest tasks.
GET /users/tasks/oldest: Get the oldest tasks.
GET /users/tasks/important: Get the most important tasks.
GET /project/{project}: Show a project.
GET /project/taskProject: Get tasks for a project with filters.
GET /project/user/taskProject: Get tasks for a user within a project with filters.
GET /task: List all tasks.
GET /task/{task}: Show a specific task.
