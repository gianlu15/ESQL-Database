<!DOCTYPE html>
<html>

<head>
    <title>LOGIN</title>
    <link rel="stylesheet" type="text/css" href="Style/style.css">
</head>

<body>
    <div class="wrapper">
        <form action="Autenticazione/login.php" method="post">
            <h2>LOGIN</h2>
            <?php if (isset($_GET['error'])) { ?>
                <p class="error"><?php echo $_GET['error']; ?></p>

            <?php } ?>
            <label>Email</label>
            <input type="text" name="email" class="input-box" placeholder="Email"><br>
            <label>Password</label>
            <input type="password" name="password" class="input-box" placeholder="Password"><br>
            <button type="submit">Login</button>


            <div>
                <p>Non hai un account?</p>
                <p>Registrati come <a href="Autenticazione/registrazione_docente.php"><u>Docente</u></a> o <a
                        href="Autenticazione/registrazione_studente.php"><u>Studente</u></a>.</p>

            </div>
    </div>

    </form>

    <img src="Style/logo.png" />

</body>

</html>