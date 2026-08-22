<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Illuminate\Contracts\Validation\ValidationRule;
use Zolta\Http\Request\BaseRequest;

class CreateUserRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => ['required', 'min:8'],
            'terms' => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'The username field is required.',
            'email.unique' => 'The email already exists.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.custom_password_rule' => 'The password must contain at least one uppercase letter and one symbol.',
            'password.confirmed' => 'The password confirmation does not match.',
            'terms.required' => 'You must accept the terms and conditions to proceed.',
            'terms.accepted' => 'You must accept the terms and conditions to proceed.',
        ];
    }
}
