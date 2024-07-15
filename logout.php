<?php
    session_start();
    unset($_SESSION["auth"]);
    unset($_SESSION["center_id"]);
    header("Location: index.php");
?>