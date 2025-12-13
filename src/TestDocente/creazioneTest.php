<?php
session_start();
include "../Autenticazione/db_connect.php";
// require '../MongoDB/vendor/autoload.php'; // Assicurati che il path sia corretto per l'autoload di Composer

// use MongoDB\Client;

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    $message = "";
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_name'])) {
        $testName = $_POST['test_name'];
        $emailDocente = $_SESSION['email'];
        $dataCreazione = date('Y-m-d');
        
        try {
            $checkSQL = "SELECT COUNT(*) AS count FROM test WHERE titolo = :testName";
            $stmtCheck = $pdo->prepare($checkSQL);
            $stmtCheck->bindParam(':testName', $testName);
            $stmtCheck->execute();
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $message = "Un test con questo nome esiste già. Scegli un nome diverso.";
            } else {
                $procedureSQL = "CALL NUOVO_TEST(:testName, :dataCreazione, :emailDocente)";
                $stmtProcedure = $pdo->prepare($procedureSQL);
                $stmtProcedure->bindParam(':testName', $testName);
                $stmtProcedure->bindParam(':dataCreazione', $dataCreazione);
                $stmtProcedure->bindParam(':emailDocente', $emailDocente);
                $stmtProcedure->execute();
                $stmtProcedure->closeCursor();

                if (isset($_FILES['test_image']) && $_FILES['test_image']['error'] === UPLOAD_ERR_OK) {
                    $imageTmpPath = $_FILES['test_image']['tmp_name'];
                    $imageContent = file_get_contents($imageTmpPath);  // Legge il contenuto del file
                    $imageName = basename($_FILES['test_image']['name']);
                    $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
                    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');

                    if (in_array($imageExt, $allowedExtensions)) {
                        $procedureSQL2 = "CALL INSERISCI_FOTO_TEST(:testName, :emailDocente, :foto)";
                        $stmtInsertImage = $pdo->prepare($procedureSQL2);
                        $stmtInsertImage->bindParam(':testName', $testName);
                        $stmtInsertImage->bindParam(':emailDocente', $emailDocente);
                        $stmtInsertImage->bindParam(':foto', $imageContent, PDO::PARAM_LOB);  // Uso del parametro PDO::PARAM_LOB per BLOB
                        $stmtInsertImage->execute();
                    } else {
                        $message = "Errore nel caricamento dell'immagine. Verifica che il file sia di tipo jpg, jpeg, png, o gif.";
                    }
                }

                /* Connessione a MongoDB
                $client = new Client("mongodb://localhost:27017");
                $collection = $client->piattaforma_ESQL->new_test;

                // Preparazione dei dati dell'evento
                $event = [
                    'event' => 'new_test_creation',
                    'test_name' => $testName,
                    'email_docente' => $emailDocente,
                    'data_creazione' => new MongoDB\BSON\UTCDateTime(strtotime($dataCreazione) * 1000), // Converte in formato UTCDateTime
                    'timestamp' => new MongoDB\BSON\UTCDateTime() // Data e ora corrente
                ];

                // Inserimento dell'evento nella collezione di MongoDB
                $collection->insertOne($event);
                */

                // Reindirizza a popolaTest.php
                header("Location: popolaTest.php?test_name=" . urlencode($testName));
                exit();
            }
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
    <title>Creazione Test</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>
<header>
    <a id="logo" href="../Autenticazione/home_docente.php"><img src="../Style/logo.png"></a>
    <h1 id="pageTag">Creazione Test</h1>
</header>
<body>
    <a href="gestioneTest.php">
        <button>Indietro</button>
    </a>
    <h1>Creazione Nuovo Test</h1>
    <form method="POST" action="creazioneTest.php" enctype="multipart/form-data">
        <label for="test_name">Nome del Test:</label>
        <input type="text" id="test_name" name="test_name" required>
        <label for="test_image">Carica immagine del Test:</label>
        <input type="file" id="test_image" name="test_image" accept=".jpg,.jpeg,.png,.gif">
        <button type="submit">Conferma</button>
    </form>
    <?php if ($message) : ?>
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
