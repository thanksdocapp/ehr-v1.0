<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomepageSectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sections = HomepageSection::ordered()->get();
        return view('admin.homepage-sections.index', compact('sections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.homepage-sections.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'section_name' => 'required|string|max:50|unique:homepage_sections,section_name',
            'title' => 'required|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|url|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        HomepageSection::create([
            'section_name' => $request->section_name,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'description' => $request->description,
            'image' => $request->image,
            'data' => [],
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.homepage-sections.index')
            ->with('success', 'Homepage section created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(HomepageSection $homepageSection)
    {
        return view('admin.homepage-sections.show', compact('homepageSection'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HomepageSection $homepageSection)
    {
        return view('admin.homepage-sections.edit', compact('homepageSection'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HomepageSection $homepageSection)
    {
        $validator = Validator::make($request->all(), [
            'section_name' => 'required|string|max:50|unique:homepage_sections,section_name,' . $homepageSection->id,
            'title' => 'required|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|url|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $homepageSection->update([
            'section_name' => $request->section_name,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'description' => $request->description,
            'image' => $request->image,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.homepage-sections.index')
            ->with('success', 'Homepage section updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HomepageSection $homepageSection)
    {
        $homepageSection->delete();
        
        return redirect()->route('admin.homepage-sections.index')
            ->with('success', 'Homepage section deleted successfully!');
    }
}
