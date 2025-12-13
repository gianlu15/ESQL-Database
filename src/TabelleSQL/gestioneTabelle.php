<?php
session_start();
include "../Autenticazione/db_connect.php";


if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {

    $email_docente = $_SESSION['email'];
    $stmt = $pdo->prepare("CALL VISUALIZZAZIONE_TABELLA(?)");
    $stmt->bindParam(1, $email_docente, PDO::PARAM_STR, 255);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tabelle Docente</title>
        <link rel="stylesheet" type="text/css" href="../Style/style.css">
    </head>

    <header>
        <a id="logo" href="../Autenticazione/home_docente.php"><img src="../Style/logo.png"></a>
        <h1 id="pageTag">Gestione Tabelle</h1>
    </header>

    <body>
        <a href="../Autenticazione/home_docente.php">
            <button>Indietro</button>
        </a>
        <a href="../TabelleSQL/creazioneTabelle.php"><button>Nuova Tabella</button></a>
        <br>
        <?php

        if (isset($tables)) {
            echo "<h2>Tabelle associate all'email del docente: $email_docente</h2>";
            echo "<table>";
            echo '<thead><tr><th>Nome Tabella</th><th>Data Creazione</th><th>Numero Righe</th><th>Inserisci Righe</th><th>Visualizza Tabella</th></tr></thead>';
            echo "<tbody>";
            foreach ($tables as $table) {
                echo "<tr>";
                echo "<td>" . $table['nome_tabella'] . "</td>";
                echo "<td>" . $table['data_creazione'] . "</td>";
                echo "<td>" . $table['num_righe'] . "</td>";
                echo '<td><a href="../TabelleSQL/popolaTabelle.php?table=' . urlencode($table['nome_tabella']) . '"> Popola ></a></td>';
                echo '<td><a href="../TabelleSQL/visualizzaTabelle.php?table=' . urlencode($table['nome_tabella']) . '"> Visualizza ></a></td>';
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
    header('Location: ../Autenticazione/home_docente.php');
    exit();
}
?>