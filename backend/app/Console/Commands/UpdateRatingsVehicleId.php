<?php

namespace App\Console\Commands;

use App\Models\Rating;
use App\Models\Booking;
use Illuminate\Console\Command;

class UpdateRatingsVehicleId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ratings:update-vehicle-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing ratings with vehicle_id from their associated bookings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating ratings with vehicle_id from bookings...');

        $ratings = Rating::whereNull('vehicle_id')
            ->whereNotNull('booking_id')
            ->get();

        $count = 0;
        foreach ($ratings as $rating) {
            $booking = Booking::find($rating->booking_id);
            if ($booking && $booking->vehicle_id) {
                $rating->vehicle_id = $booking->vehicle_id;
                $rating->save();
                $count++;
            }
        }

        $this->info("Updated {$count} ratings with vehicle_id.");
        
        return Command::SUCCESS;
    }
}
