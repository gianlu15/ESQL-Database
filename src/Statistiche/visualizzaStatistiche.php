<?php
session_start();
include "../Autenticazione/db_connect.php";

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    $classificaStudenti = [];
    $classificaAccuratezza = [];
    $message = "";

    try {
        $sqlTestCompletati = "SELECT * FROM Classifica_Studenti_Test_Completati";
        $stmtTestCompletati = $pdo->prepare($sqlTestCompletati);
        $stmtTestCompletati->execute();
        $classificaStudenti = $stmtTestCompletati->fetchAll(PDO::FETCH_ASSOC);

        $sqlAccuratezza = "SELECT * FROM Classifica_Studenti_Accuratezza";
        $stmtAccuratezza = $pdo->prepare($sqlAccuratezza);
        $stmtAccuratezza->execute();
        $classificaAccuratezza = $stmtAccuratezza->fetchAll(PDO::FETCH_ASSOC);

        $sqlQuesitiRisposte = "SELECT * FROM Classifica_Quesiti_Risposte";
        $stmtQuesitiRisposte = $pdo->prepare($sqlQuesitiRisposte);
        $stmtQuesitiRisposte->execute();
        $classificaQuesiti = $stmtQuesitiRisposte->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Errore: " . $e->getMessage();
    }
?>

    <!DOCTYPE html>
    <html lang="it">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Statistiche Test</title>
        <link rel="stylesheet" type="text/css" href="../Style/style.css">
    </head>
    <header>
        <a id="logo" href="<?php echo $_SESSION['tipo_utente'] == 'docente' ? '../Autenticazione/home_docente.php' : '../Autenticazione/home_studente.php'; ?>"><img src="../Style/logo.png"></a>
        <h1 id="pageTag">Statistiche Test</h1>
    </header>

    <body>
        <a href="<?php echo $_SESSION['tipo_utente'] == 'docente' ? '../Autenticazione/home_docente.php' : '../Autenticazione/home_studente.php'; ?>">
            <button>Indietro</button>
        </a>
        <br>
        <?php if ($message) : ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php else : ?>
            <h2>Classifica degli Studenti per Numero di Test Completati</h2>
            <?php if (count($classificaStudenti) > 0) : ?>
                <table>
                    <thead>
                        <tr>
                            <th>Codice Alfanumerico Studente</th>
                            <th>Numero di Test Completati</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classificaStudenti as $studente) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($studente['codice_alfanumerico']); ?></td>
                                <td><?php echo htmlspecialchars($studente['NumeroTestCompletati']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>Nessuno studente ha completato un test.</p>
            <?php endif; ?>

            <h2>Classifica degli Studenti per Accuratezza delle Risposte</h2>
            <?php if (count($classificaAccuratezza) > 0) : ?>
                <table>
                    <thead>
                        <tr>
                            <th>Codice Alfanumerico Studente</th>
                            <th>Risposte Corrette</th>
                            <th>Totale Risposte</th>
                            <th>Percentuale Correttezza</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classificaAccuratezza as $studente) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($studente['codice_alfanumerico']); ?></td>
                                <td><?php echo htmlspecialchars($studente['RisposteCorrette']); ?></td>
                                <td><?php echo htmlspecialchars($studente['TotaleRisposte']); ?></td>
                                <td><?php echo number_format($studente['PercentualeCorrettezza'], 2); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>Nessuno studente ha inserito risposte.</p>
            <?php endif; ?>

            <h2>Classifica dei Quesiti per Numero di Risposte Inserite</h2>
            <?php if (count($classificaQuesiti) > 0) : ?>
                <table>
                    <thead>
                        <tr>
                            <th>Numero Quesito</th>
                            <th>Titolo Test</th>
                            <th>Descrizione</th>
                            <th>Difficoltà</th>
                            <th>Tipo</th>
                            <th>Totale Risposte Inserite</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classificaQuesiti as $quesito) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($quesito['numero_progressivo']); ?></td>
                                <td><?php echo htmlspecialchars($quesito['titolo_test']); ?></td>
                                <td><?php echo htmlspecialchars($quesito['descrizione']); ?></td>
                                <td><?php echo htmlspecialchars($quesito['difficolta']); ?></td>
                                <td><?php echo htmlspecialchars($quesito['tipo']); ?></td>
                                <td><?php echo htmlspecialchars($quesito['TotaleRisposteInserite']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>Nessuna risposta inserita per i quesiti.</p>
            <?php endif; ?>
        <?php endif; ?>
    </body>

    </html>

<?php
} else {
    header('Location: ../index.php');
    exit();
}
?>