<?php
session_start();
include "../Autenticazione/db_connect.php";

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    $message = "";
    if (isset($_GET['test_name'])) {
        $testName = $_GET['test_name'];
        $quesiti = [];

        try {
            $procedureSQL = "CALL VISUALIZZAZIONE_QUESITI(:testName)";
            $stmt = $pdo->prepare($procedureSQL);
            $stmt->bindParam(':testName', $testName);
            $stmt->execute();
            $quesiti = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $message = "Errore: " . $e->getMessage();
        }
    }
?>
    <!DOCTYPE html>
    <html lang="it">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Popolamento Test</title>
        <link rel="stylesheet" type="text/css" href="../Style/style.css">
    </head>
    <header>
        <a id="logo" href="../Autenticazione/home_docente.php"><img src="../Style/logo.png"></a>
        <h1 id="pageTag">Gestione Test</h1>
    </header>

    <body>
        <div id="back">
            <a href="../TestDocente/gestioneTest.php">Torna indietro</a>
        </div>
        <?php if ($testName && isset($quesiti)) : ?>
            <h1>Nome Test: <?php echo htmlspecialchars($testName); ?></h1>
            <h2>Quesiti del Test:</h2>
            <?php if (count($quesiti) > 0) : ?>
                <?php foreach ($quesiti as $quesito) : ?>
                    <div class="quesito">
                        <div class="numerazione">
                            <h3><?php echo htmlspecialchars($quesito['numero_progressivo']); ?></h3>
                        </div>
                        <div class="dati">
                            <h3><?php echo htmlspecialchars($quesito['descrizione']); ?></h3>
                            <p>Difficoltà: <i><?php echo htmlspecialchars($quesito['difficolta']); ?></i></p>

                            <?php
                            $tabelle = [];
                            try {
                                $procedureSQL = "CALL VISUALIZZAZIONE_QUESITO_TABELLA(:numeroQuesito, :titoloTest)";
                                $stmtTabelle = $pdo->prepare($procedureSQL);
                                $stmtTabelle->bindParam(':numeroQuesito', $quesito['numero_progressivo']);
                                $stmtTabelle->bindParam(':titoloTest', $testName);
                                $stmtTabelle->execute();
                                $tabelle = $stmtTabelle->fetchAll(PDO::FETCH_ASSOC);
                                $stmtTabelle->closeCursor();
                            } catch (PDOException $e) {
                                $message = "Errore: " . $e->getMessage();
                                $tabelle = [];
                            }
                            ?>

                            <?php if (count($tabelle) > 0) : ?>
                                <p>Tabelle di riferimento:
                                    <?php foreach ($tabelle as $tabella) : ?>
                                        <i><?php echo htmlspecialchars($tabella['nome_tabella_sql']); ?> </i>
                                    <?php endforeach; ?>
                                </p>
                            <?php else : ?>
                                <p>Nessuna tabella di riferimento.</p>
                            <?php endif; ?>

                            <?php
                            $opzioni = [];
                            $soluzioni = [];

                            if ($quesito['tipo'] === 'Chiuso') {
                                try {
                                    $procedureSQL = "CALL VISUALIZZAZIONE_OPZIONI(:testName, :numeroQuesito)";
                                    $stmt = $pdo->prepare($procedureSQL);
                                    $stmt->bindParam(':testName', $testName);
                                    $stmt->bindParam(':numeroQuesito', $quesito['numero_progressivo']);
                                    $stmt->execute();
                                    $opzioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    $stmt->closeCursor();
                                } catch (PDOException $e) {
                                    $message = "Errore: " . $e->getMessage();
                                    $opzioni = [];
                                }
                            } elseif ($quesito['tipo'] === 'Codice') {
                                try {
                                    $procedureSQL = "CALL VISUALIZZAZIONE_SOLUZIONE_CODICE(:testName, :numeroQuesito)";
                                    $stmt = $pdo->prepare($procedureSQL);
                                    $stmt->bindParam(':testName', $testName);
                                    $stmt->bindParam(':numeroQuesito', $quesito['numero_progressivo']);
                                    $stmt->execute();
                                    $soluzioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    $stmt->closeCursor();
                                } catch (PDOException $e) {
                                    $message = "Errore: " . $e->getMessage();
                                    $soluzioni = [];
                                }
                            }
                            ?>

                            <?php if (isset($opzioni) && count($opzioni) > 0) : ?>
                                <h3>Opzioni:</h3>
                                <ul>
                                    <?php foreach ($opzioni as $opzione) : ?>
                                        <li>
                                            <?php echo htmlspecialchars($opzione['campo_testo']); ?>
                                            <?php if ($opzione['corretta']) : ?>
                                                <strong>(Corretta)</strong>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if (isset($soluzioni) && count($soluzioni) > 0) : ?>
                                <h3>Soluzioni:</h3>
                                <ul>
                                    <?php foreach ($soluzioni as $soluzione) : ?>
                                        <li>
                                            <pre><?php echo htmlspecialchars($soluzione['codice']); ?></pre>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
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