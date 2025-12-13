<?php
// Credenziali di accesso al database
$servername = "localhost";
$username = "root";
$password = "root";
$database = "PROGETTO_BASI";

try {
    // Connessione al database MySQL usando PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Gestione degli errori in caso di connessione fallita
    echo "Connessione al database fallita: " . $e->getMessage();
}