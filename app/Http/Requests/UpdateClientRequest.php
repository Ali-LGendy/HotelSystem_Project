<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
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
        // return [
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email|unique:users,email,' . $this->route('client'),
        //     'avatar_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        //     'country' => 'required|string',
        //     'gender' => 'required|in:Male,Female',
        // ];
    $userId = $this->route('user')->id; 
    return [
        'name' => ['sometimes', 'string', 'max:255'],
        'email' => [
            'sometimes', 
            'email', 
            'max:255', 
            Rule::unique('users', 'email')->ignore($userId)
        ],
        'password' => ['nullable', 'min:6'],
        'mobile' => [
            'sometimes', 
            'string', 
        ],
        'gender' => 'required|in:Male,Female',
        'avatar_img' => ['nullable', 'image', 'max:5120'], // 5MB max
    ];
    }
}
