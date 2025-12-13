<?php
session_start();
include "../Autenticazione/db_connect.php";
// require '../MongoDB/vendor/autoload.php'; // Assicurati che il path sia corretto per l'autoload di Composer

// use MongoDB\Client;

if (!isset($_SESSION['email']) || !isset($_SESSION['nome']) || !isset($_POST['email_docente'])) {
    header('Location: ../Autenticazione/login.php');
    exit();
}

$emailDocente = $_POST['email_docente'];
$emailStudente = $_SESSION['email'];

try {
    $stmt = $pdo->prepare("CALL VISUALIZZAZIONE_TEST(:emailDocente)");
    $stmt->bindParam(':emailDocente', $emailDocente, PDO::PARAM_STR);
    $stmt->execute();
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (PDOException $e) {
    $error_message = "Errore nel recupero dei test: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titolo'], $_POST['messaggio'], $_POST['titolo_test'])) {
    $titolo = $_POST['titolo'];
    $messaggio = $_POST['messaggio'];
    $titolo_test = $_POST['titolo_test'];

    try {
        $stmt = $pdo->prepare("CALL INSERIMENTO_MESSAGGIO_STUDENTE(NOW(), :titolo, :messaggio, :email_studente, :email_docente, :titolo_test)");
        $stmt->bindParam(':titolo', $titolo, PDO::PARAM_STR);
        $stmt->bindParam(':messaggio', $messaggio, PDO::PARAM_STR);
        $stmt->bindParam(':email_studente', $emailStudente, PDO::PARAM_STR);
        $stmt->bindParam(':email_docente', $emailDocente, PDO::PARAM_STR);
        $stmt->bindParam(':titolo_test', $titolo_test, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();
        $success_message = "Messaggio inviato con successo!";

        // Connessione a MongoDB e inserimento dell'evento
        /* Connessione a MongoDB
        $client = new Client("mongodb://localhost:27017");
        $collection = $client->piattaforma_ESQL->new_message;

        // Preparazione dei dati dell'evento
        $event = [
            'event' => 'new_message_creation',
            'user_type' => 'studente',
            'email_studente' => $emailStudente,
            'email_docente' => $emailDocente,
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'titolo_test' => $titolo_test,
            'timestamp' => new MongoDB\BSON\UTCDateTime() // Data e ora corrente
        ];

        // Inserimento dell'evento nella collezione di MongoDB
        $collection->insertOne($event);
        */

    } catch (PDOException $e) {
        $error_message = "Errore durante l'invio del messaggio: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inserisci Dettagli Messaggio</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>
<header>
    <a id="logo"><img src="../Style/logo.png"></a>
    <h1 id="pageTag">Inserisci i dettagli del messaggio</h1>
</header>
<body>
   
    <?php if (isset($success_message)) { echo "<p style='color:green;'>$success_message</p>"; } ?>
    <?php if (isset($error_message)) { echo "<p style='color:red;'>$error_message</p>"; } ?>
    
    <form method="POST" action="">
        <input type="hidden" name="email_docente" value="<?php echo htmlspecialchars($emailDocente); ?>">

        <label for="titolo">Titolo:</label>
        <input type="text" id="titolo" name="titolo" required>

        <label for="messaggio">Messaggio:</label>
        <textarea id="messaggio" name="messaggio" rows="5" required></textarea>

        <label for="titolo_test">Test di riferimento:</label>
        <select id="titolo_test" name="titolo_test" required>
            <?php foreach ($tests as $test): ?>
                <option value="<?php echo htmlspecialchars($test['titolo']); ?>"><?php echo htmlspecialchars($test['titolo']); ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Invia Messaggio</button>
    </form>
    <div id="back">
        <a href='gestioneMessaggi.php'>Torna indietro</a>
    </div>
</body>
</html>
