<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NoticesController extends Controller
{
    /**
     * Display a listing of notices.
     */
    public function index()
    {
        $notices = Notice::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.notices.index', compact('notices'));
    }

    /**
     * Show the form for creating a new notice.
     */
    public function create()
    {
        $roles = User::getRoles();
        return view('admin.notices.create', compact('roles'));
    }

    /**
     * Store a newly created notice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,danger',
            'priority' => 'required|in:low,medium,high,urgent',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:doctor,nurse,receptionist,staff,admin',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');
        
        // If "target all" is checked or no roles selected, set target_roles to null
        if ($request->has('target_all') || empty($validated['target_roles'])) {
            $validated['target_roles'] = null;
        }

        Notice::create($validated);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice created successfully.');
    }

    /**
     * Display the specified notice.
     */
    public function show(Notice $notice)
    {
        $notice->load('creator');
        return view('admin.notices.show', compact('notice'));
    }

    /**
     * Show the form for editing the specified notice.
     */
    public function edit(Notice $notice)
    {
        $roles = User::getRoles();
        return view('admin.notices.edit', compact('notice', 'roles'));
    }

    /**
     * Update the specified notice.
     */
    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,danger',
            'priority' => 'required|in:low,medium,high,urgent',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:doctor,nurse,receptionist,staff,admin',
        ]);

        $validated['is_active'] = $request->has('is_active');
        
        // If "target all" is checked or no roles selected, set target_roles to null
        if ($request->has('target_all') || empty($validated['target_roles'])) {
            $validated['target_roles'] = null;
        }

        $notice->update($validated);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    /**
     * Remove the specified notice.
     */
    public function destroy(Notice $notice)
    {
        $notice->delete();

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice deleted successfully.');
    }

    /**
     * Toggle notice active status
     */
    public function toggleStatus(Notice $notice)
    {
        $notice->update(['is_active' => !$notice->is_active]);

        return redirect()->back()
            ->with('success', 'Notice status updated successfully.');
    }
}
