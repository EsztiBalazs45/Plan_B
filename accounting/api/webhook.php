<?php
require_once '../vendor/autoload.php';
require_once '../includes/config.php';

\Stripe\Stripe::setApiKey('sk_test_51R5NbyHUv7jEVnHmYVlHmBjKx6mbmqQtxWkqEKOp06JvQdAK4jx0IfGnhZdll4zKA3ee4knG1HWC3DJFmYTioA1D006q3pwsbW');
$endpoint_secret = 'whsec_d71638c96a47948161f9bf73524247cab08a05f485bd94b1cd9a962c83ebcf8f';

function logMessage($message)
{
    file_put_contents('webhook_log.txt', $message . "\n", FILE_APPEND);
}

logMessage("Webhook started - " . date('Y-m-d H:i:s'));

$payload = @file_get_contents('php://input');
if ($payload === false || empty(trim($payload))) {
    logMessage("Error: Empty or invalid payload");
    http_response_code(400);
    exit();
}
logMessage("Payload received: " . substr($payload, 0, 100) . "...");

$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;
if (!$sig_header || empty(trim($sig_header))) {
    logMessage("Error: Missing or invalid signature header");
    http_response_code(400);
    exit();
}
logMessage("Signature header: " . $sig_header);

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
    logMessage("Event constructed: " . $event->type);
} catch (\UnexpectedValueException $e) {
    logMessage("Error: Invalid payload - " . $e->getMessage());
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    logMessage("Error: Signature verification failed - " . $e->getMessage());
    http_response_code(400);
    exit();
}

if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $client_reference_id = $session->client_reference_id;
    logMessage("Client reference ID: " . $client_reference_id);

    $parts = explode('|', $client_reference_id);
    if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
        logMessage("Error: Invalid client_reference_id format: $client_reference_id");
        http_response_code(400);
        exit();
    }
    list($user_id, $service_id) = $parts;
    logMessage("Parsed user_id: $user_id, service_id: $service_id");

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        logMessage("Error: Database connection failed - " . $conn->connect_error);
        http_response_code(500);
        exit();
    }
    logMessage("Database connected");

    // Ellenőrizzük, hogy a service_id létezik-e
    $stmt = $conn->prepare("SELECT service_price FROM services WHERE id = ?");
    if (!$stmt) {
        logMessage("Error: SQL prepare failed (services): " . $conn->error);
        http_response_code(500);
        exit();
    }
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        logMessage("Error: Invalid service_id: $service_id");
        http_response_code(400);
        exit();
    }
    $service = $result->fetch_assoc();
    $price = $service['service_price'];
    logMessage("Service price retrieved: $price");
    $stmt->close();

    // Tranzakcióval biztosítjuk az adatkonzisztenciát
    $conn->begin_transaction();
    try {
        // Előfizetés beszúrása
        $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, service_id, start_date) VALUES (?, ?, NOW())");
        if (!$stmt) {
            throw new Exception("SQL prepare failed (subscriptions): " . $conn->error);
        }
        $stmt->bind_param( "ii",$user_id, $service_id);
        $stmt->execute();
        $subscription_id = $conn->insert_id;
        logMessage("Success: New subscription $subscription_id created");
        $stmt->close();

        // Fizetési adatok beszúrása
        $payment_intent_id = $session->payment_intent ?? 'unknown';
        $amount = $session->amount_total / 100; // Stripe centben számol
        $stmt = $conn->prepare("INSERT INTO payment (user_id, service_id, payment_intent_id, amount, payment_date) VALUES (?, ?, ?, ?, NOW())");
        if (!$stmt) {
            throw new Exception("SQL prepare failed (payment): " . $conn->error);
        }
        $stmt->bind_param( "iisd",$user_id, $service_id, $payment_intent_id, $amount);
        $stmt->execute();
        $payment_id = $conn->insert_id;
        logMessage("Success: New payment $payment_id recorded with amount $amount");
        $stmt->close();

        $conn->commit();
        logMessage("Transaction committed");
    } catch (Exception $e) {
        $conn->rollback();
        logMessage("Error: Transaction failed - " . $e->getMessage());
        http_response_code(500);
        exit();
    }

    $conn->close();
    logMessage("Database connection closed");
}

http_response_code(200);
logMessage("Webhook completed successfully");
exit();