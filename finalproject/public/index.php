<?php

    // configuration
    require("../includes/config.php");
    
    // if form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
    //TODO adjustable filters for projects, planning horizon, status & type
    //TODO ability to update status, description, etc.
    //TODO ability to sort & filter
        $planning_horizon = new DateTime() + 14 * 24 * 60 * 60;
        $commitments = query("SELECT * FROM commitments WHERE due_by <= ? ORDER BY promisor ASC project ASC due_date ASC)", $planning_horizon);
        render("commitments.php", ["commitments" => $commitments]);
    }
    else // show default configuration
    {
    //TODO save user preferences for setup; show only user's projects
        $planning_horizon = new DateTime() + 30 * 24 * 60 * 60;
        $commitments = query("SELECT * FROM commitments WHERE due_by <= ? ORDER BY project ASC due_date ASC promisor ASC)", $planning_horizon);
        render("commitments.php", ["commitments" => $commitments]);
    }
?>
