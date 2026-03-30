<?php
require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$users = User::all();
echo "Total users: " . $users->count() . "\n";

foreach ($users as $user) {
    echo "ID: {$user->id}, Email: {$user->email}, Role: {$user->role}\n";
}
