<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Download extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'user_id',
        'app_file_id',
        'version',
        'ip_address',
        'user_agent',
        'device_id',
        'country',
        'bytes_sent',
        'status_code',
        'completed_at',
    ];

    protected $casts = [
        'bytes_sent'   => 'integer',
        'status_code'  => 'integer',
        'completed_at' => 'datetime',
    ];

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(AppFile::class, 'app_file_id');
    }
}
