<?php
    require("../includes/config.php");
	
	if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['p']))
    {		
		$p_num = $_POST['p'];
		echo 'ok';
		render('commitments_form.html', ["project" => $p_num]);
	}
	else echo 'fail';