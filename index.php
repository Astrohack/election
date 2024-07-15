<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Wybory prezydenckie</title>
    <style>

    </style> 
</head>
<body sheme="light">
    

    <?php
        session_start();
        $authorized = isset($_SESSION["auth"]);
        $dataPoints = array();
        $centerData = array();
        $candidates = array();
        $conn = mysqli_connect("localhost", "root", "", "election");

        $authorized_center_name = $authorized ? $_SESSION["auth"]:"";

        $r = $conn->query("SELECT name, id, avatar, description FROM candidates ");
        while ($e = mysqli_fetch_row($r)) $candidates[$e[1]] = array("name" => "$e[0]", "id"=>$e[1], "avatar" => "$e[2]", "desc" => "$e[3]");

        $r = mysqli_fetch_row($conn->query("SELECT SUM(valid_votes), SUM(invalid_votes) FROM votes"));
        $votes_total = $r[0] + $r[1];
        $valid_votes_total = $r[0];

        $chartDataWrapper = [];
        $r = $conn->query("SELECT cn.name, c.name, v.valid_votes, candidate_id FROM votes as v JOIN centers cn ON cn.id=v.center_id JOIN candidates c ON c.id=v.candidate_id");
        while ($e = mysqli_fetch_row($r)) {
            if (empty($chartDataWrapper[$e[0]])) $chartDataWrapper[$e[0]] = [];
            array_push($chartDataWrapper[$e[0]], array("label" => $e[1], "y"=> $e[2]));
        }
        foreach($chartDataWrapper as $name=>$payload){
            array_push($centerData, array("type" => "column", "indexLabel"=>"{y}", "name" => "$name", "showInLegend" => true, "yValueFormatString" => "#,##0", "dataPoints" => $payload));
        }
    ?>



    <div id="site-content">
        <div id="section-top">
            <div id="logo">
                <div>
                    <img src="avatars/logo.png" alt="logo">
                    <h1>Wybory prezydenckie</h1>
                    <h1><?php echo $votes_total;?> głosów w bazie</h1>
                </div>
                <div>
                    <?php echo $authorized?<<<END
                            <h2>Komisja: $authorized_center_name</h2>
                            <input type="button" class='btn' onClick="window.location.href = 'logout.php'" value="Wyloguj">
                        END:"
                            <input type=\"button\" class='btn' onClick=\"window.location.href = 'login.php'\" value=\"Zaloguj\">
                        ";?>
                </div>
            </div>
        </div>
        <div id="section-right">
            <div id="candidates-container">
                <div id="candidates-wraper" class="card first-card">
                    <h3>Kandydaci</h3>                    
                    <div id="candidates">
                        <table id="candidates">
                            <tr>
                                <td>Pozycja</td><td>Imię i nazwisko</td><td>Ważne głosy</td><td>Unieważnione głosy</td><td>Ważne głosy w %</td>
                            </tr>
                            <?php
                                $position = 1;
                                $r = $conn->query("SELECT c.id, c.name, SUM(v.valid_votes) as f , SUM(v.invalid_votes) FROM votes as v JOIN candidates c ON c.id=v.candidate_id GROUP BY c.id ORDER BY f DESC");
                                $winner_id = mysqli_fetch_row($r)[0];
                                mysqli_data_seek($r, 0);
                                while ($e = mysqli_fetch_row($r)) {
                                    $id = $e[0];
                                    $name = $e[1];
                                    $valid = $e[2];
                                    $invalid = $e[3];
                                    $percent = round($valid  / $valid_votes_total * 100,2);
                                    echo "<tr onClick=\"changePreview($id)\"><td>$position</td><td>$name</td><td>$valid</td><td>$invalid</td><td>$percent % </td></tr>";
                                    array_push($dataPoints, array("label" =>$name, "votes"=> $valid, "y" => $percent));
                                    $position++;
                                }
                            ?>
                        </table>
                    </div>
                </div>
                <div id="candidates-preview" class="card second-card">
                    <div id="candidates-preview_container">
                        <img id="candidate-preview-avatar" src="" alt="zdjęcie profilowe">
                        <h4 id="candidates-preview-description"></h4>
                        <h3 id="candidates-preview-name"></h3>
                    </div>
                </div>
                <form id="candidates-vote-container" method="post" action="vote.php" class="card third-card">
                    <h2><?php echo $authorized?"Dodaj głosy":"Oddaj swój głos";?></h2>
                    <?php echo $authorized?<<<END
                        <h3>Ilość głosów do dodania</h3>
                        <input type="number" name="amount" class="text-input">
                    END:"";?>
                    <h3>Wybierz kandydata</h3>
                    <select name="candidate_id" class="vote-selection" id="candidate-selection">
                        <?php
                            $r = $conn->query("SELECT name, id FROM candidates ORDER BY name asc ");
                            while($e = mysqli_fetch_row($r)) {
                                echo "<option value=\"$e[1]\"  onClick=\"changePreview($e[1])\">$e[0]</option>";
                            }
                        ?>
                    </select>
                    
                    <?php 
                        if(!$authorized) {
                            echo '<h3>Wybierz ośrodek</h3> 
                                        <select name="center_id" class="vote-selection">';
                                $r = $conn->query("SELECT name, id FROM centers ORDER BY name DESC");
                                while($e = mysqli_fetch_row($r)) {
                                    echo "<option value=\"$e[1]\">$e[0]</option>";
                                }
                            echo '</select>';
                            
                        }
                        else{
                            echo <<<END
                                <h3>Typ głosu</h3>
                                <div>
                                    <input type="radio"  name="type" checked value="valid_votes">
                                    <span>Ważny</span>
                                    <input type="radio"  name="type" value="invalid_votes">
                                    <span>Nieważny</span>
                                </div> 
                                END;
                        }
                    ?>
                   
                    <input id='candidate-vote-button' type='submit' value='Zagłosuj'>
                </form>
            </div>  
            <div id="charts__container">
                <div id="charts__general-wraper" class="card fourth-card">
                    <div id="graph-type_button-container">
                        <input type="button" class="btn-gr" id="pie" value="Kołowy" onclick="drawChart('pie', 'Wykres kołowy')">
                        <input type="button" class="btn-gr" id="column"  value="Kolumnowy" onclick="drawChart('column', 'Wykres kolumnowy')">
                        <input type="button" class="btn-gr" id="bar"  value="Słupkowy" onclick="drawChart('bar', 'Wykres słupkowy')">
                    </div>
                    <div id="chartContainer" style="height: 370px; width: 600px;"></div>
                </div>

                <div id="charts__detailed-container" class="card fifth-card">                    
                    <div id="stackedChartContainer" style="height: 370px; width: 95%;"></div>
                </div>
            </div>
          
            
        </div>
    </div>


    <script src="charts.js"></script>


    <script>
        const ctx = document.getElementById('chart');
        var current_selected_main_graph_id=  "pie";
        const candidates = <?php echo json_encode($candidates, JSON_NUMERIC_CHECK); ?>

        window.onload = function() {
            drawChart(current_selected_main_graph_id, "Wykres kołowy")
            drawComplex()
            changePreview(<?php echo $winner_id; ?>);
        }

        function changePreview(candidateId){
            document.getElementById("candidate-preview-avatar").setAttribute("src", "avatars/"+candidates[candidateId].avatar)
            document.getElementById("candidates-preview-description").innerHTML = candidates[candidateId].desc
            document.getElementById("candidates-preview-name").innerHTML = candidates[candidateId].name
            document.getElementById("candidate-selection").value = candidateId
        }

        function drawChart(type, desc) {
            var chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                title: {
                    text: desc
                },
                theme: "light2",
                subtitles: [{
                    text: "Luty 2023"
                }],
                backgroundColor: "transparent",
                axisY: {
                    logarithmic:  true,
                },
                data: [{
                    type: type,
                    yValueFormatString: "#,##0.00\"%\"",
                    indexLabel: "{label} - {votes} ({y})",
                    dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
                }]
            });
            chart.render();
            document.querySelector(".canvasjs-chart-credit").remove();
            document.getElementById(type).setAttribute("status", "selected")
            if(current_selected_main_graph_id!=type) document.getElementById(current_selected_main_graph_id).setAttribute("status", null)
            current_selected_main_graph_id = type
        }



        function drawComplex(){
            var chart = new CanvasJS.Chart("stackedChartContainer", {
                title: {
                    text: "Wykres słupkowy z uwzględnieniem poszczególnych ośrodków"
                },
                theme: "light2",
                backgroundColor: "transparent",
                animationEnabled: true,
                axisY: {
                    logarithmic:  true,
                },
                toolTip:{
                    shared: true,
                    reversed: true
                },
                data: <?php echo json_encode($centerData, JSON_NUMERIC_CHECK); ?>
            });
        
            chart.render();
            document.querySelector(".canvasjs-chart-credit").remove(); 
        }
        
    </script>
</body>
</html>