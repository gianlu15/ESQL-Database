<?php
session_start();
include "../Autenticazione/db_connect.php";

if (!isset($_SESSION['email']) || !isset($_SESSION['nome'])) {
    header('Location: ../Autenticazione/login.php');
    exit();
}

try {
    $stmt = $pdo->query("SELECT email_docente, nome, cognome FROM Docente");
    $docenti = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Errore nel recupero dei docenti: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seleziona Docente</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>

<header>
    <a id="logo"><img src="../Style/logo.png"></a>
    <h1 id="pageTag">Seleziona il docente destinatario del messaggio</h1>
    </header>

<body>

    <?php if (isset($error_message)) { echo "<p style='color:red;'>$error_message</p>"; } ?>
    
    <form method="POST" action="creaMessaggio.php">
        <label for="email_docente">Seleziona il docente:</label>
        <select id="email_docente" name="email_docente" required>
            <?php foreach ($docenti as $docente): ?>
                <option value="<?php echo htmlspecialchars($docente['email_docente']); ?>">
                    <?php echo htmlspecialchars($docente['nome'] . ' ' . $docente['cognome']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Prosegui</button>
    </form>
    <div id="back">
        <a href='gestioneMessaggi.php'>Torna indietro</a>
    </div>
</body>
</html>
