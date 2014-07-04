<?php

    // configuration
    require("../includes/config.php");

    //TODO adjustable filters for projects, planning horizon, status & type
    //TODO ability to update status, description, etc.
    //TODO ability to sort & filter

        $planning_horizon = 14;
        $commitments = queryx("SELECT * FROM commitments WHERE due_by <= DATE_ADD(CURDATE(),INTERVAL ? DAY) ORDER BY promiser, project_number, due_by", $planning_horizon);
        render("commitments_form.php", ["commitments" => $commitments]);
?>
