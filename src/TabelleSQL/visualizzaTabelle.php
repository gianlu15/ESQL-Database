<?php
session_start();
include "../TabelleSQL/dbESQL_connect.php";
include "../Autenticazione/db_connect.php";

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    if (isset($_GET['table'])) {
        $nome_tabella = $_GET['table'];
        
        try {
            $stmt = $pdoESQL->prepare("SELECT * FROM " . $nome_tabella);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columnsStmt = $pdoESQL->prepare("DESCRIBE " . $nome_tabella);
            $columnsStmt->execute();
            $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $error = "Errore: " . $e->getMessage();
        }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizza Tabella</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>

<body>
    <h1>Visualizzazione della tabella: <?php echo htmlspecialchars($nome_tabella); ?></h1>
    
    <?php if (isset($error)): ?>
        <p><?php echo $error; ?></p>
    <?php else: ?>
        <table border="1">
            <thead>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <th><?php echo htmlspecialchars($column); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <br>
    <a href="../TabelleSQL/gestioneTabelle.php"><button>Torna indietro</button></a>
</body>

</html>

<?php
    } else {
        echo "Errore: Nome della tabella non fornito.";
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>
