<?php

namespace Modules\LMS\Http\Controllers\Instructor\Courses\Bundle;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LMS\Repositories\Courses\Bundle\BundleRepository;

class BundleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(protected BundleRepository $bundle) {}

    public function index(Request $request)
    {
        //
        $options = [];
        $filterType = '';
        if ($request->has('filter')) {
            $filterType = $request->filter ?? '';
        }
        switch ($filterType) {
            case 'trash':
                $options['onlyTrashed'] = [];
                break;
            case 'all':
                $options['withTrashed'] = [];
                break;
        }
        $response = $this->bundle->bundleGetByUser(options: $options);
        $bundles = $response ?? [];
        $countResponse = $this->bundle->trashCount(options: [
            'where' => ['user_id' => authCheck()->id]
        ]);
        $countData = [
            'total' => 0,
            'published' => 0,
            'trashed' => 0
        ];

        if ($countResponse['status'] === 'success') {
            $response =  $countResponse['data'] ?? [];
            $countData =  !empty($response) ? $response->toArray() : $countData;
        }
        return view('portal::instructor.course.bundle.index', compact('bundles', 'countData'));
    }

    /**
     * create
     */
    public function create(): View
    {
        return view('portal::instructor.course.bundle.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $bundle = $this->bundle->save($request);
        if ($bundle['status'] !== 'success') {
            return response()->json($bundle);
        }
        return $this->jsonSuccess(
            'Bundle has been saved successfully',
            route('instructor.bundle.index')
        );
    }

    /**
     * Show the specified resource.
     */
    public function edit($id, Request $request): View
    {

        $locale = $request->locale ?? app()->getLocale();
        $bundle = $this->bundle->bundleEdit($id, locale: $locale);
        return view('portal::instructor.course.bundle.create', compact('bundle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, Request $request): JsonResponse
    {
        $bundle = $this->bundle->update($id, $request);
        if ($bundle['status'] !== 'success') {
            return response()->json($bundle);
        }
        return $this->jsonSuccess(
            'Bundle has been update successfully',
            route('instructor.bundle.index')
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $response = $this->bundle->delete(id: $id);
        $response['url'] = route('instructor.bundle.index');
        return response()->json($response);
    }


    public function thumbnailDelete($id)
    {
        $response = $this->bundle->thumbnailDelete(id: $id);
        return response()->json($response);
    }

    /**
     * restore the specified bundle from storage.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function restore(int $id)
    {
        $response = $this->bundle->restore(id: $id);
        $response['url'] = route('instructor.bundle.index');
        return response()->json($response);
    }
}
