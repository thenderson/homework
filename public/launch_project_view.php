<?php
    require('../includes/config.php');
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['p']))
    {		
		echo 'ok';
		render('commitments_form.html', ['project' => $_POST['p']]);
		error_log('back from the render');
	}
	else echo 'fail';
	error_log("this shouldnt happen");