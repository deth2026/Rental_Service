<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db   = 'vehicle_rental';
$user = 'root';
$pass = '';
$output = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $output .= "Connected to database\n";
    
    // Delete all payments first
    $pdo->exec('DELETE FROM payments');
    $output .= "Deleted all payments\n";
    
    // Get bookings  
    $stmt = $pdo->query('SELECT id, user_id, vehicle_id, shop_id, total_price FROM bookings LIMIT 7');
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $output .= "Found " . count($bookings) . " bookings\n";
    
    // Create a payment for each booking
    $insert = $pdo->prepare('INSERT INTO payments (booking_id, transaction_id, amount, payment_method, payment_status, paid_at, created_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
    
    foreach ($bookings as $booking) {
        $txnId = 'TXN' . strtoupper(bin2hex(random_bytes(6)));
        $insert->execute([
            $booking['id'],
            $txnId,
            $booking['total_price'],
            'qr',
            'paid'
        ]);
        $output .= "Created payment for booking {$booking['id']}\n";
    }
    
    $count = $pdo->query('SELECT COUNT(*) FROM payments')->fetchColumn();
    $output .= "Total payments after: $count\n";
    
} catch (PDOException $e) {
    $output .= "Error: " . $e->getMessage() . "\n";
}

file_put_contents('debug_output.txt', $output);
echo $output;
