<?php

namespace App\Events;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewAuction implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auction;

    public function __construct(Auction $auction)
    {
        $this->auction = $auction;
    }

    public function broadcastOn()
    {
        return new Channel('auction.created');
    }

    public function broadcastWith()
    {
        return $this->auction->toArray();
    }
}
