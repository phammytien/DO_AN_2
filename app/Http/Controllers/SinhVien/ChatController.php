<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\DeTai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewChatMessageMail;

class ChatController extends Controller
{
    /**
     * Get conversation list for student
     */
    public function index()
    {
        $user = Auth::user();
        $maSV = $user->MaSo;

        $conversations = Conversation::forStudent($maSV)
            ->with(['giangVien', 'deTai', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        $conversations = $conversations->map(function ($conv) {
            return [
                'id' => $conv->id,
                'lecturer_name' => $conv->giangVien->TenGV ?? 'Giảng viên',
                'lecturer_avatar' => $conv->giangVien->HinhAnh ?? null,
                'project_name' => $conv->deTai->TenDeTai ?? null,
                'last_message' => $conv->lastMessage->message ?? '',
                'last_message_time' => $conv->last_message_at ? $conv->last_message_at->diffForHumans() : '',
                'unread_count' => $conv->unreadCountForStudent,
            ];
        });

        return response()->json($conversations);
    }

    /**
     * Get messages for a conversation
     */
    public function show($conversationId)
    {
        $user = Auth::user();
        $maSV = $user->MaSo;

        $conversation = Conversation::forStudent($maSV)->findOrFail($conversationId);

        $messages = Message::forConversation($conversationId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($maSV) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'file_name' => $msg->file_name,
                    'file_url' => $msg->file_url,
                    'sender_name' => $msg->sender_name,
                    'is_mine' => $msg->sender_type === 'SinhVien' && $msg->sender_id === $maSV,
                    'created_at' => $msg->created_at->format('H:i'),
                    'is_read' => $msg->is_read,
                ];
            });

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'lecturer_name' => $conversation->giangVien->TenGV ?? 'Giảng viên',
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send a new message
     */
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'nullable|exists:chat_conversations,id',
            'message' => 'required_without:file|string|max:5000',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $user = Auth::user();
        $maSV = $user->MaSo;

        // Get or create conversation
        if ($request->conversation_id) {
            $conversation = Conversation::forStudent($maSV)->findOrFail($request->conversation_id);
        } else {
            // Get student's project and lecturer
            $deTai = DeTai::whereHas('sinhViens', function ($q) use ($maSV) {
                $q->where('MaSV', $maSV);
            })->first();

            if (!$deTai || !$deTai->MaGV) {
                return response()->json(['error' => 'Bạn chưa có đề tài hoặc giảng viên hướng dẫn'], 400);
            }

            $conversation = Conversation::findOrCreate($maSV, $deTai->MaGV, $deTai->MaDeTai);
        }

        // Handle file upload
        $filePath = null;
        $fileName = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('chat_files', $fileName, 'public');
            $filePath = 'storage/' . $filePath;
        }

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'SinhVien',
            'sender_id' => $maSV,
            'message' => $request->message,
            'file_path' => $filePath,
            'file_name' => $fileName,
        ]);

        // Update conversation last message time
        $conversation->update(['last_message_at' => now()]);

        // Send email notification to lecturer
        try {
            $lecturer = $conversation->giangVien;
            if ($lecturer && $lecturer->Email) {
                Mail::to($lecturer->Email)->send(new NewChatMessageMail($message, $conversation));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send chat email: ' . $e->getMessage());
        }

        // Broadcast event (will implement with Pusher later)
        // event(new NewChatMessage($message, $conversation));

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'message' => $message->message,
                'file_name' => $message->file_name,
                'file_url' => $message->file_url,
                'sender_name' => $message->sender_name,
                'is_mine' => true,
                'created_at' => $message->created_at->format('H:i'),
            ],
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead($conversationId)
    {
        $user = Auth::user();
        $maSV = $user->MaSo;

        $conversation = Conversation::forStudent($maSV)->findOrFail($conversationId);

        // Mark all messages from lecturer as read
        Message::forConversation($conversationId)
            ->where('sender_type', 'GiangVien')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $maSV = $user->MaSo;

        $count = Message::whereHas('conversation', function ($q) use ($maSV) {
            $q->where('MaSV', $maSV);
        })
        ->where('sender_type', 'GiangVien')
        ->where('is_read', false)
        ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Delete a message
     */
    public function deleteMessage($id)
    {
        $user = Auth::user();
        $maSV = $user->MaSo;

        $message = Message::findOrFail($id);

        // Check permission: only sender can delete
        if ($message->sender_type !== 'SinhVien' || $message->sender_id !== $maSV) {
            return response()->json(['error' => 'Bạn không có quyền xóa tin nhắn này'], 403);
        }

        // Delete file if exists
        if ($message->file_path && file_exists(public_path($message->file_path))) {
            unlink(public_path($message->file_path));
        }

        $message->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa tin nhắn']);
    }

    /**
     * Delete a conversation
     */
    public function deleteConversation($id)
    {
        $user = Auth::user();
        $maSV = $user->MaSo;

        $conversation = Conversation::forStudent($maSV)->findOrFail($id);

        // Delete all messages first
        Message::where('conversation_id', $id)->delete();

        // Delete conversation
        $conversation->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa cuộc trò chuyện']);
    }
}
