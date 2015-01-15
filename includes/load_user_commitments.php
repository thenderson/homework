<?php

    // configuration
    require_once('../includes/config.php');     
	require_once('../includes/EditableGrid.php');     
	
	// Get POST data
	$planning_horizon = strip_tags($_POST['horizon']); // days
	$showComplete = strip_tags($_POST['showComplete']);
	
	error_log('horizon:'.$planning_horizon.', showHidden:'.$showHidden);

	/*  COMPOSE QUERY */
	
	$q = "SELECT unique_id, project_number, task_id, description, requester, promiser, DATE_FORMAT(due_by,'%m/%d/%Y') as due_by, priority_h, status FROM commitments";
	
	if ($planning_horizon == 'all') $q = $q . " WHERE promiser = :promiser";
	else $q = $q . " WHERE due_by <= DATE_ADD(CURDATE(), INTERVAL ? DAY) and promiser = :promiser";
	
	if ($showComplete == false) $q = $q . " and status IN ('O', '?', 'D', 'NA')";
	
	$q = $q . ' ORDER BY due_by, project_number';
	
	error_log('query: '.$q);
	
	/*	RETRIEVE COMMITMENTS */
	
	$stmt = $comm_db->prepare("
		SELECT unique_id, project_number, task_id, description, requester, promiser, DATE_FORMAT(due_by,'%m/%d/%Y') as due_by, priority_h, status
		FROM commitments 
		WHERE due_by <= DATE_ADD(CURDATE(), INTERVAL ? DAY) and promiser = ? and status != 
		ORDER BY due_by, project_number");
	
	if (!$stmt)
	{
		trigger_error('Statement failed: ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try 
	{
		$stmt->bindParam(1, $planning_horizon, PDO::PARAM_INT);
		$stmt->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
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
		foreach ($commitments as &$commitment) $commitment['project_shortname'] = $projects[$commitment['project_number']];
	}

	// create grid
	$grid = new EditableGrid();
	
	//declare grid columns TODO add columns for due/overdue, variance
	$grid->addColumn('unique_id', 'U_ID #', 'integer', NULL, false);
	$grid->addColumn('project_number', 'PROJECT #', 'string');
	$grid->addColumn('project_shortname', 'PROJECT NAME', 'string');
	$grid->addColumn('task_id', 'ID #', 'string', NULL, false);
	$grid->addColumn('description', 'COMMITMENT', 'string');
	//$grid->addColumn('promiser','PROMISER','string', $username_lookup);
	$grid->addColumn('requester','REQUESTER','string', $username_lookup);
	$grid->addColumn('due_by','DUE BY','date');
	$grid->addColumn('priority_h', '!','boolean');
	$grid->addColumn('completed', '?', 'boolean');
	$grid->addColumn('status','STAT','string');
	$grid->addColumn('actions', 'DO', 'html', NULL, false, 'id');

	//render grid
	$grid->renderXML($commitments);