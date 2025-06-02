<?php
header('Content-Type: application/json');
session_start();

if(!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    exit(json_encode(['error' => 'Не авторизован']));
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM word_cards WHERE user_id = ? ORDER BY next_review ASC";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($cards);