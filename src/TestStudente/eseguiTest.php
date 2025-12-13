<?php
session_start();
include "../Autenticazione/db_connect.php";
include "../TabelleSQL/dbESQL_connect.php";

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    $message = "";
    if (isset($_GET['test_name'])) {
        $testName = $_GET['test_name'];
        $quesiti = [];
        $statoTest = null;

        try {
            $sqlStato = "SELECT stato FROM Studente_Svolge_Test WHERE email_studente = :emailStudente AND titolo_test = :titoloTest";
            $stmtStato = $pdo->prepare($sqlStato);
            $stmtStato->bindParam(':emailStudente', $_SESSION['email']);
            $stmtStato->bindParam(':titoloTest', $testName);
            $stmtStato->execute();
            $statoTest = $stmtStato->fetchColumn();

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

    $numeroQuesitoEsito = isset($_GET['numero_quesito']) ? $_GET['numero_quesito'] : null;
    $esitoRisposta = isset($_GET['esito']) ? $_GET['esito'] : null;

    $sqlContent = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'sql_') === 0) {
                $sqlContent[$key] = $value;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esegui Test</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
    <link rel="stylesheet" type="text/css" href="../Style/styleEditor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js"></script>
    <style>
        #result {
            margin-top: 20px;
        }

        #sqlForm {
            width: 90%;
        }

        .quesito, .quesitoChiuso, .docente{
            display: flex;
            flex-direction: column;
            text-align: center;
        }

        .tabelle_quesito, table{
            margin-left: auto;
      margin-right: auto;
      border-collapse: collapse;
        }
    </style>
</head>
<header>
    <a id="logo" href="../Autenticazione/home_studente.php"><img src="../Style/logo.png"></a>
    <h1 id="pageTag">Esegui Test</h1>
</header>

