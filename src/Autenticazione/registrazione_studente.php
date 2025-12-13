<?php
session_start();
include "db_connect.php";
// require '../MongoDB/vendor/autoload.php'; // Assicurati che il path sia corretto per l'autoload di Composer

// use MongoDB\Client;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $nome = $_POST['name'];
    $cognome = $_POST['cognome'];
    $anno_immatricolazione = $_POST['anno'];
    $codice_alfanumerico = $_POST['codice'];
    $recapito_telefonico = isset($_POST['numerotelefono']) ? $_POST['numerotelefono'] : null;

    try {
        $stmt = $pdo->prepare("CALL REGISTRAZIONE_STUDENTE(:email, :password, :nome, :cognome, :anno_immatricolazione, :codice_alfanumerico, :rec_telefonico, @success)");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':cognome', $cognome, PDO::PARAM_STR);
        $stmt->bindParam(':anno_immatricolazione', $anno_immatricolazione, PDO::PARAM_STR);
        $stmt->bindParam(':codice_alfanumerico', $codice_alfanumerico, PDO::PARAM_STR);
        $stmt->bindParam(':rec_telefonico', $recapito_telefonico, PDO::PARAM_STR);
        $stmt->execute();

        $success = $pdo->query("SELECT @success")->fetch(PDO::FETCH_ASSOC)['@success'];

        if ($success) {
            $_SESSION['email'] = $email;
            $_SESSION['nome'] = $nome;
            $_SESSION['tipo_utente'] = 'studente';

            /* Connessione a MongoDB
            $client = new Client("mongodb://localhost:27017");
            $collection = $client->piattaforma_ESQL->new_user;

            // Preparazione dei dati dell'evento
            $event = [
                'event' => 'new_user_registration',
                'user_type' => 'studente',
                'email' => $email,
                'nome' => $nome,
                'cognome' => $cognome,
                'anno_immatricolazione' => $anno_immatricolazione,
                'codice_alfanumerico' => $codice_alfanumerico,
                'timestamp' => new MongoDB\BSON\UTCDateTime() // Data e ora corrente
            ];

            // Inserimento dell'evento nella collezione di MongoDB
            $collection->insertOne($event);
            */

            header("Location: home_studente.php");
            exit();
        } else {
            $error = "Utente già presente!";
        }
    } catch (PDOException $e) {
        $error = "Errore durante la registrazione: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Registrazione_Studente</title>
  <link rel="stylesheet" type="text/css" href="../Style/style.css">
</head>

<body>
  <form action="" method="post">
    <h3> Inserisci i tuoi dati </h3>

    <?php if (isset($error)) { ?>
      <p class="error"><?php echo $error; ?></p>
    <?php } ?>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required />

    <label for="name">Nome: </label>
    <input type="text" id="name" name="name" required />

    <label for="cognome">Cognome:</label>
    <input type="text" id="cognome" name="cognome" required />

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required />

    <label for="anno">Anno Immatricolazione:</label>
    <input type="text" id="anno" name="anno" minlength="4" maxlength="4" pattern="\d{4}" placeholder="YYYY" />

    <label for="codice">Codice Alfanumerico:</label>
    <input type="text" id="codice" name="codice" minlength="16" maxlength="16" placeholder="Inserisci il codice" />

    <label for="numerotelefono">Telefono:</label>
    <input type="tel" id="numerotelefono" minlength="10" maxlength="14"  pattern="[0-9]+" name="numerotelefono" />

    <button type="submit">REGISTRATI</button>

    <button type="button" onclick="resetForm()">Annulla</button>
  </form>

  <script>
    function resetForm() {
      window.location.href = '../index.php';
    }
  </script>
  
</body>

</html>
