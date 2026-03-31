<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Vehicle;
use App\Models\Shop;

echo "=== Database Connection Check ===\n";
echo "Database: " . config('database.connections.mysql.database') . "\n\n";

echo "=== Vehicles in Database ===\n";
$vehicles = Vehicle::all();
foreach ($vehicles as $v) {
    echo "ID: {$v->id}, Name: {$v->name}, Type: {$v->type}\n";
    echo "  image_url: " . ($v->image_url ?? 'null') . "\n";
    echo "  photos: " . ($v->photos ?? 'null') . "\n";
    echo "\n";
}

echo "=== Shops in Database ===\n";
$shops = Shop::all();
foreach ($shops as $s) {
    echo "ID: {$s->id}, Name: {$s->name}\n";
    echo "  img_url: " . ($s->img_url ?? 'null') . "\n";
    echo "\n";
}