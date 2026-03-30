<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db   = 'vehicle_rental';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Updating payments...\n";
    
    $bookingStmt = $pdo->query('SELECT id FROM bookings ORDER BY id');
    $bookings = $bookingStmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($bookings) . " bookings: " . implode(', ', $bookings) . "\n";
    
    $paymentStmt = $pdo->query('SELECT id FROM payments ORDER BY id');
    $payments = $paymentStmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($payments) . " payments\n";
    
    $updateStmt = $pdo->prepare('UPDATE payments SET booking_id = :booking_id WHERE id = :id');
    
    foreach ($payments as $i => $paymentId) {
        if (isset($bookings[$i])) {
            $updateStmt->execute([':booking_id' => $bookings[$i], ':id' => $paymentId]);
            echo "Payment $paymentId -> booking_id $bookings[$i]\n";
        }
    }
    
    echo "Done!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
