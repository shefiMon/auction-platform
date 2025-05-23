<?php

namespace App\Events;

use App\Models\Bid;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewBid implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bid;

    public function __construct(Bid $bid)
    {
        $this->bid = $bid->load('user', 'auction');
    }

    public function broadcastOn()
    {
        return new Channel('auction.' . $this->bid->auction_id);
    }

    public function broadcastWith()
    {
        return [
            'bid' => [
                'id' => $this->bid->id,
                'amount' => $this->bid->amount,
                'user_name' => $this->bid->user->name,
                'bid_time' => $this->bid->bid_time->toISOString(),
            ],
            'auction' => [
                'id' => $this->bid->auction->id,
                'current_price' => $this->bid->auction->current_price,
                'end_time' => $this->bid->auction->end_time->toISOString(),
            ]
        ];
    }
}
