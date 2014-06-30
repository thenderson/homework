<?php

    // configuration
    require("../includes/config.php");

    //if form was submitted
    // if ($_SERVER["REQUEST_METHOD"] == "POST")
    // {
    //TODO adjustable filters for projects, planning horizon, status & type
    //TODO ability to update status, description, etc.
    //TODO ability to sort & filter

        render("commitments_form.php");
    // }
    // else // else ... huh. Is there an else?
    // {
		// apologize("hmmm. something odd in index.php.");
    // }
?>
