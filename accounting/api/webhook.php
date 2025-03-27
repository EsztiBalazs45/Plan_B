<?php
require_once '../vendor/autoload.php';
require_once '../includes/config.php';

\Stripe\Stripe::setApiKey('valami');
$endpoint_secret = 'whsec_abcdefghijklmnopqrstuvwxyz123456';

// A nyers POST adat lekérése
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

if (!$sig_header || empty(trim($sig_header))) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid Stripe-Signature header']);
    exit();
}

// Most már biztos, hogy $sig_header nem null, próbáljuk meg az eseményt létrehozni
try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch (\UnexpectedValueException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit();
}

// Esemény feldolgozása
if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $subscription_id = $session->client_reference_id;

    $conn = new mysqli("localhost", "root", "", "asd");
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE subscriptions SET status = 'active' WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("i", $subscription_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

http_response_code(200);
echo json_encode(['status' => 'success']);
?>