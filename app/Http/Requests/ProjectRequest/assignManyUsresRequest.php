<?php

namespace App\Http\Requests\ProjectRequest;

use Illuminate\Foundation\Http\FormRequest;

class assignManyUsresRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(){
        $users = $this->input('users', []);
        foreach ($users as &$user) {
            if (isset($user['role'])) {
                $user['role'] = ucfirst(strtolower($user['role']));
            }
        }
        $this->merge(['users' => $users]);
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'users.*.user_id' => 'required|integer',
            'users.*.role' => 'required|string'
        ];
    }


    public function attributes()
    {
        return [
            'users.*.user_id' => 'User ID',
            'users.*.role' => 'User Role',
        ];
    }

}
