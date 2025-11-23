<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutStat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AboutStatsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $aboutStats = AboutStat::ordered()->get();
        return view('admin.about-stats.index', compact('aboutStats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.about-stats.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'value' => 'required|string|max:50',
            'prefix' => 'nullable|string|max:10',
            'suffix' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        
        AboutStat::create($data);

        return redirect()->route('admin.about-stats.index')
            ->with('success', 'About statistic created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(AboutStat $aboutStat)
    {
        return view('admin.about-stats.show', compact('aboutStat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AboutStat $aboutStat)
    {
        return view('admin.about-stats.edit', compact('aboutStat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AboutStat $aboutStat)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'value' => 'required|string|max:50',
            'prefix' => 'nullable|string|max:10',
            'suffix' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        
        $aboutStat->update($data);

        return redirect()->route('admin.about-stats.index')
            ->with('success', 'About statistic updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AboutStat $aboutStat)
    {
        $aboutStat->delete();
        
        return redirect()->route('admin.about-stats.index')
            ->with('success', 'About statistic deleted successfully!');
    }

    /**
     * Toggle the status of a statistic
     */
    public function toggleStatus(AboutStat $aboutStat): JsonResponse
    {
        $aboutStat->update([
            'is_active' => !$aboutStat->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'is_active' => $aboutStat->is_active
        ]);
    }
}
