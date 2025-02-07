<?php

namespace Modules\LMS\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LMS\Repositories\CurrencyRepository;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(protected CurrencyRepository $currency) {}

    public function index(): JsonResponse
    {
        $currencies = $this->currency->get();
        return response()->json($currencies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $currency = $this->currency->save($request->all());

        return response()->json($currency);
    }

    /**
     * Show the specified resource.
     */
    public function show($id): JsonResponse
    {
        $currency = $this->currency->first($id);

        return response()->json($currency);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, Request $request): JsonResponse
    {
        $currency = $this->currency->update($id, $request->all());
        return response()->json($currency);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $currency = $this->currency->delete(id: $id);
        return response()->json($currency);
    }
}
