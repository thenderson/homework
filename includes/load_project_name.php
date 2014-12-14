<?php

    // configuration
    require_once('../includes/config.php');     
	require_once('../includes/EditableGrid.php');     

	// Get POST data
	$project_number = strip_tags($_POST['p']);
	
	/*	RETRIEVE PROJECT NAME */ //move this to config & pass into this script?
	$stmt = $comm_db->prepare('SELECT project_name FROM projects WHERE project_number = ?');
	
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

	var_dump($result);
	echo 'Project Name Goes Here';