<body>
    <div id="back">
        <a href="../TestStudente/visualizzaDocenti.php">Torna indietro</a>
    </div>
    <?php if ($testName && isset($quesiti)) : ?>
        <h1>Test: <?php echo htmlspecialchars($testName); ?></h1>
        <?php if ($statoTest === 'Concluso') : ?>
            <h2>Il test è stato concluso</h2>
        <?php else : ?>
            <h2>Quesiti:</h2>
            <?php if (count($quesiti) > 0) : ?>
                <?php foreach ($quesiti as $quesito) : ?>
                    <div class="quesito">
                        <div class="numerazione">
                            <h3>Quesito <?php echo htmlspecialchars($quesito['numero_progressivo']); ?></h3>
                        </div>
                        <div class="informazioni">
                            <h4><?php echo htmlspecialchars($quesito['descrizione']); ?></h4>
                            <p>Difficoltà: <i><?php echo htmlspecialchars($quesito['difficolta']); ?></i></p>
                        </div>

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
                            <div class="tabella">
                                <h4>Tabelle di riferimento:</h4>
                                <?php foreach ($tabelle as $tabella) : ?>
                                    <div class="tabelle_riferimento">
                                        <h5><?php echo htmlspecialchars($tabella['nome_tabella_sql']); ?></h5>
                                        <?php
                                        try {
                                            $stmt = $pdoESQL->prepare("SELECT * FROM " . $tabella['nome_tabella_sql']);
                                            $stmt->execute();
                                            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            $columnsStmt = $pdoESQL->prepare("DESCRIBE " . $tabella['nome_tabella_sql']);
                                            $columnsStmt->execute();
                                            $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);

                                            if (!empty($rows)) {
                                                echo '<table class="tabelle_quesito">';
                                                echo '<tr>';
                                                foreach ($columns as $column) {
                                                    echo '<th>' . htmlspecialchars($column) . '</th>';
                                                }
                                                echo '</tr>';
                                                foreach ($rows as $row) {
                                                    echo '<tr>';
                                                    foreach ($row as $value) {
                                                        echo '<td>' . htmlspecialchars($value) . '</td>';
                                                    }
                                                    echo '</tr>';
                                                }
                                                echo '</table>';
                                            } else {
                                                echo 'La tabella è vuota.';
                                            }
                                        } catch (PDOException $e) {
                                            echo "Errore: " . $e->getMessage();
                                        }
                                        ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <p>Nessuna tabella di riferimento.</p>
                            <?php endif; ?>
                            </div>

                            <?php if ($quesito['tipo'] === 'Chiuso') : ?>
                                <?php
                                $opzioni = [];
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
                                ?>

                                <div class="opzioni">
                                    <h4>Opzioni:</h4>
                                    <?php if (isset($opzioni) && count($opzioni) > 0) : ?>
                                        <form method="post" action="confermaRispostaChiusa.php">
                                            <?php foreach ($opzioni as $opzione) : ?>
                                                <div>
                                                    <input type="radio" id="opzione_<?php echo $opzione['numerazione']; ?>" name="opzione" value="<?php echo $opzione['numerazione']; ?>">
                                                    <label for="opzione_<?php echo $opzione['numerazione']; ?>"><?php echo htmlspecialchars($opzione['campo_testo']); ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                            <input type="hidden" name="numero_quesito" value="<?php echo $quesito['numero_progressivo']; ?>">
                                            <input type="hidden" name="titolo_test" value="<?php echo htmlspecialchars($testName); ?>">

                                            <button type="submit" name="conferma">Conferma</button>
                                        </form>

                                        <?php if ($numeroQuesitoEsito == $quesito['numero_progressivo']): ?>
                                            <?php if ($esitoRisposta === 'successo'): ?>
                                                <p style="color: green;">Risposta salvata o aggiornata con successo.</p>
                                            <?php elseif ($esitoRisposta === 'errore'): ?>
                                                <p style="color: red;">Errore durante il salvataggio della risposta.</p>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                    <?php else : ?>
                                        <p>Nessuna opzione disponibile per questo quesito.</p>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($quesito['tipo'] === 'Codice') : ?>
                                <div id="editor">
                                    <h4>Inserisci la tua risposta SQL:</h4>
                                    <form id="sqlForm" method="post" action="">
                                        <textarea id="sqlEditor_<?php echo $quesito['numero_progressivo']; ?>" name="sql_<?php echo $quesito['numero_progressivo']; ?>"><?php echo isset($sqlContent['sql_' . $quesito['numero_progressivo']]) ? htmlspecialchars($sqlContent['sql_' . $quesito['numero_progressivo']]) : ''; ?></textarea>

                                        <input type="hidden" name="numero_quesito" value="<?php echo $quesito['numero_progressivo']; ?>">
                                        <input type="hidden" name="titolo_test" value="<?php echo htmlspecialchars($testName); ?>">

                                        <button type="submit" name="esegui" value="<?php echo $quesito['numero_progressivo']; ?>">Esegui</button>
                                        <button type="submit" name="conferma" value="<?php echo $quesito['numero_progressivo']; ?>" formaction="confermaRispostaCodice.php">Conferma</button>
                                    </form>

                                    <div id="result_<?php echo $quesito['numero_progressivo']; ?>">
                                        <?php
                                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['esegui']) && $_POST['esegui'] == $quesito['numero_progressivo']) {
                                            $sqlContent = $_POST['sql_' . $quesito['numero_progressivo']];
                                            try {
                                                $stmt = $pdoESQL->prepare($sqlContent);
                                                $stmt->execute();

                                                if (preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\s/i', $sqlContent)) {
                                                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                    if (count($result) > 0) {
                                                        echo '<table border="1">';
                                                        echo '<tr>';
                                                        foreach ($result[0] as $key => $value) {
                                                            echo '<th>' . htmlspecialchars($key) . '</th>';
                                                        }
                                                        echo '</tr>';
                                                        foreach ($result as $row) {
                                                            echo '<tr>';
                                                            foreach ($row as $value) {
                                                                echo '<td>' . htmlspecialchars($value) . '</td>';
                                                            }
                                                            echo '</tr>';
                                                        }
                                                        echo '</table>';
                                                    } else {
                                                        echo 'Nessun risultato trovato.';
                                                    }
                                                }
                                            } catch (PDOException $e) {
                                                echo "Errore: " . $e->getMessage();
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Nessun quesito trovato per questo test.</p>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</body>

<script>
    document.querySelectorAll('textarea[id^="sqlEditor_"]').forEach(function(element) {
        CodeMirror.fromTextArea(element, {
            mode: "text/x-mysql",
            lineNumbers: true
        });
    });

    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener("submit", function(e) {
            var textarea = form.querySelector('textarea');
            if (textarea.value.trim() === "") {
                alert("Inserire il codice nell'editor");
                e.preventDefault();
            }
        });
    });
</script>

</html>
<?php
} else {
    header('Location: ../index.php');
    exit();
}
?>
