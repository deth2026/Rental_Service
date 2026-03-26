<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$bookings = DB::table('bookings')->orderBy('id')->pluck('id')->toArray();
echo 'Bookings: ' . implode(', ', $bookings) . PHP_EOL;

$payments = DB::table('payments')->orderBy('id')->get();
foreach ($payments as $p) {
    echo 'Payment ' . $p->id . ' has booking_id ' . $p->booking_id . PHP_EOL;
}
