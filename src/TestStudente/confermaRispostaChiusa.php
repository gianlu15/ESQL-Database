<?php
session_start();
include "../Autenticazione/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeroQuesito = $_POST['numero_quesito'];
    $numeroOpzione = $_POST['opzione'];
    $titoloTest = $_POST['titolo_test'];
    $emailStudente = $_SESSION['email'];

    try {
        $sqlProcedure = "CALL VERIFICA_OPZIONE_CORRETTA(:numeroQuesito, :numeroOpzione, :titoloTest, @corretta)";
        $stmt = $pdo->prepare($sqlProcedure);
        $stmt->bindParam(':numeroQuesito', $numeroQuesito, PDO::PARAM_INT);
        $stmt->bindParam(':numeroOpzione', $numeroOpzione, PDO::PARAM_INT);
        $stmt->bindParam(':titoloTest', $titoloTest, PDO::PARAM_STR);
        $stmt->execute();

        $stmt = $pdo->query("SELECT @corretta AS corretta");
        $corretta = $stmt->fetchColumn();
        $esito = ($corretta) ? true : false;

        $sqlInsertUpdateProcedure = "CALL INSERISCI_O_AGGIORNA_RISPOSTA_CHIUSA(:esito, :emailStudente, :numeroQuesito, :numeroOpzione, :titoloTest)";
        $stmt = $pdo->prepare($sqlInsertUpdateProcedure);
        $stmt->bindParam(':esito', $esito, PDO::PARAM_BOOL);
        $stmt->bindParam(':emailStudente', $emailStudente, PDO::PARAM_STR);
        $stmt->bindParam(':numeroQuesito', $numeroQuesito, PDO::PARAM_INT);
        $stmt->bindParam(':numeroOpzione', $numeroOpzione, PDO::PARAM_INT);
        $stmt->bindParam(':titoloTest', $titoloTest, PDO::PARAM_STR);
        $stmt->execute();

        header("Location: eseguiTest.php?test_name=" . urlencode($titoloTest) . "&numero_quesito=" . $numeroQuesito . "&esito=successo");
        exit();
    } catch (PDOException $e) {
        header("Location: eseguiTest.php?test_name=" . urlencode($titoloTest) . "&numero_quesito=" . $numeroQuesito . "&esito=errore");
        exit();
    }
} else {
    echo "Metodo di richiesta non valido.";
}
?>
