<?php
session_start();
include "../TabelleSQL/dbESQL_connect.php";
include "../Autenticazione/db_connect.php";

$message = "";

if (isset($_SESSION['email']) && isset($_SESSION['nome'])) {
    if (isset($_GET['table'])) {
        $nome_tabella = $_GET['table'];
        
        $stmt = $pdoESQL->prepare("DESCRIBE " . $nome_tabella);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $insertSQL = "INSERT INTO $nome_tabella (";
            $valuesSQL = "VALUES (";
            $params = [];
            foreach ($columns as $column) {
                $columnName = $column['Field'];
                if (isset($_POST[$columnName])) {
                    $insertSQL .= "$columnName, ";
                    $valuesSQL .= "?, ";
                    $params[] = $_POST[$columnName];
                }
            }
            $insertSQL = rtrim($insertSQL, ', ') . ') ';
            $valuesSQL = rtrim($valuesSQL, ', ') . ')';
            $finalSQL = $insertSQL . $valuesSQL;

            try {
                $stmt = $pdoESQL->prepare($finalSQL);
                $stmt->execute($params);
                
                $insertAppoggioSQL = "INSERT INTO TabellaAppoggio (nome_tabella) VALUES (?)";
                $stmtAppoggio = $pdo->prepare($insertAppoggioSQL);
                $stmtAppoggio->execute([$nome_tabella]);
                
                $message = "Riga inserita con successo!";
            } catch (PDOException $e) {
                $message = "Errore: " . $e->getMessage();
            }
        }
        
        function getInputType($type) {
            if (preg_match('/^int/i', $type)) {
                return 'number';
            } elseif (preg_match('/^date/i', $type)) {
                return 'date';
            } else {
                return 'text'; 
            }
        }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popola Tabella</title>
    <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>
<body>
    <h1>Popola Tabella: <?php echo htmlspecialchars($nome_tabella); ?></h1>
    <form method="POST" action="popolaTabelle.php?table=<?php echo urlencode($nome_tabella); ?>">
        <?php foreach ($columns as $column): ?>
            <div>
                <label for="<?php echo $column['Field']; ?>"><?php echo $column['Field']; ?>:</label>
                <input type="<?php echo getInputType($column['Type']); ?>" id="<?php echo $column['Field']; ?>" name="<?php echo $column['Field']; ?>" required>
            </div>
        <?php endforeach; ?>
        <button type="submit">Inserisci</button>
    </form>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <div id="back">
        <a href="../TabelleSQL/gestioneTabelle.php"> Torna indietro</a>
    </div>
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