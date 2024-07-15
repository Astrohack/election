<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>
<body>
    <div id="section-top">
        <div id="logo">
            <div>
                <img src="avatars/logo.png" alt="logo">
                <h1>Wybory prezydenckie</h1>
            </div>
            <div>
                <input type="button" class='btn' onClick="window.location.href = 'index.php'" value="Wróć na stronę główną">
            </div>
        </div>
    </div>
    <?php
        $error = isset($_GET["error"])?$_GET["error"]:0;
        session_start();
        if(isset($_SESSION["auth"])) {
            header("Location: index.php");
        }
        else if(isset($_POST["passwd"])){
            $password = $_POST["passwd"];
            $center_id = intval(substr($password,-1));
            if(!$center_id){
                header("Location: login.php?error=Błędne hasło");
                exit();
            }
            $conn = mysqli_connect("localhost","root","","election");
            $r = $conn->query("SELECT name, id, passwd FROM centers WHERE id=$center_id");
            $conn->close();
            if(($e = mysqli_fetch_row($r)) && password_verify($password, $e[2])) {
                $_SESSION["auth"] = $e[0];
                $_SESSION["center_id"] = $e[1];
                header("Location: index.php");
                exit();
            }
            header("Location: login.php?error=Błędne hasło");
        }        
    ?>
    <div id="site__container">
        <form method="post" id="login__wraper">
            <?php echo $error? "<h3 style='color: red'>$error</h3>":""?>
            <h3 class="form-label">Podaj hasło</h3>
            <input type="password" name="passwd">
            <input type="submit" value="Zaloguj">
        </form>
    </div>
</body>
</html>