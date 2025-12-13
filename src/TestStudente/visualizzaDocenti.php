<?php
session_start();
include "../Autenticazione/db_connect.php";

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    try {
        $sql = "SELECT email_docente, nome, cognome, nome_dipartimento, nome_corso FROM docente";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $docenti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Errore: " . $e->getMessage();
        $docenti = [];
    }
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizza Docenti</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>

<header>
    <a id="logo" href="../Autenticazione/home_studente.php"><img src="../Style/logo.png"></a>
    <h1 id="pageTag">Seleziona Docente</h1>
</header>

<body>

    <?php if (count($docenti) > 0) : ?>
        <?php foreach ($docenti as $docente) : ?>
            <div class="docente">
                <a href="visualizzaTestDocente.php?email_docente=<?php echo urlencode($docente['email_docente']); ?>" class="dati">
                    <h3><?php echo htmlspecialchars($docente['nome']); ?> <?php echo htmlspecialchars($docente['cognome']); ?></h3>
                    <p>Dipartimento: <i><?php echo htmlspecialchars($docente['nome_dipartimento']); ?></i></p>
                    <p>Email: <i><?php echo htmlspecialchars($docente['email_docente']); ?></i></p>
                    <p>Nome corso: <i><?php echo htmlspecialchars($docente['nome_corso']); ?></i></p>
                </a>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Nessun docente trovato.</p>
    <?php endif; ?>
    <div id="back">
        <a href="../Autenticazione/home_studente.php">Torna indietro</a>
    </div>
</body>

</html>

<?php
} else {
    header('Location: ../index.php');
    exit();
}
?>
