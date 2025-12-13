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
    $nome_dipartimento = $_POST['nomedip'];
    $nome_corso = $_POST['nomecorso'];
    $recapito_telefonico = isset($_POST['numerotelefono']) ? $_POST['numerotelefono'] : null;

    try {
        $stmt = $pdo->prepare("CALL REGISTRAZIONE_DOCENTE(:email, :password, :nome, :cognome, :nome_dipartimento, :nome_corso, :rec_telefonico, @success)");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':cognome', $cognome, PDO::PARAM_STR);
        $stmt->bindParam(':nome_dipartimento', $nome_dipartimento, PDO::PARAM_STR);
        $stmt->bindParam(':nome_corso', $nome_corso, PDO::PARAM_STR);
        $stmt->bindParam(':rec_telefonico', $recapito_telefonico, PDO::PARAM_STR);
        $stmt->execute();

        $success = $pdo->query("SELECT @success")->fetch(PDO::FETCH_ASSOC)['@success'];

        if ($success) {
            $_SESSION['email'] = $email;
            $_SESSION['nome'] = $nome;
            $_SESSION['tipo_utente'] = 'docente';

            /* Connessione a MongoDB
            $client = new Client("mongodb://localhost:27017");
            $collection = $client->piattaforma_ESQL->new_user;

            // Preparazione dei dati dell'evento
            $event = [
                'event' => 'new_user_registration',
                'user_type' => 'docente',
                'email' => $email,
                'nome' => $nome,
                'cognome' => $cognome,
                'dipartimento' => $nome_dipartimento,
                'corso' => $nome_corso,
                'timestamp' => new MongoDB\BSON\UTCDateTime() // Data e ora corrente
            ];

            // Inserimento dell'evento nella collezione di MongoDB
            $collection->insertOne($event);
            */
            
            header("Location: home_docente.php");
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
  <title>Registrazione_Docente</title>
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

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required />

    <label for="name">Nome: </label>
    <input type="text" id="name" name="name" required />

    <label for="cognome">Cognome:</label>
    <input type="text" id="cognome" name="cognome" required />

    <label for="nomedip">Nome Dipartimento:</label>
    <input type="text" id="nomedip" name="nomedip" required />

    <label for="nomecorso">Nome Corso:</label>
    <input type="text" id="nomecorso" name="nomecorso" required />

    <label for="numerotelefono">Telefono:</label>
    <input
        type="tel"
        id="numerotelefono"
        minlength="10" maxlength="14"  pattern="[0-9]+"
        name="numerotelefono"
        placeholder="0039 XXXXXXXX"
      />

    <button type="submit">REGISTRATI</button>
  </form>
</body>

</html>
