<?php

    require("../includes/config.php");
        
    $commitments = query("SELECT * FROM commitments WHERE VALUES (project, due_by)", $project, $planning_horizon);
    
    render("commitments.php", ["commitmens" => $commitments]);
?>
