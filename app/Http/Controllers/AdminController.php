<?php
namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        $stats = [
            'total_auctions' => Auction::count(),
            'active_auctions' => Auction::where('status', 'active')->count(),
            'total_users' => User::where('role', 'bidder')->count(),
            'completed_auctions' => Auction::where('status', 'completed')->count(),
        ];

        $recentAuctions = Auction::with('creator')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentAuctions'));
    }

    public function auctions()
    {
        $auctions = Auction::with(['creator', 'winner'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('auctions.index', compact('auctions'));
    }

    public function users()
    {
        $users = User::where('role', 'bidder')
            ->withCount('bids')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.users', compact('users'));
    }
}
