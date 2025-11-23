<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IrsRefund;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IrsRefundController extends Controller
{
    /**
     * Display a listing of the IRS refunds.
     */
    public function index(Request $request)
    {
        // Calculate statistics
        $totalRefunds = IrsRefund::count();
        $pendingRefunds = IrsRefund::pending()->count();
        $underReviewRefunds = IrsRefund::underReview()->count();
        $processingRefunds = IrsRefund::processing()->count();
        $approvedRefunds = IrsRefund::approved()->count();
        $rejectedRefunds = IrsRefund::rejected()->count();
        $receivedRefunds = IrsRefund::where('status', IrsRefund::STATUS_RECEIVED)->count();
        
        $totalAmount = IrsRefund::where('status', '!=', IrsRefund::STATUS_REJECTED)
                              ->sum('refund_amount');
        
        $approvalRate = $totalRefunds > 0 
            ? round((($approvedRefunds + $receivedRefunds) / $totalRefunds) * 100, 1) 
            : 0;
            
        // Monthly growth calculation
        $thisMonth = IrsRefund::whereMonth('created_at', now()->month)
                             ->whereYear('created_at', now()->year)
                             ->count();
        $lastMonth = IrsRefund::whereMonth('created_at', now()->subMonth()->month)
                             ->whereYear('created_at', now()->subMonth()->year)
                             ->count();
        $monthlyGrowth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;
        
        $stats = [
            'total_refunds' => $totalRefunds,
            'pending_refunds' => $pendingRefunds + $underReviewRefunds,
            'processing_refunds' => $processingRefunds,
            'approved_refunds' => $approvedRefunds + $receivedRefunds,
            'rejected_refunds' => $rejectedRefunds,
            'total_amount' => $totalAmount,
            'approval_rate' => $approvalRate,
            'monthly_growth' => round($monthlyGrowth, 1)
        ];
        
        // Build query with filters
        $query = IrsRefund::with(['user', 'account']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('tax_year')) {
            $query->forYear($request->tax_year);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('irs_reference', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Get refunds with pagination
        $refunds = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get available tax years for filter dropdown
        $taxYears = IrsRefund::distinct('tax_year')
                            ->orderBy('tax_year', 'desc')
                            ->pluck('tax_year')
                            ->filter();
        
        return view('admin.irs-refunds.index', compact('stats', 'refunds', 'taxYears'));
    }

    /**
     * Show the form for creating a new IRS refund.
     */
    public function create()
    {
        $users = User::where('status', 'active')->orderBy('name')->get();
        return view('admin.irs-refunds.create', compact('users'));
    }

    /**
     * Store a newly created IRS refund in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'tax_year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'filing_status' => 'required|in:single,married_joint,married_separate,head_of_household,qualifying_widow',
            'adjusted_gross_income' => 'required|numeric|min:0',
            'total_tax' => 'required|numeric|min:0',
            'total_payments' => 'required|numeric|min:0',
            'refund_amount' => 'required|numeric|min:0',
        ]);

        $refund = IrsRefund::create([
            'user_id' => $request->user_id,
            'reference_number' => 'IRS-' . strtoupper(uniqid()),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'tax_year' => $request->tax_year,
            'filing_status' => $request->filing_status,
            'adjusted_gross_income' => $request->adjusted_gross_income,
            'total_tax' => $request->total_tax,
            'total_payments' => $request->total_payments,
            'refund_amount' => $request->refund_amount,
            'status' => IrsRefund::STATUS_PENDING,
        ]);

        return redirect()->route('admin.irs-refunds.index')
                        ->with('success', 'IRS refund application created successfully.');
    }

    /**
     * Display the specified IRS refund.
     */
    public function show(IrsRefund $refund)
    {
        $refund->load(['user', 'account']);
        return view('admin.irs-refunds.show', compact('refund'));
    }

    /**
     * Show the form for editing the specified IRS refund.
     */
    public function edit(IrsRefund $refund)
    {
        $users = User::where('status', 'active')->orderBy('name')->get();
        return view('admin.irs-refunds.edit', compact('refund', 'users'));
    }

    /**
     * Update the specified IRS refund in storage.
     */
    public function update(Request $request, IrsRefund $refund)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'tax_year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'filing_status' => 'required|in:single,married_joint,married_separate,head_of_household,qualifying_widow',
            'adjusted_gross_income' => 'required|numeric|min:0',
            'total_tax' => 'required|numeric|min:0',
            'total_payments' => 'required|numeric|min:0',
            'refund_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,under_review,processing,approved,rejected,received,cancelled',
            'notes' => 'nullable|string',
        ]);

        $refund->update($request->only([
            'first_name', 'last_name', 'tax_year', 'filing_status',
            'adjusted_gross_income', 'total_tax', 'total_payments',
            'refund_amount', 'status', 'notes'
        ]));

        return redirect()->route('admin.irs-refunds.index')
                        ->with('success', 'IRS refund updated successfully.');
    }

    /**
     * Remove the specified IRS refund from storage.
     */
    public function destroy(IrsRefund $refund)
    {
        $refund->delete();

        return response()->json([
            'success' => true, 
            'message' => 'IRS refund deleted successfully.'
        ]);
    }

    /**
     * Update the status of the IRS refund.
     */
    public function updateStatus(Request $request, IrsRefund $refund)
    {
        $request->validate([
            'status' => 'required|in:pending,under_review,processing,approved,rejected,received,cancelled',
            'notes' => 'nullable|string',
        ]);

        $updateData = ['status' => $request->status];
        
        // Set timestamps based on status
        switch ($request->status) {
            case IrsRefund::STATUS_UNDER_REVIEW:
                $updateData['review_started_at'] = now();
                break;
            case IrsRefund::STATUS_PROCESSING:
                $updateData['processing_started_at'] = now();
                break;
            case IrsRefund::STATUS_APPROVED:
            case IrsRefund::STATUS_REJECTED:
            case IrsRefund::STATUS_RECEIVED:
                $updateData['completed_at'] = now();
                break;
        }

        if ($request->filled('notes')) {
            $updateData['notes'] = $request->notes;
        }

        $refund->update($updateData);

        return response()->json([
            'success' => true, 
            'message' => 'IRS refund status updated successfully.'
        ]);
    }

    /**
     * Export IRS refunds data.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        // This would implement actual export functionality
        return response()->json([
            'success' => true, 
            'message' => "Export functionality for {$format} format will be implemented."
        ]);
    }

    /**
     * Process bulk actions on selected IRS refunds.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'refund_ids' => 'required|array',
            'refund_ids.*' => 'exists:irs_refunds,id',
        ]);

        $action = $request->action;
        $refundIds = $request->refund_ids;
        
        switch ($action) {
            case 'approve':
                IrsRefund::whereIn('id', $refundIds)
                        ->update([
                            'status' => IrsRefund::STATUS_APPROVED,
                            'completed_at' => now()
                        ]);
                $message = 'Selected IRS refunds have been approved.';
                break;
                
            case 'reject':
                IrsRefund::whereIn('id', $refundIds)
                        ->update([
                            'status' => IrsRefund::STATUS_REJECTED,
                            'completed_at' => now()
                        ]);
                $message = 'Selected IRS refunds have been rejected.';
                break;
                
            case 'delete':
                IrsRefund::whereIn('id', $refundIds)->delete();
                $message = 'Selected IRS refunds have been deleted.';
                break;
        }

        return response()->json([
            'success' => true, 
            'message' => $message
        ]);
    }
}
