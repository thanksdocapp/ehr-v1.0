<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomepageFeaturesController extends Controller
{
    public function index()
    {
        $features = HomepageFeature::ordered()->paginate(20);
        return view('admin.homepage-features.index', compact('features'));
    }

    public function create()
    {
        return view('admin.homepage-features.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'icon' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['is_active'] = $request->has('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['color'] = $data['color'] ?? '#0d6efd';

        HomepageFeature::create($data);

        return redirect()->route('admin.homepage-features.index')
            ->with('success', 'Homepage feature created successfully.');
    }

    public function show(HomepageFeature $homepageFeature)
    {
        return view('admin.homepage-features.show', compact('homepageFeature'));
    }

    public function edit(HomepageFeature $homepageFeature)
    {
        return view('admin.homepage-features.edit', compact('homepageFeature'));
    }

    public function update(Request $request, HomepageFeature $homepageFeature)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'icon' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['is_active'] = $request->has('is_active');
        $data['sort_order'] = $data['sort_order'] ?? $homepageFeature->sort_order;
        $data['color'] = $data['color'] ?? $homepageFeature->color;

        $homepageFeature->update($data);

        return redirect()->route('admin.homepage-features.index')
            ->with('success', 'Homepage feature updated successfully.');
    }

    public function destroy(HomepageFeature $homepageFeature)
    {
        $homepageFeature->delete();

        return redirect()->route('admin.homepage-features.index')
            ->with('success', 'Homepage feature deleted successfully.');
    }

    public function toggleStatus(HomepageFeature $homepageFeature)
    {
        $homepageFeature->update([
            'is_active' => !$homepageFeature->is_active
        ]);

        $status = $homepageFeature->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'success' => true,
            'message' => "Homepage feature {$status} successfully.",
            'is_active' => $homepageFeature->is_active
        ]);
    }
}
