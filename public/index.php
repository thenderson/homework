<?php

    // configuration
    require("../includes/config.php");
    error_log("index!!");
    // if form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
    //TODO adjustable filters for projects, planning horizon, status & type
    //TODO ability to update status, description, etc.
    //TODO ability to sort & filter
	error_log("form was posted!");
        $planning_horizon = 14;
        $commitments = query("SELECT * FROM commitments WHERE due_by <= DATE_ADD(CURDATE(),INTERVAL ? DAY) ORDER BY promisor, project, due_by", $planning_horizon);
        render("commitments_form.php", ["commitments" => $commitments]);
    }
    else // show default configuration
    {
    //TODO save user preferences for setup; show only user's projects
	error_log("showing default config");
        $planning_horizon = 28;
        $commitments = query("SELECT * FROM commitments WHERE due_by <= DATE_ADD(CURDATE(),INTERVAL ? DAY) ORDER BY project, due_by, promisor", $planning_horizon);
        render("commitments_form.php", ["commitments" => $commitments]);
    }
?>
