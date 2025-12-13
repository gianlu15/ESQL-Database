<?php
session_start();

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home Studente</title>
        <link rel="stylesheet" type="text/css" href="../Style/style.css">
    </head>

    <body>
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
        <div class="container">
            <h1> Ciao <?php echo htmlspecialchars($_SESSION['nome']); ?></h1>
            <button onclick="location.href='../TestStudente/visualizzaDocenti.php'">Visualizza test disponibili</button>
            <button onclick="location.href='../TestStudente/visualizzaEsiti.php'">Visualizza test completati</button>
            <button onclick="location.href='../MessaggiStudente/gestioneMessaggi.php'">Messaggi</button>
            <button onclick="location.href='../Statistiche/visualizzaStatistiche.php'">Statistiche</button>
        </div>
    </body>

    </html>
    <?php
} else {
    header('Location: ../index.php');
    exit();
}
?>
