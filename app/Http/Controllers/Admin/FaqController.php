<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Faq::query();
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }
        
        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $faqs = $query->orderBy('sort_order')->orderBy('created_at', 'desc')->paginate(20);
        
        $categories = [
            'general' => 'General Questions',
            'appointments' => 'Appointments',
            'services' => 'Medical Services',
            'emergency' => 'Emergency Care',
            'insurance' => 'Insurance & Billing'
        ];
        
        return view('admin.faqs.index', compact('faqs', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = [
            'general' => 'General Questions',
            'appointments' => 'Appointments',
            'services' => 'Medical Services',
            'emergency' => 'Emergency Care',
            'insurance' => 'Insurance & Billing'
        ];
        
        return view('admin.faqs.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'required|string|in:general,appointments,services,emergency,insurance',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);
        
        try {
            DB::beginTransaction();
            
            $faq = Faq::create([
                'question' => $request->question,
                'answer' => $request->answer,
                'category' => $request->category,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->boolean('is_active', true)
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.faqs.index')
                           ->with('success', 'FAQ created successfully.');
                           
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating FAQ: ' . $e->getMessage());
            
            return back()->withInput()
                        ->with('error', 'Error creating FAQ. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Faq $faq)
    {
        return view('admin.faqs.show', compact('faq'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Faq $faq)
    {
        $categories = [
            'general' => 'General Questions',
            'appointments' => 'Appointments',
            'services' => 'Medical Services',
            'emergency' => 'Emergency Care',
            'insurance' => 'Insurance & Billing'
        ];
        
        return view('admin.faqs.edit', compact('faq', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'required|string|in:general,appointments,services,emergency,insurance',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);
        
        try {
            DB::beginTransaction();
            
            $faq->update([
                'question' => $request->question,
                'answer' => $request->answer,
                'category' => $request->category,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->boolean('is_active', true)
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.faqs.index')
                           ->with('success', 'FAQ updated successfully.');
                           
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating FAQ: ' . $e->getMessage());
            
            return back()->withInput()
                        ->with('error', 'Error updating FAQ. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq)
    {
        try {
            $faq->delete();
            
            return redirect()->route('admin.faqs.index')
                           ->with('success', 'FAQ deleted successfully.');
                           
        } catch (\Exception $e) {
            Log::error('Error deleting FAQ: ' . $e->getMessage());
            
            return back()->with('error', 'Error deleting FAQ. Please try again.');
        }
    }

    /**
     * Toggle FAQ status
     */
    public function toggleStatus(Faq $faq)
    {
        try {
            $faq->update(['is_active' => !$faq->is_active]);
            
            $status = $faq->is_active ? 'activated' : 'deactivated';
            
            return response()->json([
                'success' => true,
                'message' => "FAQ {$status} successfully.",
                'status' => $faq->is_active
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error toggling FAQ status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating FAQ status.'
            ], 500);
        }
    }
}
