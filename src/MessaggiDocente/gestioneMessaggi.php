<?php
session_start();
include "../Autenticazione/db_connect.php";

if (!isset($_SESSION['email']) || !isset($_SESSION['nome'])) {
    header('Location: ../Autenticazione/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Messaggi</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>
<body>
    <header>
        <a id="logo" href="../Autenticazione/home_docente.php"><img src="../Style/logo.png" alt="Logo"></a>
        <h1 id="pageTag">Gestione Messaggi</h1>
    </header>
    <div class="container">
       
        <button class="section-button" onclick="location.href='creaMessaggio.php'">Crea un nuovo messaggio</button>
        <button class="section-button" onclick="location.href='visualizzaMessaggiRicevuti.php'">Visualizza messaggi ricevuti</button>
        <button class="section-button" onclick="location.href='visualizzaMessaggiInviati.php'">Visualizza messaggi inviati</button>
    </div>
    
    <a href="../Autenticazione/home_docente.php">
        <button>Indietro</button>
    </a>
    
</body>
</html>
