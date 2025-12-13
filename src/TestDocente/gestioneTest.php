<?php
session_start();
include "../Autenticazione/db_connect.php";

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {

    $email_docente = $_SESSION['email'];
    $stmt = $pdo->prepare("CALL VISUALIZZAZIONE_TEST(?)");
    $stmt->bindParam(1, $email_docente, PDO::PARAM_STR, 255);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Docente</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
    <style>
        .logout-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>

<header>
    <a id="logo" href="../Autenticazione/home_docente.php"><img src="../Style/logo.png"></a>
    <h1 id="pageTag">Gestione Test</h1>
    <a class="logout-btn" href="../Autenticazione/logout.php">
        <button>Logout</button>
    </a>
</header>

<body>
    <a href="../Autenticazione/home_docente.php">
        <button>Indietro</button>
    </a>
    <a href="creazioneTest.php"><button>Nuovo Test</button></a>
    <br>
    <?php
    if (isset($tables)) {
        echo "<h2>Test associati all'email del docente: $email_docente</h2>";
        echo "<table>";
        echo '<thead><tr><th>Titolo test</th><th>Data Creazione</th><th>Visualizza Risposte</th><th>Visualizza</th></tr></thead>';
        echo "<tbody>";
        foreach ($tables as $table) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($table['titolo']) . "</td>";
            echo "<td>" . htmlspecialchars($table['data_creazione']) . "</td>";
            echo "<td>";
            echo '<form method="post" action="cambiaVisualizzaRisposte.php">';
            echo '<input type="hidden" name="titolo_test" value="' . htmlspecialchars($table['titolo']) . '">';
            echo '<input type="hidden" name="visualizza_risposte" value="' . $table['visualizza_risposte'] . '">';
            echo '<button type="submit">' . ($table["visualizza_risposte"] ? "true" : "false"). '</button>';
            echo '</form>';
            echo "</td>";
            echo '<td><a href="visualizzaTest.php?test_name=' . urlencode($table['titolo']) . '"> Visualizza ></a></td>';
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    }
    ?>

</body>

</html>
<?php
} else {
    header('Location: ../index.php');
    exit();
}
?>
