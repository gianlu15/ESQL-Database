<?php
session_start();
include "../Autenticazione/db_connect.php";
include "../TabelleSQL/dbESQL_connect.php";

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    $emailStudente = $_SESSION['email'];
    $titoloTest = isset($_GET['titolo_test']) ? $_GET['titolo_test'] : null;
    $quesiti = [];
    $message = "";
    $visualizzaRisposte = false;  

    if ($titoloTest) {
        try {
            $sqlVisualizza = "SELECT visualizza_risposte FROM Test WHERE titolo = :titoloTest";
            $stmtVisualizza = $pdo->prepare($sqlVisualizza);
            $stmtVisualizza->bindParam(':titoloTest', $titoloTest, PDO::PARAM_STR);
            $stmtVisualizza->execute();
            $visualizzaRisposte = $stmtVisualizza->fetchColumn();  // Ottieni il valore booleano
            $stmtVisualizza->closeCursor();

            $procedureSQL = "CALL VISUALIZZAZIONE_QUESITI(:titoloTest)";
            $stmt = $pdo->prepare($procedureSQL);
            $stmt->bindParam(':titoloTest', $titoloTest);
            $stmt->execute();
            $quesiti = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $message = "Errore: " . $e->getMessage();
        }
    } else {
        $message = "Test non trovato.";
    }
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettagli Test Completato</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>
<header>
    <a id="logo" href="../Autenticazione/home_studente.php"><img src="../Style/logo.png"></a>
    <h1 id="pageTag">Dettagli Test Completato</h1>
</header>

<body>
    <div id="back">
        <a href="visualizzaEsiti.php">Torna indietro</a>
    </div>
    <?php if ($message) : ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php else : ?>
        <h2>Quesiti del Test: <?php echo htmlspecialchars($titoloTest); ?></h2>
        <?php if (count($quesiti) > 0) : ?>
            <?php foreach ($quesiti as $quesito) : ?>
                <div class="quesito">
                    <h3>Quesito <?php echo htmlspecialchars($quesito['numero_progressivo']); ?>:</h3>
                    <p><?php echo htmlspecialchars($quesito['descrizione']); ?></p>
                    <p>Difficoltà: <i><?php echo htmlspecialchars($quesito['difficolta']); ?></i></p>

                    <?php if ($quesito['tipo'] === 'Chiuso') : ?>
                        <ul>
                            <?php
                            $opzioni = [];
                            try {
                                $procedureSQL = "CALL VISUALIZZAZIONE_OPZIONI(:testName, :numeroQuesito)";
                                $stmt = $pdo->prepare($procedureSQL);
                                $stmt->bindParam(':testName', $titoloTest);
                                $stmt->bindParam(':numeroQuesito', $quesito['numero_progressivo']);
                                $stmt->execute();
                                $opzioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $stmt->closeCursor();
                            } catch (PDOException $e) {
                                $message = "Errore: " . $e->getMessage();
                                $opzioni = [];
                            }
                            ?>
                            <?php foreach ($opzioni as $opzione) : ?>
                                <li>
                                    <?php echo htmlspecialchars($opzione['campo_testo']); ?>
                                    <?php if ($visualizzaRisposte && $opzione['corretta']) : ?>
                                        <strong>(Corretta)</strong>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <p>Hai scelto:
                            <?php
                            try {
                                $stmtRisposta = $pdo->prepare("CALL VISUALIZZA_OPZIONE_SCELTA(:emailStudente, :titoloTest, :numeroQuesito, @campo_testo)");
                                $stmtRisposta->bindParam(':emailStudente', $emailStudente, PDO::PARAM_STR);
                                $stmtRisposta->bindParam(':titoloTest', $titoloTest, PDO::PARAM_STR);
                                $stmtRisposta->bindParam(':numeroQuesito', $quesito['numero_progressivo'], PDO::PARAM_INT);
                                $stmtRisposta->execute();

                                $stmt = $pdo->query("SELECT @campo_testo AS campo_testo");
                                $rispostaScelta = $stmt->fetchColumn();
                                $stmt->closeCursor();

                                echo htmlspecialchars($rispostaScelta ?? '');
                            } catch (PDOException $e) {
                                echo "Errore nel recupero dell'opzione scelta.";
                            }
                            ?>
                        </p>
                    <?php elseif ($quesito['tipo'] === 'Codice') : ?>
                        <?php
                        try {
                            $stmtRispostaStudente = $pdo->prepare("CALL VISUALIZZA_TESTO_INSERITO(:emailStudente, :titoloTest, :numeroQuesito, @testoSQL)");
                            $stmtRispostaStudente->bindParam(':emailStudente', $emailStudente, PDO::PARAM_STR);
                            $stmtRispostaStudente->bindParam(':titoloTest', $titoloTest, PDO::PARAM_STR);
                            $stmtRispostaStudente->bindParam(':numeroQuesito', $quesito['numero_progressivo'], PDO::PARAM_INT);
                            $stmtRispostaStudente->execute();

                            $stmt = $pdo->query("SELECT @testoSQL AS testoSQL");
                            $testoSQLStudente = $stmt->fetchColumn();
                            $stmt->closeCursor();

                            echo "<p>Hai inserito:</p>";
                            echo "<pre>" . htmlspecialchars($testoSQLStudente ?? '') . "</pre>";

                            if ($visualizzaRisposte) {
                                $stmtSoluzioneCodice = $pdo->prepare("CALL VISUALIZZAZIONE_SOLUZIONE_CODICE(:titoloTest, :numeroQuesito)");
                                $stmtSoluzioneCodice->bindParam(':titoloTest', $titoloTest, PDO::PARAM_STR);
                                $stmtSoluzioneCodice->bindParam(':numeroQuesito', $quesito['numero_progressivo'], PDO::PARAM_INT);
                                $stmtSoluzioneCodice->execute();
                                $soluzioni = $stmtSoluzioneCodice->fetchAll(PDO::FETCH_ASSOC);
                                $stmtSoluzioneCodice->closeCursor();

                                echo "<p>Soluzioni corrette:</p>";
                                foreach ($soluzioni as $soluzione) {
                                    echo "<pre>" . htmlspecialchars($soluzione['codice']) . "</pre>";
                                }
                            } else {
                                echo "<p>Le soluzioni non sono visibili per questo test.</p>";
                            }
                        } catch (PDOException $e) {
                            echo "Errore nel recupero dei dati: " . htmlspecialchars($e->getMessage());
                        }
                        ?>

                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>Nessun quesito trovato per questo test.</p>
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
