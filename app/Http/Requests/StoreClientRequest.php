<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
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
        return [
            'name' => ['sometimes', 'string', 'max:255'],
        'email' => [
            'sometimes', 
            'email', 
            'max:255', 
            Rule::unique('users', 'email')
        ],
        'password' => ['nullable', 'min:6'],
        'mobile' => [
            'sometimes', 
            'string', 
            Rule::unique('users', 'mobile')
        ],
        'avatar_img' => ['nullable', 'image', 'max:5120'],
        'gender' => 'required|in:Male,Female',
 // 5MB max
            // 'mobile' => 'required|string|max:1',
        ];
    }
}
