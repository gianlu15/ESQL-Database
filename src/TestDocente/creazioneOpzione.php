<?php
session_start();
include "../Autenticazione/db_connect.php";
// require '../MongoDB/vendor/autoload.php'; // Assicurati che il path sia corretto per l'autoload di Composer

// use MongoDB\Client;

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) 
{
    $titolo_test = '';
    $numero_quesito = '';

    if (isset($_GET['titolo_test']) && isset($_GET['numero_quesito'])) {
        $titolo_test = htmlspecialchars($_GET['titolo_test']);
        $numero_quesito = htmlspecialchars($_GET['numero_quesito']);
    } else {
        echo "Parametri mancanti.";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    {
        $opzioni = [];
        for ($i = 1; $i <= 4; $i++) {
            $corretta = ($_POST['corretta'] === "opzione$i") ? 1 : 0;
            $opzioni[] = [
                'campo_testo' => htmlspecialchars($_POST["descrizione$i"]),
                'corretta' => $corretta
            ];
        }
        $descrizioni = array_column($opzioni, 'campo_testo');
        if (count($descrizioni) !== count(array_unique($descrizioni)))
            echo "<script>alert('Non ci possono essere descrizioni uguali'); window.history.back();</script>";
        else 
        {
            try 
            {
                $procedureSQL = "CALL INSERISCI_OPZIONE(:numero_quesito, :titolo_test, :campo_testo, :corretta)";
                $stmtProcedure = $pdo->prepare($procedureSQL);

                foreach ($opzioni as $opzione) {
                    $stmtProcedure->bindParam(':numero_quesito', $numero_quesito, PDO::PARAM_INT);
                    $stmtProcedure->bindParam(':titolo_test', $titolo_test, PDO::PARAM_STR);
                    $stmtProcedure->bindParam(':campo_testo', $opzione['campo_testo'], PDO::PARAM_STR);
                    $stmtProcedure->bindParam(':corretta', $opzione['corretta'], PDO::PARAM_INT);
                    $stmtProcedure->execute();
                }

                // Inserimento dell'evento in MongoDB
                /* Connessione a MongoDB
                $client = new Client("mongodb://localhost:27017");
                $collection = $client->piattaforma_ESQL->new_option;

                // Preparazione dei dati dell'evento
                $event = [
                    'event' => 'new_option_creation',
                    'test_name' => $titolo_test,
                    'question_number' => $numero_quesito,
                    'options' => $opzioni,
                    'timestamp' => new MongoDB\BSON\UTCDateTime() // Data e ora corrente
                ];

                // Inserimento dell'evento nella collezione di MongoDB
                $collection->insertOne($event);
                */

                // Redirect alla pagina del test dopo l'inserimento
                header("Location: popolaTest.php?test_name=" . urlencode($titolo_test));
                exit();

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
    <title>Creazione Opzione</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>
<header>
    <a id="logo" href="../Autenticazione/home_docente.php"><img src="../Style/logo.png"></a>
    <h1 id="pageTag">Creazione Opzione</h1>
</header>
<body>
    <div id="back">
        <a href='popolaTest.php?test_name=<?php echo urlencode($titolo_test); ?>'>Torna indietro</a>
    </div>
    <form method="POST" action="creazioneOpzione.php?numero_quesito=<?php echo urlencode($numero_quesito); ?>&titolo_test=<?php echo urlencode($titolo_test); ?>">
        <input type="hidden" name="numero_quesito" value="<?php echo htmlspecialchars($numero_quesito); ?>">
        <input type="hidden" name="titolo_test" value="<?php echo htmlspecialchars($titolo_test); ?>">

        <?php for ($i = 1; $i <= 4; $i++) : ?>
            <div class="opzione">
                <h3>Opzione <?php echo $i; ?></h3>
                <br>
                <label for="descrizione<?php echo $i; ?>">Descrizione</label>
                <br>
                <textarea id="descrizione<?php echo $i; ?>" name="descrizione<?php echo $i; ?>" required></textarea>
                <br>
                <input type="radio" id="corretta<?php echo $i; ?>" name="corretta" value="opzione<?php echo $i; ?>" required>
                <label for="corretta<?php echo $i; ?>">Corretta</label>
            </div>
        <?php endfor; ?>

        <button type="submit">Conferma</button>
    </form>

    <?php if (isset($message)) : ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
</body>
</html>

<?php
} else {
    header('Location: ../index.php');
    exit();
}
?>
