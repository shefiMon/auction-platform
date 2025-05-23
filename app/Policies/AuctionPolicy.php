<?php
namespace App\Policies;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuctionPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, Auction $auction)
    {
        return $user->isAdmin() || $user->id === $auction->created_by;
    }

    public function delete(User $user, Auction $auction)
    {
        return $user->isAdmin();
    }
}
