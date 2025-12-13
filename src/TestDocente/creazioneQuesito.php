<?php
session_start();
include "../Autenticazione/db_connect.php";
// require '../MongoDB/vendor/autoload.php'; // Assicurati che il path sia corretto per l'autoload di Composer

// use MongoDB\Client;

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    $message = "";
    $testName = "";
    $tipoQuesito = "";

    if (isset($_GET['test_name']) && isset($_GET['tipo'])) {
        $testName = $_GET['test_name'];
        $tipoQuesito = $_GET['tipo'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $difficolta = $_POST['difficolta'];
        $descrizione = $_POST['descrizione'];
        $selectedTables = isset($_POST['selected-tables']) ? $_POST['selected-tables'] : [];

        try {
            $pdo->beginTransaction();

            $procedureSQL = "CALL INSERISCI_QUESITO(:titolo_test, :difficolta, :descrizione, :tipo, @numero_progressivo)";
            $stmtProcedure = $pdo->prepare($procedureSQL);
            $stmtProcedure->bindParam(':titolo_test', $testName);
            $stmtProcedure->bindParam(':difficolta', $difficolta);
            $stmtProcedure->bindParam(':descrizione', $descrizione);
            $stmtProcedure->bindParam(':tipo', $tipoQuesito);
            $stmtProcedure->execute();

            $stmtProcedure = $pdo->query("SELECT @numero_progressivo AS numero_progressivo");
            $result = $stmtProcedure->fetch(PDO::FETCH_ASSOC);
            $numeroQuesito = $result['numero_progressivo'];

            if (!$numeroQuesito) {
                throw new PDOException("Impossibile recuperare il numero progressivo del quesito.");
            }

            if (!empty($selectedTables)) {
                $procedureSQL = "CALL INSERISCI_QUESITO_TABELLA(:numero_progressivo, :titolo_test, :nome_tabella_sql)";
                $stmtProcedure = $pdo->prepare($procedureSQL);
                foreach ($selectedTables as $nomeTabella) {
                    $stmtProcedure->bindParam(':numero_progressivo', $numeroQuesito);
                    $stmtProcedure->bindParam(':titolo_test', $testName);
                    $stmtProcedure->bindParam(':nome_tabella_sql', $nomeTabella);
                    $stmtProcedure->execute();
                }
            } else {
                $message = "Nessuna tabella selezionata.";
            }

            // Aggiungi l'evento a MongoDB
            /* Connessione a MongoDB
            $client = new Client("mongodb://localhost:27017");
            $collection = $client->piattaforma_ESQL->new_question;

            // Preparazione dei dati dell'evento
            $event = [
                'event' => 'new_question_creation',
                'test_name' => $testName,
                'question_number' => $numeroQuesito,
                'difficulty' => $difficolta,
                'description' => $descrizione,
                'tables' => $selectedTables,
                'timestamp' => new MongoDB\BSON\UTCDateTime() // Data e ora corrente
            ];

            // Inserimento dell'evento nella collezione di MongoDB
            $collection->insertOne($event);
            */

            $pdo->commit();

            header("Location: popolaTest.php?test_name=" . urlencode($testName));
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Errore: " . $e->getMessage();
        }
    }

    $emailDocente = $_SESSION['email'];
    $stmt = $pdo->prepare("CALL VISUALIZZAZIONE_TABELLA(?)");
    $stmt->execute([$emailDocente]);
    $tabelle = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <!DOCTYPE html>
    <html lang="it">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Creazione Quesito <?php echo htmlspecialchars($tipoQuesito); ?></title>
        <link rel="stylesheet" type="text/css" href="../Style/style.css">
        <script>
            function validateForm(event) {
                var checkboxes = document.querySelectorAll('input[name="selected-tables[]"]');
                var checkedOne = Array.prototype.slice.call(checkboxes).some(x => x.checked);

                if (!checkedOne) {
                    event.preventDefault();
                    alert("Seleziona almeno una tabella.");
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                var form = document.querySelector('form');
                form.addEventListener('submit', validateForm);
            });
        </script>
    </head>

    <header>
        <a id="logo" href="../Autenticazione/home_docente.php"><img src="../Style/logo.png"></a>
        <h1 id="pageTag">Creazione Quesito <?php echo htmlspecialchars($tipoQuesito); ?></h1>
    </header>

    <body>
        <a href='popolaTest.php?test_name=<?php echo urlencode($testName); ?>'>
            <button>Indietro</button>
        </a>
        <h1>Nome Test: <?php echo htmlspecialchars($testName); ?></h1>

        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST" action="creazioneQuesito.php?test_name=<?php echo urlencode($testName); ?>&tipo=<?php echo urlencode($tipoQuesito); ?>">
            <label for="tabelle-disponibili">Seleziona le tabelle:</label>
            <div id="tabelle-disponibili">
                <?php foreach ($tabelle as $tabella): ?>
                    <div>
                        <input type="checkbox" id="tabella-<?php echo htmlspecialchars($tabella['nome_tabella']); ?>" name="selected-tables[]" value="<?php echo htmlspecialchars($tabella['nome_tabella']); ?>">
                        <label for="tabella-<?php echo htmlspecialchars($tabella['nome_tabella']); ?>"><?php echo htmlspecialchars($tabella['nome_tabella']); ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <label for="difficolta">Difficoltà</label>
            <select id="difficolta" name="difficolta">
                <option value="Basso">Bassa</option>
                <option value="Medio">Media</option>
                <option value="Alto">Alta</option>
            </select>

            <label for="descrizione">Descrizione</label>
            <input type="text" id="descrizione" name="descrizione" required>

            <button type="submit">Conferma</button>
        </form>
    </body>

    </html>

<?php
} else {
    header('Location: ../index.php');
    exit();
}
?>