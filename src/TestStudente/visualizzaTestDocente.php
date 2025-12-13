<?php
session_start();
include "../Autenticazione/db_connect.php";

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    $emailDocente = isset($_GET['email_docente']) ? $_GET['email_docente'] : '';
    $emailStudente = $_SESSION['email'];

    try {
        $stmt = $pdo->prepare("CALL VISUALIZZA_TEST_NON_COMPLETI(:email_docente, :email_studente)");
        $stmt->bindParam(':email_docente', $emailDocente, PDO::PARAM_STR);
        $stmt->bindParam(':email_studente', $emailStudente, PDO::PARAM_STR);
        $stmt->execute();

        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Errore: " . $e->getMessage();
        $tests = [];
    }
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizza Test</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
    <style>
        .dati {
            position: relative;
            padding: 15px;
            border: 1px solid #66645f;
            border-radius: 50px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            transition: background-color 0.3s;
        }

        .dati:hover {
            background-color: #f1f0ed;
        }

        .dati h3 {
            margin: 0;
            padding: 0;
        }

        .dati p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #666;
        }

        .dati a.button {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            transition: background-color 0.3s;
        }

        .dati a.button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<header>
    <a id="logo"><img src="../Style/logo.png"></a>
    <h1 id="pageTag">Test Disponibili:</h1>
</header>

<body>

    <?php if (count($tests) > 0) : ?>
        <?php foreach ($tests as $test) : ?>
            <div class="test">
                <div class="dati">
                    <h3><?php echo htmlspecialchars($test['titolo']); ?></h3>
                    <p>Data di creazione: <i><?php echo htmlspecialchars($test['data_creazione']); ?></i></p>
                    <a id="test-docente" href="eseguiTest.php?test_name=<?php echo urlencode($test['titolo']); ?>" class="button">Esegui test</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Nessun test trovato per questo docente.</p>
    <?php endif; ?>
    <div id="back">
        <a href="visualizzaDocenti.php">Torna indietro</a>
    </div>
</body>

</html>

<?php
} else {
    header('Location: ../index.php');
    exit();
}
?>
