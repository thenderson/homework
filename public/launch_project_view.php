<?php
    require("../includes/config.php");
	
	if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['p']))
    {		
		$p_num = $_POST['p'];
		render('commitments_form.html');
	}
	else echo 'fail';