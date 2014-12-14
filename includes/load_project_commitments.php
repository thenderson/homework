<?php

    // configuration
    require_once('../includes/config.php');     
	require_once('../includes/EditableGrid.php');     

	// Get POST data
	$planning_horizon = strip_tags($_POST['horizon']); // days
	$project_number = strip_tags($_POST['p']);
	
	/*	RETRIEVE COMMITMENTS */	
	$stmt = $comm_db->prepare("
		SELECT unique_id, task_id, description, requester, promiser, DATE_FORMAT(due_by,'%m/%d/%Y') as due_by_f, DATE_FORMAT(requested_on, '%m/%d/%Y') as requested_on_f, status, type, metric 
		FROM commitments 
		WHERE due_by <= DATE_ADD(CURDATE(), INTERVAL ? DAY) AND project_number = ?
		ORDER BY due_by, promiser");
	
	if (!$stmt)
	{
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try 
	{
		$stmt->bindParam(1, $planning_horizon, PDO::PARAM_INT);
		$stmt->bindParam(2, $project_number, PDO::PARAM_STR);
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
		foreach ($users as $row) $username_lookup[$row['user_id']] = $row['name'];
	}

	// create grid
	$grid = new EditableGrid();
	
	//declare grid columns TODO add columns for due/overdue, variance
	$grid->addColumn('unique_id', 'U_ID #', 'integer', NULL, false);
	$grid->addColumn('project_number', 'PROJECT #', 'string');
	$grid->addColumn('task_id', 'ID #', 'string', NULL, false);
	$grid->addColumn('description', 'COMMITMENT', 'string');
	$grid->addColumn('promiser','PROMISER','string', $username_lookup);
	$grid->addColumn('requester','REQUESTER','string', $username_lookup);
	$grid->addColumn('due_by_f','DUE BY','date');
	$grid->addColumn('status','STATUS','string');
	$grid->addColumn('metric','METRIC','string', NULL, false);
	$grid->addColumn('actions', 'DO', 'html', NULL, false, 'id');

	// ob_start();
	// echo($commitments[0]['due_by_f']);
	// $contents = ob_get_contents();
	// ob_end_clean();
	// error_log($contents);

	//render grid
	$grid->renderXML($commitments);