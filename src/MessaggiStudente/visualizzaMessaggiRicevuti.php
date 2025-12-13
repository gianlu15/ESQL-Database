<?php
session_start();
include "../Autenticazione/db_connect.php";

if (!isset($_SESSION['email']) || !isset($_SESSION['nome'])) {
    header('Location: ../Autenticazione/login.php');
    exit();
}

try {
    $stmt = $pdo->prepare("CALL VISUALIZZA_MESSAGGI_RICEVUTI_STUDENTE(:email_studente)");
    $stmt->bindParam(':email_studente', $_SESSION['email'], PDO::PARAM_STR);
    $stmt->execute();
    $messaggi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Errore nel recupero dei messaggi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messaggi Ricevuti</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>
<header>
    <a id="logo"><img src="../Style/logo.png"></a>
    <h1 id="pageTag">Messaggi Ricevuti</h1>
    </header>
<body> 
    <?php if (isset($error_message)) { echo "<p style='color:red;'>$error_message</p>"; } ?>
    <?php if (count($messaggi) > 0): ?>
        <?php foreach ($messaggi as $messaggio): ?>
            <div class="messaggio">
                <h3><?php echo htmlspecialchars($messaggio['titolo']); ?></h3>
                <p><strong>Da:</strong> <?php echo htmlspecialchars($messaggio['email_docente']); ?></p>
                <p><strong>Data:</strong> <?php echo htmlspecialchars($messaggio['data_messaggio']); ?></p>
                <p><strong>Test:</strong> <?php echo htmlspecialchars($messaggio['titolo_test']); ?></p>
                <p><?php echo htmlspecialchars($messaggio['testo']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nessun messaggio ricevuto.</p>
    <?php endif; ?>
    <div id="back">
        <a href='gestioneMessaggi.php'>Torna indietro</a>
    </div>
</body>
</html>
