<?php

namespace App\Console\Commands;

use App\Models\Auction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ActivateAuction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:activate-auction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $auctions = Auction::where('status', 'pending')
            ->where('start_time', '<=', Carbon::now())
            ->get();

        foreach ($auctions as $auction) {
            $auction->update(['status' => 'active']);
            $this->info("Auction #{$auction->id} has been activated.");
        }

        $this->info('Auction activation check completed.');
    }
}
