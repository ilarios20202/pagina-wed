<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = 'root';
$DB_NAME = 'zapateria';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {

    die("Error de conexión: " . $e->getMessage());
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function s($v) {

    if (is_null($v)) return '';
    if (is_bool($v)) return $v ? '1' : '0';

    if (is_array($v) || is_object($v)) {
        $v = print_r($v, true);
    } else {
        $v = (string)$v;
    }

    $v = trim($v);

    if (!defined('ENT_QUOTES')) {
        return htmlspecialchars($v);
    }
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}