<?php
namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message->load('user');
    }

    public function broadcastOn()
    {
        return new Channel('auction.' . $this->message->auction_id . '.chat');
    }

    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'message' => $this->message->message,
                'user_name' => $this->message->user->name,
                'created_at' => $this->message->created_at->toISOString(),
                'diffForHumans' => $this->message->created_at->diffForHumans(),

            ]
        ];
    }
}
