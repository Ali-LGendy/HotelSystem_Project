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
         $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => $userId ? 'sometimes|nullable|min:6' : 'required|min:6',
            'national_id' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'national_id')->ignore($userId),
            ],
            'avatar_img' => 'nullable|image|max:8048',
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
