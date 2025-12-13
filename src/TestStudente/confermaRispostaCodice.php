<?php
session_start();
include "../Autenticazione/db_connect.php";
include "../TabelleSQL/dbESQL_connect.php"; // Usa $pdoESQL per eseguire le query

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeroQuesito = $_POST['numero_quesito'];
    $sqlTesto = $_POST['sql_' . $numeroQuesito]; // Recupera il codice SQL dallo studente
    $titoloTest = $_POST['titolo_test'];
    $emailStudente = $_SESSION['email'];

    try {
        $sqlSoluzione = "SELECT codice FROM Soluzione_Codice WHERE numero_quesito = :numeroQuesito AND titolo_test = :titoloTest";
        $stmtSoluzione = $pdo->prepare($sqlSoluzione);
        $stmtSoluzione->bindParam(':numeroQuesito', $numeroQuesito, PDO::PARAM_INT);
        $stmtSoluzione->bindParam(':titoloTest', $titoloTest, PDO::PARAM_STR);
        $stmtSoluzione->execute();
        $codiceSoluzione = $stmtSoluzione->fetchColumn();

        if (!$codiceSoluzione) {
            throw new Exception("Soluzione non trovata per il quesito specificato.");
        }

        $stmtStudente = $pdoESQL->prepare($sqlTesto);
        $stmtStudente->execute();
        $resultStudente = $stmtStudente->fetchAll(PDO::FETCH_ASSOC);

        $stmtSoluzione = $pdoESQL->prepare($codiceSoluzione);
        $stmtSoluzione->execute();
        $resultSoluzione = $stmtSoluzione->fetchAll(PDO::FETCH_ASSOC);

        $esito = ($resultStudente === $resultSoluzione);

        $sqlInsertUpdateProcedure = "CALL INSERISCI_O_AGGIORNA_RISPOSTA_CODICE(:esito, :emailStudente, :numeroQuesito, :sqlTesto, :titoloTest)";
        $stmt = $pdo->prepare($sqlInsertUpdateProcedure);
        $stmt->bindParam(':esito', $esito, PDO::PARAM_BOOL);
        $stmt->bindParam(':emailStudente', $emailStudente, PDO::PARAM_STR);
        $stmt->bindParam(':numeroQuesito', $numeroQuesito, PDO::PARAM_INT);
        $stmt->bindParam(':sqlTesto', $sqlTesto, PDO::PARAM_STR);
        $stmt->bindParam(':titoloTest', $titoloTest, PDO::PARAM_STR);
        $stmt->execute();

        header("Location: eseguiTest.php?test_name=" . urlencode($titoloTest) . "&numero_quesito=" . $numeroQuesito . "&esito=successo");
        exit();
    } catch (PDOException $e) {
        header("Location: eseguiTest.php?test_name=" . urlencode($titoloTest) . "&numero_quesito=" . $numeroQuesito . "&esito=errore");
        exit();
    } catch (Exception $e) {
        header("Location: eseguiTest.php?test_name=" . urlencode($titoloTest) . "&numero_quesito=" . $numeroQuesito . "&esito=errore");
        exit();
    }
} else {
    echo "Metodo di richiesta non valido.";
}
?>
