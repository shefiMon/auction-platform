<?php
namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use App\Events\NewBid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuctionController extends Controller
{
    public function index()
    {
        $auctions = Auction::with(['creator', 'bids.user'])
            ->where('status', 'active')
            ->orWhere('start_time', '>', now())
            ->orderBy('start_time')
            ->paginate(12);

        return view('auctions.index', compact('auctions'));
    }

    public function show(Auction $auction)
    {
        $auction->load(['creator', 'bids.user', 'chatMessages.user']);

        return view('auctions.show', compact('auction'));
    }

    public function create()
    {

        $this->authorize('create', Auction::class);
        return view('auctions.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Auction::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'starting_price' => 'required|numeric|min:0',
            'reserve_price' => 'nullable|numeric|min:0',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'image' => 'nullable|image|max:2048',
            'stream_url' => 'nullable|url',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('auctions', 'public');
        }

        $validated['created_by'] = Auth::id();
        $validated['current_price'] = $validated['starting_price'];

        Auction::create($validated);

        return redirect()->route('auctions.index')
            ->with('success', 'Auction created successfully!');
    }

    public function placeBid(Request $request, Auction $auction)
    {
        $request->validate([
            'amount' => 'required|numeric|min:' . ($auction->current_price + 1),
        ]);

        if (!$auction->isActive()) {
            return back()->withErrors('This auction is no longer active.');
        }

        if (Auth::user()->balance < $request->amount) {
            return back()->withErrors('Insufficient balance.');
        }

        DB::transaction(function () use ($request, $auction) {
            // Check if auction needs time extension
            $timeRemaining = $auction->timeRemaining();
            if ($auction->auto_extend && $timeRemaining <= 60) { // Last minute
                $auction->update([
                    'end_time' => $auction->end_time->addSeconds($auction->extension_time)
                ]);
            }

            // Create the bid
            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'bid_time' => now(),
            ]);

            // Update auction current price
            $auction->update(['current_price' => $request->amount]);

            // Broadcast the new bid
            broadcast(new NewBid($bid));
        });

        return back()->with('success', 'Bid placed successfully!');
    }
}
