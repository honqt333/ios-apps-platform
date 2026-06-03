<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Screenshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'path',
        'disk',
        'url',
        'device_type',
        'width',
        'height',
        'sort_order',
    ];

    protected $casts = [
        'width'      => 'integer',
        'height'     => 'integer',
        'sort_order' => 'integer',
    ];

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function getUrlAttribute(): ?string
    {
        if ($this->attributes['url']) {
            return $this->attributes['url'];
        }

        if ($this->path) {
            return \Storage::disk($this->disk)->url($this->path);
        }

        return null;
    }
}
