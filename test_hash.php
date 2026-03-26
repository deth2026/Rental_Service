<?php
require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$password = 'password123';
$hashed = Hash::make($password);

echo "Plain: $password\n";
echo "Hashed: $hashed\n";

$user = new User();
$user->password = $hashed;
echo "User password (after setting hashed): " . $user->password . "\n";

if (Hash::check($password, $user->password)) {
    echo "Hash::check(plain, user->password) IS TRUE (expected if no double hashing)\n";
} else {
    echo "Hash::check(plain, user->password) IS FALSE (unexpected if no double hashing)\n";
}

if (Hash::check($hashed, $user->password)) {
    echo "Double hashing detected! (because user->password matches Hash::make(hashed))\n";
} else {
    echo "No double hashing detected.\n";
}

$user2 = new User();
$user2->password = $password;
echo "User password (after setting plain): " . $user2->password . "\n";
if (Hash::check($password, $user2->password)) {
    echo "Hash::check(plain, user2->password) IS TRUE (expected for plain set)\n";
}
