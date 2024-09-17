<?php

namespace App\Http\Requests\TaskRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255|unique:tasks,title',
            'description' => 'sometimes|string',
            'execute_time' => 'sometimes|integer|min:1',
            'priority' => 'sometimes|in:Low,Medium,High',
            'user_id' => 'sometimes|exists:users,id',
        ];
    }
}
