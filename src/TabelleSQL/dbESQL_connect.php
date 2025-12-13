<?php
$servername = "localhost";
$username = "root";
$password = "root";
$database = "PIATTAFORMA_ESQL";

try {
    $pdoESQL = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $pdoESQL->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connessione al database fallita: " . $e->getMessage();
}