<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

// Storage::fake('public'); // Fakes storage to prevent actual file uploads

// $avatar = UploadedFile::fake()->image('avatar.jpg'); // Creates a fake image


test('new users can register', function () {
    $this->withoutExceptionHandling(); // Show actual errors

    Role::create(['name' => 'client', 'guard_name' => 'web']);

    // Fake an empty file (bypasses actual image generation)
    $avatar = UploadedFile::fake()->create('avatar.jpg', 1, 'image/jpeg');

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'gender'=>'male',
        'avatar_image' => $avatar, // File upload
        'country' => 'USA', // Required field
    ]);
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
