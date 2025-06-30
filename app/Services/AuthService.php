<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Register a new user
     *
     * @param array $data Validated data containing name, email, password, and profile_image
     * @return array
     * @throws \Exception
     */
    public function register(array $data): array
    {
        try {
            // Handle profile image upload
            $imagePath = null;
            if (isset($data['profile_image'])) {
                $image = $data['profile_image'];
                $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('profile_images', $imageName, 'public');
            }

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'profile_image' => $imagePath,
            ]);

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_image' => $imagePath ? Storage::url($imagePath) : null,
                ],
                'token' => $token,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login a user
     *
     * @param array $credentials Validated data containing email and password
     * @return array
     * @throws \Exception
     */
    public function login(array $credentials): array
    {
        try {
            if (!Auth::attempt($credentials)) {
                throw new \Exception('Invalid credentials', 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_image' => $user->profile_image ? Storage::url($user->profile_image) : null,
                ],
                'token' => $token,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Login failed: ' . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
}