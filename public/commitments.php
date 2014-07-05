<?php

    // configuration
    require("../includes/config.php");

    //TODO adjustable filters for projects, planning horizon, status & type
    //TODO ability to update status, description, etc.
    //TODO ability to sort & filter

        $planning_horizon = 14;
        $commitments = $comm_db->query("SELECT * FROM commitments WHERE due_by <= DATE_ADD(CURDATE(),INTERVAL {$planning_horizon} DAY ORDER BY promiser, project_number, due_by");
        pr($commitments);
		render("commitments_form.php", ["commitments" => $commitments]);
?>
