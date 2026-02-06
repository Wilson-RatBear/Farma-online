<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Juan Perez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'direccion' => 'Av. Principal 123, Centro.',
            'telefono' => '123456789'
        ]);

        $response->assertStatus(201);
    }

    public function test_user_cannot_register_with_special_chars_in_name()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Juan Perez@',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'direccion' => 'Av. Principal 123',
            'telefono' => '123456789'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_user_cannot_register_with_numbers_in_name()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Juan Perez 123',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'direccion' => 'Av. Principal 123',
            'telefono' => '123456789'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_user_cannot_register_with_special_chars_in_address()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Juan Perez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'direccion' => 'Av. Principal #123', // # is not allowed
            'telefono' => '123456789'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['direccion']);
    }

    public function test_user_can_register_with_valid_punctuation_in_address()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Maria Lopez',
            'email' => 'maria@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'direccion' => 'Calle 50, No. 10.', // , and . are allowed
            'telefono' => '123456789'
        ]);

        $response->assertStatus(201);
    }
}
