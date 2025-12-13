<?php
session_start();

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home Docente</title>
        <link rel="stylesheet" type="text/css" href="../Style/style.css">
    </head>

    <header>
        <img id="logo" src="../Style/logo.png">
        <h1 id="pageTag">Home</h1>
        <form action="logout.php" method="post" >
            <button class="logout" type="submit">Logout</button>
        </form>
    </header>

    <body>
        <div class="container">
            <h1> Benvenuto/a <?php echo $_SESSION['nome'] ?>,</h1>

            <p>Scegli un'operazione</p>

            <div id="container">
                <a class="box" href="../TabelleSQL/gestioneTabelle.php">
                    <img src="../Style/table.svg">
                    <h4>TabelleSQL</h4>
                    <p>Gestisci le tue tabelle SQL</p>
                </a>
                <a class="box" href="../TestDocente/gestioneTest.php">
                    <img src="../Style/test.svg">
                    <h4>Test</h4>
                    <p>Gestisci i tuoi test</p>
                </a>
                <a class="box" href="../MessaggiDocente/gestioneMessaggi.php">
                    <img src="../Style/message.svg">
                    <h4>Messaggi</h4>
                    <p>Controlla i tuoi messaggi</p>
                </a>
                <a class="box" href="../Statistiche/visualizzaStatistiche.php">
                    <img src="../Style/stats.svg">
                    <h4>Statistiche</h4>
                    <p>Vedi le statistiche</p>
                </a>
            </div>
        </div>

    </body>

    </html>
<?php
} else {
    header('Location: ../index.php');
    exit();
}
?>