<?php
session_start();
include "../Autenticazione/db_connect.php";
include "../TabelleSQL/dbESQL_connect.php";
// require '../MongoDB/vendor/autoload.php'; // Assicurati che il path sia corretto per l'autoload di Composer

// use MongoDB\Client;

$sqlContent = '';

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    $titolo_test = '';
    $numero_quesito = '';

    if (isset($_GET['titolo_test']) && isset($_GET['numero_quesito'])) {
        $titolo_test = htmlspecialchars($_GET['titolo_test']);
        $numero_quesito = htmlspecialchars($_GET['numero_quesito']);
    } else {
        echo "Parametri mancanti.";
        exit();
    }

    $tabelle = [];
    try {
        $procedureSQL = "CALL VISUALIZZAZIONE_QUESITO_TABELLA(:numeroQuesito, :titoloTest)";
        $stmtTabelle = $pdo->prepare($procedureSQL);
        $stmtTabelle->bindParam(':numeroQuesito', $numero_quesito);
        $stmtTabelle->bindParam(':titoloTest', $titolo_test);
        $stmtTabelle->execute();
        $tabelle = $stmtTabelle->fetchAll(PDO::FETCH_ASSOC);
        $stmtTabelle->closeCursor();
    } catch (PDOException $e) {
        $message = "Errore: " . $e->getMessage();
        $tabelle = [];
    }

    $response = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sqlContent = $_POST['sql'];
        if (isset($_POST['esegui'])) {

            try {
                $sql = $_POST['sql'];
                $stmt = $pdoESQL->prepare($sql);
                $stmt->execute();

                if (preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\s/i', $sql)) {
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($result) > 0) {
                        $response .= '<table border="1">';
                        $response .= '<tr>';
                        foreach ($result[0] as $key => $value) {
                            $response .= '<th>' . htmlspecialchars($key) . '</th>';
                        }
                        $response .= '</tr>';
                        foreach ($result as $row) {
                            $response .= '<tr>';
                            foreach ($row as $value) {
                                $response .= '<td>' . htmlspecialchars($value) . '</td>';
                            }
                            $response .= '</tr>';
                        }
                        $response .= '</table>';
                    } else {
                        $response .= 'Nessun risultato trovato.';
                    }
                }
            } catch (PDOException $e) {
                $response .= "Errore: " . $e->getMessage();
            }
        } elseif (isset($_POST['salva'])) {

            try {
                $procedureSQL = "CALL INSERISCI_SOLUZIONE_CODICE(:numeroQuesito, :titoloTest, :codice)";
                $stmtSave = $pdo->prepare($procedureSQL);
                $stmtSave->bindParam(':numeroQuesito', $numero_quesito);
                $stmtSave->bindParam(':titoloTest', $titolo_test);
                $stmtSave->bindParam(':codice', $sqlContent);
                $stmtSave->execute();
                $message = "Soluzione salvata con successo.";

                // Connessione a MongoDB e inserimento dell'evento
                /* Connessione a MongoDB
                $client = new Client("mongodb://localhost:27017");
                $collection = $client->piattaforma_ESQL->new_solution;

                // Preparazione dei dati dell'evento
                $event = [
                    'event' => 'new_solution_creation',
                    'test_name' => $titolo_test,
                    'question_number' => $numero_quesito,
                    'sql_code' => $sqlContent,
                    'timestamp' => new MongoDB\BSON\UTCDateTime() // Data e ora corrente
                ];

                // Inserimento dell'evento nella collezione di MongoDB
                $collection->insertOne($event);
                */

            } catch (PDOException $e) {
                $message = "Errore: " . $e->getMessage();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creazione Soluzione</title>
    <link rel="stylesheet" type="text/css" href="../Style/styleEditor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js"></script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }

        #left-panel,
        #right-panel {
            width: 45%;
            margin: 10px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
        }

        #right-panel {
            display: flex;
            flex-direction: column;
        }

        #result {
            margin-top: 20px;
        }

        #sqlForm{
            width: 90%;
        }
    </style>
</head>

<body>
    <div id="left-panel">
        <h2>Tabelle del Quesito</h2>
        <?php
        if (!empty($tabelle)) {
            foreach ($tabelle as $tabella) {
                echo "<h3>" . htmlspecialchars($tabella['nome_tabella_sql']) . "</h3>";

                try {
                    $stmt = $pdoESQL->prepare("SELECT * FROM " . $tabella['nome_tabella_sql']);
                    $stmt->execute();
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $columnsStmt = $pdoESQL->prepare("DESCRIBE " . $tabella['nome_tabella_sql']);
                    $columnsStmt->execute();
                    $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($rows)) {
                        echo '<table border="1">';
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
            }
        } else {
            echo "Nessuna tabella trovata.";
        }
        ?>
    </div>
    <div id="right-panel">
        <h2>Editor SQL</h2>
        <form id="sqlForm" method="POST">
            <textarea id="sqlEditor" name="sql"><?php echo htmlspecialchars($sqlContent); ?></textarea>
            <button type="submit" name="esegui">Esegui</button>
            <button type="submit" name="salva">Salva</button>
        </form>
        <div id="result">
            <h3>Esito:</h3>
            <?php
            if (!empty($response)) {
                echo $response;
            }
            if (isset($message)) {
                echo "<p>$message</p>";
            }
            ?>
        </div>
        <div id="backHome">
            <a href="popolaTest.php?test_name=<?php echo urlencode($titolo_test); ?>"> Torna indietro</a>
        </div>
    </div>
    <script>
        var editor = CodeMirror.fromTextArea(document.getElementById("sqlEditor"), {
            mode: "text/x-mysql",
            lineNumbers: true
        });

        document.getElementById("sqlForm").addEventListener("submit", function(e) {
            if (editor.getValue().trim() === "") {
                alert("Inserire il codice nell'editor");
                e.preventDefault();
            }
        });
    </script>
</body>

</html>
<?php
} else {
    header('Location: ../index.php');
    exit();
}
?>
