<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = 'chat_conversations';
    
    protected $fillable = [
        'MaSV',
        'MaGV',
        'MaDeTai',
        'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Relationship: Sinh viên
     */
    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }

    /**
     * Relationship: Giảng viên
     */
    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    /**
     * Relationship: Đề tài
     */
    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    /**
     * Relationship: Messages
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    /**
     * Get last message
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')->latest();
    }

    /**
     * Scope: For student
     */
    public function scopeForStudent($query, $maSV)
    {
        return $query->where('MaSV', $maSV);
    }

    /**
     * Scope: For lecturer
     */
    public function scopeForLecturer($query, $maGV)
    {
        return $query->where('MaGV', $maGV);
    }

    /**
     * Get unread count for student
     */
    public function getUnreadCountForStudentAttribute()
    {
        return $this->messages()
            ->where('sender_type', 'GiangVien')
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get unread count for lecturer
     */
    public function getUnreadCountForLecturerAttribute()
    {
        return $this->messages()
            ->where('sender_type', 'SinhVien')
            ->where('is_read', false)
            ->count();
    }

    /**
     * Find or create conversation
     */
    public static function findOrCreate($maSV, $maGV, $maDeTai = null)
    {
        return static::firstOrCreate(
            ['MaSV' => $maSV, 'MaGV' => $maGV],
            ['MaDeTai' => $maDeTai]
        );
    }
}
