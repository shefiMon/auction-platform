<?php
namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\ChatMessage;
use App\Events\NewChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function store(Request $request, Auction $auction)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $message = ChatMessage::create([
            'auction_id' => $auction->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        broadcast(new NewChatMessage($message));

        return response()->json(['success' => true]);
    }
}
