<?php 
// 🔧 Activar todos los mensajes de error
ini_set('error_reporting', E_ALL);

// 🕒 Establecer la zona horaria
date_default_timezone_set('America/Los_Angeles');

// 🔌 Datos de conexión a la base de datos
$dbhost = 'localhost';
$dbname = 'ecommerceweb';
$dbuser = 'root';
$dbpass = '';

// ✅ Definir constantes solo si no existen (para evitar errores de redefinición)
if (!defined("BASE_URL")) {
    define("BASE_URL", "http://localhost/eCommerceSite-PHP/");
}

if (!defined("ADMIN_URL")) {
    define("ADMIN_URL", BASE_URL . "admin/");
}

// 🌐 Conexión a la base de datos usando PDO
try {
    $pdo = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
}
