<?php
require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Let's try resetting the customer's password to a known value
$email = 'customer@gmail.com';
$newPassword = 'customer123';

$user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

if (!$user) {
    echo "User not found!\n";
    exit(1);
}

// Reset the password
$user->password = Hash::make($newPassword);
$user->save();

echo "Password has been reset for {$user->email}\n";
echo "New hash: {$user->password}\n";

// Verify it works now
$verify = Hash::check($newPassword, $user->password);
echo "Verification: " . ($verify ? 'SUCCESS' : 'FAILED') . "\n";
