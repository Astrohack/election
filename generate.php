<?php
    // generate votes
    $conn = mysqli_connect("localhost", "root", "", "election");
    $r = $conn->query("SELECT id FROM candidates");
    $candidates = array();
    $values = "";
    while($e = mysqli_fetch_row($r)){
        $candidates[] = $e[0];
    }
    $r = $conn->query("SELECT id FROM centers");
    while($e = mysqli_fetch_row($r)){
        foreach($candidates as $id){
            $valid = rand(500, 2000);
            $invalid = rand(10, 50);
            $values.= "($e[0],$id, $valid, $invalid),";
        }
    }
    $values = rtrim($values, ",");
    $conn->query("INSERT INTO votes VALUES $values");
    $conn->close();
    
?>