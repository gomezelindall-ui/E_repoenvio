<?php
// Configuración de conexión MySQL para AlwaysData
$dbHost = 'mysql-elindall.alwaysdata.net';
$dbName = 'elindall_envios';
$dbUser = 'elindall';
$dbPass = 'jhosep2020';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // La tabla se crea automáticamente si todavía no existe.
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS envios (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            destinatario VARCHAR(150) NOT NULL,
            direccion VARCHAR(255) NOT NULL,
            descripcion TEXT NOT NULL,
            fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_destinatario (destinatario),
            INDEX idx_fecha_creacion (fecha_creacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    http_response_code(500);
    die("No fue posible conectar con la base de datos. Verifica las credenciales y que MySQL de AlwaysData esté disponible.");
}
