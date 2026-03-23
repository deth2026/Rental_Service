<?php
require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Shop;
use App\Models\City;

$shops = Shop::with('city')->get();

echo "ALL SHOPS IN DB:\n";
foreach ($shops as $shop) {
    $cityName = $shop->city ? $shop->city->name : 'NULL';
    echo "ID: {$shop->id}, Name: {$shop->name}, City: {$cityName} (ID: {$shop->city_id}), Status: {$shop->status}\n";
}

$cities = City::where('name', 'LIKE', '%Kampong Thom%')->get();
echo "\nKAMPONG THOM CITIES IN DB:\n";
foreach ($cities as $city) {
    echo "ID: {$city->id}, Name: {$city->name}\n";
}
