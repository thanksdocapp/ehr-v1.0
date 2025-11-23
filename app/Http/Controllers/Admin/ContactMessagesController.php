<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\HospitalEmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ContactMessagesController extends Controller
{
    /**
     * Display a listing of contact messages.
     */
    public function index(Request $request): View
    {
        $query = ContactMessage::orderBy('created_at', 'desc');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }
        
        $contactMessages = $query->paginate(20);
        
        $stats = [
            'total' => ContactMessage::count(),
            'new' => ContactMessage::where('status', 'new')->count(),
            'replied' => ContactMessage::where('status', 'replied')->count(),
            'closed' => ContactMessage::where('status', 'closed')->count(),
            'today' => ContactMessage::whereDate('created_at', today())->count(),
        ];
        
        return view('admin.contact-messages.index', compact('contactMessages', 'stats'));
    }
    
    /**
     * Display the specified contact message.
     */
    public function show(ContactMessage $contactMessage): View
    {
        // Mark as read when viewing
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }
        
        return view('admin.contact-messages.show', compact('contactMessage'));
    }
    
    /**
     * Reply to a contact message.
     */
    public function reply(Request $request, ContactMessage $contactMessage, HospitalEmailNotificationService $emailService): JsonResponse
    {
        $request->validate([
            'reply_subject' => 'required|string|max:255',
            'reply_message' => 'required|string|max:5000',
        ]);
        
        try {
            // Send reply email
            $emailService->sendContactReply($contactMessage, $request->reply_subject, $request->reply_message);
            
            // Update contact message status
            $contactMessage->update([
                'status' => 'replied',
                'reply_subject' => $request->reply_subject,
                'reply_message' => $request->reply_message,
                'replied_at' => now(),
                'replied_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send contact reply', [
                'contact_message_id' => $contactMessage->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reply. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Update contact message status.
     */
    public function updateStatus(Request $request, ContactMessage $contactMessage): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:new,read,replied,closed'
        ]);
        
        $contactMessage->update([
            'status' => $request->status,
            'updated_by' => auth()->id()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'status' => $contactMessage->status
        ]);
    }
    
    /**
     * Delete a contact message.
     */
    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        try {
            $contactMessage->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Contact message deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contact message.'
            ], 500);
        }
    }
    
    /**
     * Bulk actions on contact messages.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:delete,mark_read,mark_replied,mark_closed',
            'ids' => 'required|array',
            'ids.*' => 'exists:contact_messages,id'
        ]);
        
        try {
            $count = 0;
            
            switch ($request->action) {
                case 'delete':
                    $count = ContactMessage::whereIn('id', $request->ids)->delete();
                    $message = "Deleted {$count} contact message(s) successfully!";
                    break;
                    
                case 'mark_read':
                    $count = ContactMessage::whereIn('id', $request->ids)->update(['status' => 'read']);
                    $message = "Marked {$count} contact message(s) as read!";
                    break;
                    
                case 'mark_replied':
                    $count = ContactMessage::whereIn('id', $request->ids)->update(['status' => 'replied']);
                    $message = "Marked {$count} contact message(s) as replied!";
                    break;
                    
                case 'mark_closed':
                    $count = ContactMessage::whereIn('id', $request->ids)->update(['status' => 'closed']);
                    $message = "Marked {$count} contact message(s) as closed!";
                    break;
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action.'
            ], 500);
        }
    }
}
