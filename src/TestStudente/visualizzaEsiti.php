<?php
session_start();
include "../Autenticazione/db_connect.php";

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    $emailStudente = $_SESSION['email'];
    $testCompletati = [];
    $message = "";

    try {
        $procedureSQL = "CALL VISUALIZZA_ESITI(:emailStudente)";
        $stmt = $pdo->prepare($procedureSQL);
        $stmt->bindParam(':emailStudente', $emailStudente, PDO::PARAM_STR);
        $stmt->execute();
        $testCompletati = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $e) {
        $message = "Errore: " . $e->getMessage();
    }
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Completati</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>
<header>
    <a id="logo" href="../Autenticazione/home_studente.php"><img src="../Style/logo.png"></a>
    <h1 id="pageTag">Test Completati</h1>
</header>

<body>
    <div id="back">
        <a href="../Autenticazione/home_studente.php">Torna alla Home</a>
    </div>
    <?php if ($message) : ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php else : ?>
        <h2>Lista dei Test Completati</h2>
        <?php if (count($testCompletati) > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Titolo Test</th>
                        <th>Punteggio</th>
                        <th>Totale Quesiti</th>
                        <th>Visualizza Test</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($testCompletati as $test) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($test['titolo_test']); ?></td>
                            <td><?php echo htmlspecialchars($test['punteggio']); ?></td>
                            <td><?php echo htmlspecialchars($test['totale']); ?></td>
                            <td><a href="visualizzaTestCompletato.php?titolo_test=<?php echo urlencode($test['titolo_test']); ?>"> Visualizza > </a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>Non hai completato nessun test.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>

</html>
<?php
} else {
    header('Location: ../index.php');
    exit();
}
?>
