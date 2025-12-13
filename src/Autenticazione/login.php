<?php
session_start();
include "db_connect.php";


$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $pdo->prepare("CALL AUTENTICAZIONE_UTENTE(:email, :password, @status, @user_type, @nome)");
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindParam(':password', $password, PDO::PARAM_STR);
$stmt->execute();


$stmt = $pdo->query("SELECT @status AS status, @user_type AS user_type, @nome AS nome");
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$success = $result['status'];
$user_type = $result['user_type'];
$nome = $result['nome'];


if ($success == 2) {

    echo "Accesso eseguito!";
    $_SESSION['email'] = $email;
    $_SESSION['nome'] = $nome;
    $_SESSION['tipo_utente'] = $user_type;

    if ($user_type == 'studente') {
        echo "Accesso eseguito";
        header("Location: home_studente.php");
        exit();
    } elseif ($user_type == 'docente') {
        echo "Accesso eseguito";
        header("Location: home_docente.php");
        exit();
    } else {
        echo "Accesso non riconosciuto";
        header('Location: ../index.php?error=Utente non riconosciuto');
        exit();
    }


} else if($success == 1) {
    echo "Password non corretta";
    header('Location: ../index.php?error=Password errata');
    exit();
}else{
    echo "Utente non esistente";
    header('Location: ../index.php?error=Utente non registrato');
    exit();
}
