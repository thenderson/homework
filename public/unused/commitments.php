<?php

    require("../includes/config.php");
        
    $commitments = queryx("SELECT * FROM commitments WHERE VALUES (project, due_by)", $project, $planning_horizon);
    
    render("commitments_form.php", ["commitments" => $commitments]);
?>
