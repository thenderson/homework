<?php
    require("../includes/config.php");
	error_log("project view ".$_POST('p');
	
	if ($_SERVER["REQUEST_METHOD"] == "POST")
    {		
        // validate submission
        if (!empty($_POST['p'])) render('commitments_form.html');
?>