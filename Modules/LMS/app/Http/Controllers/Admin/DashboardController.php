<?php

namespace Modules\LMS\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Models\General\ThemeSetting;
use Modules\LMS\Repositories\DashboardRepository;
use Modules\Roles\Repositories\Staff\StaffRepository;
use Modules\LMS\Repositories\SearchSuggestionRepository;

class DashboardController extends Controller
{
    public function __construct(
        protected SearchSuggestionRepository $suggestion,
        protected DashboardRepository $dashboard
    ) {}

    /**
     * Display a listing of the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(): \Illuminate\View\View
    {
        $data = $this->dashboard->dashboardInfo();
        return view('portal::admin.dashboard', compact('data'));
    }

    /**
     * Log out the current user and redirect to the login form.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(): \Illuminate\Http\RedirectResponse
    {
        Auth::logout();
        Session::flush();
        return redirect()->route('login');
    }

    /**
     * Handle search suggestions based on user input.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchingSuggestion(Request $request): \Illuminate\Http\JsonResponse
    {
        $results = $this->suggestion->searchSuggestion($request);
        return response()->json($results);
    }

    public function assetsPath(Request $request)
    {
        return assets($request->path);
    }

    public function cacheClear()
    {

        Artisan::call('optimize:clear');
        toastr()->success(translate('Cache Clear Successfully'));
        return redirect()->back();
    }

    public function cacheOptimize()
    {
        Artisan::call('optimize');
        toastr()->success(translate('Cache Optimize Successfully'));
        return redirect()->back();
    }
    public function storageLink()
    {
        Artisan::call('storage:link');
        toastr()->success(translate('storage link Successfully'));
        return redirect()->back();
    }
    public function licenseRemoveForm()
    {
        return view('portal::admin.license.remove');
    }
    public function licenseRemove(Request $request)
    {
    }

    public function profile()
    {
        return view('portal::admin.profile.form');
    }

    public function profileUpdate(Request $request)
    {
        if (!has_permissions($request->user(), ['update.staff'])) {
            return json_error('You have no permission.');
        }
        $response = StaffRepository::update($request->id, $request);

        if ($response['status'] == 'success') {
            $admin = $response['data'];
            if ($request->filled('password') && $request->password !== $admin->password) {
                Auth::logout();
                Session::flush();
            }
        }
        return $response['status'] !== 'success'
            ? response()->json($response)
            : $this->jsonSuccess('Profile Update successfully.', route('admin.profile'));
    }
}
