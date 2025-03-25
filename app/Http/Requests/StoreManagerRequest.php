<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagerRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
          $userId = $this->route('user')->id; // Get the current user's ID

    return [
        'name' => ['sometimes', 'string', 'max:255'],
        'email' => [
            'sometimes', 
            'email', 
            'max:255', 
            Rule::unique('users', 'email')->ignore($userId)
        ],
        'password' => ['nullable', 'min:6'],
        'national_id' => [
            'sometimes', 
            'string', 
            Rule::unique('users', 'national_id')->ignore($userId)
        ],
        'avatar_img' => ['nullable', 'image', 'max:5120'], // 5MB max
    ];
    }
    
    public function messages(): array
    {
        // Log::info('messages');
        return [
            'email.unique' => 'This email is already in use.',
            'national_id.unique' => 'This national ID is already in use.',
            'password.min' => 'Password must be at least 6 characters long.',
        ];
    }
}
