<?php

namespace Modules\LMS\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\LMS\Repositories\Auth\UserRepository;

class LoginController extends Controller
{
    public function __construct(protected UserRepository $user) {}

    /**
     * Display a listing of the resource.
     */
    public function showForm()
    {
        // Check if the user is authenticated
        if (Auth::check() || auth::guard('admin')->check()) {
            // Retrieve the user's guard type
            $guard = authCheck()->guard ?? null;
            // Determine the redirect route based on the guard type
            $redirectRoute = match ($guard) {
                'instructor' => 'instructor.dashboard', // Instructor dashboard
                'student' => 'student.dashboard',       // Student dashboard
                'organization' => 'organization.dashboard', // Organization dashboard
                default => 'admin.dashboard',                  // Default case (no route)
            };
            // Redirect to the matched route if available
            if ($redirectRoute) {
                return redirect()->route($redirectRoute);
            }
        }
        return view('theme::login.login');
    }

    /**
     * creating a new resource.
     */
    public function login(Request $request)
    {

        return $this->user->login($request);
    }
}
