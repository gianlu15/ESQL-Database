<?php
session_start();
include "../Autenticazione/db_connect.php";
include "dbESQL_connect.php";
// require '../MongoDB/vendor/autoload.php'; // Assicurati che il path sia corretto per l'autoload di Composer

// use MongoDB\Client;

$sqlContent = '';

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql'])) {
        $sqlContent = $_POST['sql'];
        $response = '';

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
            } else if (preg_match('/^\s*INSERT\s+INTO\s+`?(\w+)`?\s/i', $sql, $matches)) {
                $tableName = $matches[1];

                $insertAppoggioSQL = "INSERT INTO TabellaAppoggio (nome_tabella) VALUES (?)";
                $stmtAppoggio = $pdo->prepare($insertAppoggioSQL);
                $stmtAppoggio->execute([$tableName]);

                $response .= "<br>Righe inserite.";
            } else {
                $response .= "Comando eseguito con successo.";

                if (preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $sql, $matches)) {
                    $tableName = $matches[1];
                    $procedureSQL = "CALL CREAZIONE_TABELLA(:tableName, :emailDocente)";
                    $stmtProcedure = $pdo->prepare($procedureSQL);
                    $stmtProcedure->bindParam(':tableName', $tableName);
                    $stmtProcedure->bindParam(':emailDocente', $_SESSION['email']);
                    $stmtProcedure->execute();

                    // Connessione a MongoDB e inserimento dell'evento
                    /* Connessione a MongoDB
                    $client = new Client("mongodb://localhost:27017");
                    $collection = $client->piattaforma_ESQL->new_table;

                    // Preparazione dei dati dell'evento
                    $event = [
                        'event' => 'new_table_creation',
                        'user_email' => $_SESSION['email'],
                        'table_name' => $tableName,
                        'sql_query' => $sql,
                        'timestamp' => new MongoDB\BSON\UTCDateTime() // Data e ora corrente
                    ];

                    // Inserimento dell'evento nella collezione di MongoDB
                    $collection->insertOne($event);
                    */

                    $primaryKeyColumns = [];
                    if (preg_match('/PRIMARY KEY\s*\(([^)]+)\)/i', $sql, $pkMatch)) {
                        $primaryKeyColumns = array_map('trim', explode(',', $pkMatch[1]));
                    }

                    if (preg_match_all('/`?(\w+)`?\s+(\w+(?:\(\d+\))?)(.*?)(?:,|$)/i', $sql, $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $match) {
                            $attributeName = $match[1];
                            $attributeType = $match[2];
                            $attributeProperties = isset($match[3]) ? $match[3] : '';
                            $isPrimaryKey = in_array($attributeName, $primaryKeyColumns) ? 1 : 0;

                            $procedureAttrSQL = "CALL NUOVO_ATTRIBUTO(:tableName, :attributeName, :attributeType, :isPrimaryKey)";
                            $stmtAttrProcedure = $pdo->prepare($procedureAttrSQL);
                            $stmtAttrProcedure->bindParam(':tableName', $tableName);
                            $stmtAttrProcedure->bindParam(':attributeName', $attributeName);
                            $stmtAttrProcedure->bindParam(':attributeType', $attributeType);
                            $stmtAttrProcedure->bindParam(':isPrimaryKey', $isPrimaryKey);
                            $stmtAttrProcedure->execute();
                        }
                    }

                    if (preg_match_all('/FOREIGN KEY\s*\(`?(\w+)`?\)\s*REFERENCES\s*`?(\w+)`?\s*\(`?(\w+)`?\)/i', $sql, $fkMatches, PREG_SET_ORDER)) {
                        foreach ($fkMatches as $fkMatch) {
                            $attributeName1 = $fkMatch[1];
                            $tableName2 = $fkMatch[2];
                            $attributeName2 = $fkMatch[3];

                            $procedureFkSQL = "CALL NUOVO_VINCOLO(:attributeName1, :attributeName2, :tableName1, :tableName2)";
                            $stmtFkProcedure = $pdo->prepare($procedureFkSQL);
                            $stmtFkProcedure->bindParam(':attributeName1', $attributeName1);
                            $stmtFkProcedure->bindParam(':attributeName2', $attributeName2);
                            $stmtFkProcedure->bindParam(':tableName1', $tableName);
                            $stmtFkProcedure->bindParam(':tableName2', $tableName2);
                            $stmtFkProcedure->execute();
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $response .= "Errore: " . $e->getMessage();
        }
    }
?>
    <!DOCTYPE html>
    <html lang="it">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SQL Editor</title>
        <link rel="stylesheet" type="text/css" href="../Style/styleEditor.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js"></script>
    </head>

    <body>
        <h1>Creazione Tabelle</h1>
        <p>Crea una nuova tabella utilizzando l'editor</p>
        <div>
            <form id="sqlForm" method="POST" action="creazioneTabelle.php">
                <textarea id="sqlEditor" name="sql"><?php echo htmlspecialchars($sqlContent); ?></textarea>
                <button type="submit">Esegui</button>
            </form>
        </div>
        <p>Esito:</p>
        <div id="result">
            <?php
            if (!empty($response)) {
                echo $response;
            }
            ?>
        </div>
        <div id="backHome">
            <a href="../TabelleSQL/gestioneTabelle.php"> Torna indietro</a>
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
