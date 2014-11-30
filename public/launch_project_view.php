<?php
    require("../includes/config.php");
	
	if ($_SERVER["REQUEST_METHOD"] == "POST")
    {		
        // validate submission
        if (!empty($_POST['p'])) 
		{
			error_log("project view ".$_POST['p']);
			render('commitments_form.html', [project: $_POST['p']]);
			exit;
		}
	}
echo 'fail';