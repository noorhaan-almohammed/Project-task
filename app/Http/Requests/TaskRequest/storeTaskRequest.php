<?php
namespace App\Http\Requests\TaskRequest;

use Illuminate\Foundation\Http\FormRequest;

class storeTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255|unique:tasks,title',
            'description' => 'required|string',
            'execute_time' => 'required|integer|min:1',
            'priority' => 'required|in:Low,Medium,High',
        ];
    }
}

