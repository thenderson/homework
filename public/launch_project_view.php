<?php
    require("../includes/config.php");
	
	if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['p']))
    {		
		echo 'ok';
		render('commitments_formb.html', ["project" => $_POST['p']]);
	}
	else echo 'fail';