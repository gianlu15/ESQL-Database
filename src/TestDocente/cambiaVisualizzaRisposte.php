<?php
session_start();
include "../Autenticazione/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['titolo_test']) && isset($_POST['visualizza_risposte'])) {
        $titolo_test = $_POST['titolo_test'];
        $current_state = $_POST['visualizza_risposte'];
        $email_docente = $_SESSION['email'];

        $new_state = ($current_state == 1) ? 0 : 1;

        try {
            $sqlProcedure = "CALL AGGIORNA_VISUALIZZAZIONE_RISPOSTE(:titolo_test, :email_docente, :new_state)";
            $stmt = $pdo->prepare($sqlProcedure);
            $stmt->bindParam(':titolo_test', $titolo_test, PDO::PARAM_STR);
            $stmt->bindParam(':email_docente', $email_docente, PDO::PARAM_STR);
            $stmt->bindParam(':new_state', $new_state, PDO::PARAM_BOOL);
            $stmt->execute();

            header('Location: gestioneTest.php');
            exit();
        } catch (PDOException $e) {
            echo "Errore nell'aggiornamento dello stato: " . $e->getMessage();
        }
    }
} else {
    echo "Metodo di richiesta non valido.";
}
?>
