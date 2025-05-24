<?php

namespace App\Console\Commands;

use App\Models\Auction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAsCompleted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-as-completed';

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
        $auctions = Auction::where('status', 'active')
            ->where('end_time', '<=', Carbon::now())
            ->get();

        foreach ($auctions as $auction) {
            $auction->status = 'completed';
            $auction->save();
        }

        $this->info('Successfully marked ' . $auctions->count() . ' auctions as completed.');
    }
}
