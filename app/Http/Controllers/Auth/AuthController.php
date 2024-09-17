<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Services\AuthService;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest\LoginRequest;

class AuthController extends Controller
{
    protected $authService;

    /**
     * Constructor to inject the AuthService.
     *
     * @param \App\Http\Services\AuthService $authService Auth service to handle authentication logic
     */
    public function __construct(AuthService $authService){
        // Inject AuthService
        $this->authService = $authService;
        // Middleware to protect the logout and profile routes, ensuring only authenticated users can access them
        $this->middleware('auth')->only('logout','profile');
    }

    /**
     * Login the user with provided credentials.
     *
     * @param LoginRequest $request A request containing the login credentials
     * @return \Illuminate\Http\JsonResponse Returns the JWT token or an error response if credentials are invalid
     */
    public function login(LoginRequest $request)
    {
        // Extract credentials from the request
        $credentials = $request->only(['email', 'password']);
        // Attempt to log the user in via the AuthService
        $token = $this->authService->attemptLogin($credentials);

        // If login fails, return an error response
        if (!$token) {
            return response()->json(['errors' => 'Invalid email and password'], 401);
        }

        // If login succeeds, return the token response
        return $this->respondToken($token);
    }

    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request A request instance containing the authenticated user
     * @return \Illuminate\Http\JsonResponse Returns the authenticated user's profile data
     */
    public function profile(Request $request)
    {
        // Fetch the authenticated user from the request
        $user = $request->user();

        // Return the user's profile as JSON
        return response()->json(['user' => $user]);
    }

    /**
     * Logout the user by invalidating their token.
     *
     * @return \Illuminate\Http\JsonResponse Returns a success message after logout
     */
    public function logout()
    {
        // Invalidate the user's token via the AuthService
        $this->authService->logout();
        // Return a success message
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Format the response for a successful login attempt.
     *
     * @param string $token The JWT token generated after successful login
     * @return \Illuminate\Http\JsonResponse Returns the JWT token, its type, and expiration time
     */
    public function respondToken($token)
    {
        // Get the token expiration time from the JWT configuration
        $expiresIn = config('jwt.ttl') * 60;
        // Return the token and metadata as a JSON response
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiresIn
        ]);
    }
}
