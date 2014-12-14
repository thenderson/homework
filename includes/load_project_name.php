<?php

    // configuration
    require_once('../includes/config.php');     

	// Get POST data
	$project_number = strip_tags($_POST['p']);
	error_log('Project number:'.$project_number);
	
	/*	RETRIEVE PROJECT NAME */ //move this to config & pass into this script?
	$stmt = $comm_db->prepare('SELECT project_name FROM projects WHERE project_number = ?');
	
	error_log('stmt: '.var_dump($stmt));
	
	if (!$stmt)
	{
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try 
	{
		$stmt->bindParam(1, $project_number, PDO::PARAM_STR);
		$stmt->execute();		
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) 
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
	
	error_log($result);

	echo 'Project Name Goes Here';