<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auction extends Model
{
    use HasFactory;

     protected $fillable = [
        'title', 'description', 'image', 'starting_price', 'current_price',
        'reserve_price', 'start_time', 'end_time', 'status', 'created_by',
        'winner_id', 'auto_extend', 'extension_time', 'stream_url'
    ];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'reserve_price' => 'decimal:2',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'auto_extend' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class)->orderByDesc('amount');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
               now()->between($this->start_time, $this->end_time);
    }

    public function timeRemaining(): int
    {
        if (!$this->isActive()) return 0;
        return max(0, $this->end_time->diffInSeconds(now()));
    }

    public function getHighestBid(): ?Bid
    {
        return $this->bids()->first();
    }
}
