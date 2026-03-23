<?php
require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Shop;

$shops = Shop::where('province', 'LIKE', '%Kampong Thom%')
    ->orWhereHas('city', function($q) { $q->where('name', 'LIKE', '%Kampong Thom%'); })
    ->get(['id', 'name', 'province', 'city_id', 'status', 'address']);

echo "FOUND SHOPS:\n";
foreach ($shops as $shop) {
    echo "ID: {$shop->id}, Name: {$shop->name}, Province Col: {$shop->province}, City ID: {$shop->city_id}, Status: {$shop->status}, Address: {$shop->address}\n";
}
if ($shops->isEmpty()) {
    echo "No shops found for Kampong Thom.\n";
}
