<?php

    // configuration
    require_once('../includes/config.php');     
	require_once('../includes/EditableGrid.php');     

	/*	RETRIEVE COMMITMENTS */
	$planning_horizon = 14; // days
	
	$stmt = $comm_db->prepare("
		SELECT unique_id, project_number, task_id, description, requester, promiser, due_by, requested_on, status, type, metric 
		FROM commitments 
		WHERE due_by <= DATE_ADD(CURDATE(), INTERVAL ? DAY) 
		ORDER BY project_number, promiser, due_by");
	
	if (!$stmt)
	{
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try 
	{
		$stmt->bindParam(1, $planning_horizon, PDO::PARAM_INT);
		$stmt->execute();		
		$commitments = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) 
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
	
	/*	RETRIEVE USER_ID & NAMES */ //move this to config & pass into this script?
	$user_res = $comm_db->query("SELECT user_id, name FROM users ORDER BY email ASC");
	if (!$user_res) trigger_error('Statement failed : ' . E_USER_ERROR);
	else 
	{
		$users = $user_res->fetchAll(PDO::FETCH_ASSOC);
		foreach ($users as $row) $username_lookup[$row["user_id"]] = $row["name"];
	}
	
	/*	RETRIEVE PROJECT NUMBERS & PROJECT SHORTNAMES */ //move this to config & pass into this script?
	$proj_res = $comm_db->query("SELECT project_number, project_shortname FROM projects");
	if (!$proj_res) trigger_error('Statement failed : ' . E_USER_ERROR);
	else 
	{
		$rows = $proj_res->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) $projects[$row["project_number"]] = $row["project_shortname"];
	}

	// create grid
	$grid = new EditableGrid();
	
	//declare grid columns
	$grid->addColumn('unique_id', 'U_ID #', 'integer', NULL, false);
	$grid->addColumn('project_number', 'PROJECT #', 'double');
	$grid->addColumn('task_id', 'ID #', 'string', NULL, false);
	$grid->addColumn('description', 'COMMITMENT', 'string');
	$grid->addColumn('promiser','PROMISER','string', $username_lookup);
	$grid->addColumn('requester','REQUESTER','string', $username_lookup);
	$grid->addColumn('due_by','DUE BY','date');
	$grid->addColumn('status','STATUS','string');
	$grid->addColumn('metric','METRIC','string', NULL, false);
	$grid->addColumn('action', 'Action', 'html', NULL, false, 'id');  
	
	//render grid
	$grid->renderXML($commitments);