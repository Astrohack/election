<?php
        if(isset($_POST["candidate_id"])){
            session_start();
            $candidate_id = $_POST["candidate_id"];
            $authorized = isset($_SESSION["auth"]);
            $center_id = $authorized?$_SESSION["center_id"]:$_POST["center_id"];
            $conn = mysqli_connect("localhost", "root", "", "election");
            if(isset($_POST["amount"]) && $authorized) {
                $amount = $_POST["amount"];
                $type = isset($_POST["type"])? $_POST["type"]: "valid_votes";
                $conn->query("UPDATE votes SET $type=$type+$amount WHERE candidate_id=$candidate_id AND center_id=$center_id" );
            }else{
                $conn->query("UPDATE votes SET valid_votes=valid_votes+1 WHERE candidate_id=$candidate_id AND center_id=$center_id" );
            }
            $conn->close();
            header("Location: index.php");
        }
    ?>