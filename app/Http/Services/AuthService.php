<?php
namespace App\Http\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService {

    /**
     * Register a new user with the given data.
     *
     * @param array $data The user data including 'name', 'email', and 'password'.
     * @return User The created user instance.
     */
    public function register(array $data){
        $data['password'] = bcrypt($data['password']);
        return User::create($data);
    }

    /**
     * Attempt to log in with the given credentials.
     *
     * @param array $credentials The login credentials including 'email' and 'password'.
     * @return string|false The authentication token if successful, false otherwise.
     */
    public function attemptLogin(array $credentials)
    {
        if (!$token = Auth::attempt($credentials)) {
            return false;
        }
        return $token;
    }

    /**
     * Log out the currently authenticated user.
     *
     * @return void
     */
    public function logout()
    {
        return Auth::logout();
    }
}
