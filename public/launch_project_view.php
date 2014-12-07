<?php
    require('../includes/config.php');
	
	if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['p']))
    {		
		http_response_code(302);
		//header($location);
		//header('Content-Type: text/html; charset=UTF-8');
		//header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
		//header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		render('commitments_form.html', ['project' => $_GET['p']]);
		error_log('ok...back from the render');
	}
	else 
	{
		echo 'fail';
		error_log("this shouldn't happen");
	}
	
	//consider changing request to GET instead of POST.