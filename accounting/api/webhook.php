<?php
require_once '../vendor/autoload.php';
require_once '../includes/config.php';

\Stripe\Stripe::setApiKey('asd');
$endpoint_secret = 'asd';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

file_put_contents('webhook_log.txt', "Received payload: $payload\nSignature: $sig_header\n", FILE_APPEND);

if (!$sig_header || empty(trim($sig_header))) {
    file_put_contents('webhook_log.txt', "Error: Missing or invalid Stripe-Signature header\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid Stripe-Signature header']);
    exit();
}

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
    file_put_contents('webhook_log.txt', "Event constructed: " . $event->type . "\n", FILE_APPEND);
} catch (\UnexpectedValueException $e) {
    file_put_contents('webhook_log.txt', "Invalid payload: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    file_put_contents('webhook_log.txt', "Invalid signature: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit();
}

if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $subscription_id = $session->client_reference_id;

    file_put_contents('webhook_log.txt', "Processing subscription ID: $subscription_id\n", FILE_APPEND);

    if (!$subscription_id) {
        file_put_contents('webhook_log.txt', "Error: No client_reference_id provided\n", FILE_APPEND);
        http_response_code(400);
        echo json_encode(['error' => 'No client_reference_id provided']);
        exit();
    }

    $conn = new mysqli("localhost", "root", "", "asd");
    if ($conn->connect_error) {
        file_put_contents('webhook_log.txt', "DB connection failed: " . $conn->connect_error . "\n", FILE_APPEND);
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE subscriptions SET status = 'active' WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("i", $subscription_id);
    $success = $stmt->execute();

    if ($stmt->affected_rows === 0) {
        file_put_contents('webhook_log.txt', "No rows updated - Check if ID $subscription_id exists with status 'pending'\n", FILE_APPEND);
    }

    $stmt->close();
    $conn->close();
}

http_response_code(200);
echo json_encode(['status' => 'success']);
?>