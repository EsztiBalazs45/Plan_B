<?php
require_once '../vendor/autoload.php';
require_once '../includes/config.php';

\Stripe\Stripe::setApiKey('asd');
$endpoint_secret = 'asd'; 

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

$log_entry = "Received webhook - " . date('Y-m-d H:i:s') . "\n";
$log_entry .= "Payload: $payload\n";
$log_entry .= "Signature: $sig_header\n";
$log_entry .= "All Headers: " . print_r(getallheaders(), true) . "\n";
file_put_contents('webhook_log.txt', $log_entry, FILE_APPEND);

if (!$sig_header || empty(trim($sig_header))) {
    file_put_contents('webhook_log.txt', "Error: Missing or invalid signature header\n", FILE_APPEND);
    http_response_code(400);
    exit();
}

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch (\UnexpectedValueException $e) {
    file_put_contents('webhook_log.txt', "Error: Invalid payload - " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    file_put_contents('webhook_log.txt', "Error: Signature verification failed - " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(400);
    exit();
}

if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $client_reference_id = $session->client_reference_id;

    if (!$client_reference_id || strpos($client_reference_id, '|') === false) {
        file_put_contents('webhook_log.txt', "Error: Invalid or missing client_reference_id: $client_reference_id\n", FILE_APPEND);
        http_response_code(400);
        exit();
    }

    list($user_id, $service_id) = explode('|', $client_reference_id);
    file_put_contents('webhook_log.txt', "Parsed user_id: $user_id, service_id: $service_id\n", FILE_APPEND);

    $conn = new mysqli("localhost", "root", "", "asd");
    if ($conn->connect_error) {
        file_put_contents('webhook_log.txt', "Error: Database connection failed - " . $conn->connect_error . "\n", FILE_APPEND);
        http_response_code(500);
        exit();
    }

    // Lekérjük a szolgáltatás árát a services táblából
    $stmt = $conn->prepare("SELECT service_price FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        file_put_contents('webhook_log.txt', "Error: Invalid service_id: $service_id\n", FILE_APPEND);
        http_response_code(400);
        exit();
    }
    $service = $result->fetch_assoc();
    $price = $service['service_price'];

    // Beszúrjuk az előfizetést az árral együtt
    $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, service_id, start_date, price) VALUES (?, ?, NOW(), ?)");
    $stmt->bind_param("iid", $user_id, $service_id, $price);
    if ($stmt->execute()) {
        $subscription_id = $conn->insert_id;
        file_put_contents('webhook_log.txt', "Success: New subscription $subscription_id created with price $price\n", FILE_APPEND);
    } else {
        file_put_contents('webhook_log.txt', "Error: Failed to create subscription - " . $stmt->error . "\n", FILE_APPEND);
        http_response_code(500);
        exit();
    }

    $stmt->close();
    $conn->close();
}

http_response_code(200);
exit();