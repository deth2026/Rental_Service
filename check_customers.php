<?php
require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Check all customer accounts
$customers = User::where('role', 'customer')->get();

echo "Total customers: " . $customers->count() . "\n\n";

foreach ($customers as $customer) {
    echo "Customer: {$customer->email}\n";
    echo "  Current hash: " . substr($customer->password, 0, 40) . "...\n";
    
    // Try common test passwords
    $testPasswords = ['customer123', 'password123', '12345678', 'password', 'test123'];
    $found = false;
    
    foreach ($testPasswords as $pwd) {
        if (Hash::check($pwd, $customer->password)) {
            echo "  ✓ Password matches: '{$pwd}'\n";
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "  ✗ No common password matched - resetting to 'customer123'\n";
        $customer->password = Hash::make('customer123');
        $customer->save();
    }
    echo "\n";
}
