<?php
require_once '../vendor/autoload.php';
require_once '../includes/config.php';

\Stripe\Stripe::setApiKey('sk_test_51R5NbyHUv7jEVnHmYVlHmBjKx6mbmqQtxWkqEKOp06JvQdAK4jx0IfGnhZdll4zKA3ee4knG1HWC3DJFmYTioA1D006q3pwsbW');
$endpoint_secret = 'whsec_abcdefghijklmnopqrstuvwxyz123456';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch (\Exception $e) {
    http_response_code(400);
    exit();
}

if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $subscription_id = $session->client_reference_id;

    $conn = new mysqli("localhost", "root", "", "asd");
    $stmt = $conn->prepare("UPDATE subscriptions SET status = 'active' WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("i", $subscription_id);
    $stmt->execute();
    $conn->close();
}

http_response_code(200);
?>