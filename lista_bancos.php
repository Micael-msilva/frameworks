<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servidor = $_POST['servidor'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    try {
        $pdo = new PDO("mysql:host=$servidor", $usuario, $senha);
        $stmt = $pdo->query("SHOW DATABASES");
        $bancos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        header('Content-Type: application/json');
        echo json_encode($bancos);
    } catch (Exception $e) {
        echo json_encode([]);
    }
}
?>