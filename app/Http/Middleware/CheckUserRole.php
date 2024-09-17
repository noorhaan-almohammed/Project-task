<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $user = auth()->user();

        $project = $user->projects->where('project_id', $request->project_id)->first();

        if ($project && $project->pivot->role === $role) {
            return $next($request);
        }
        return response()->json(['message' => 'Access denied.'], Response::HTTP_FORBIDDEN);

    }
}
