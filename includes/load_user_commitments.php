<?php

    // configuration
    require_once('../includes/config.php');     
	require_once('../includes/EditableGrid.php');     
	
	// Get POST data
	$planning_horizon = strip_tags($_POST['horizon']); // days
	$showClosed = strip_tags($_POST['showClosed']);
	$p_or_r = strip_tags($_POST['p_or_r']); // load promises or requests

	/*  COMPOSE QUERY */
	$q = "SELECT unique_id, project_number, task_id, description, requester, promiser, due_by, 
		priority_h, status, IF(status IN ('O', '?', 'D', 'NA', 'V?'),0,1) as is_closed FROM commitments";
	
	if ($planning_horizon == 'all') {
		if ($p_or_r == 'promises') $q = $q . ' WHERE promiser = :user';
		else $q = $q . ' WHERE requester = :user';
	}
	else {
		if ($p_or_r == 'promises') $q = $q . " WHERE due_by <= DATE_ADD(CURDATE(), INTERVAL :horizon DAY) and promiser = :user";
		else $q = $q . " WHERE due_by <= DATE_ADD(CURDATE(), INTERVAL :horizon DAY) and requester = :user";
	}
	
	if ($showClosed == 'false') $q = $q . " and status IN ('O', '?', 'D', 'NA', 'V?')";
	
	$q = $q . ' ORDER BY due_by, project_number';
	
	/*	RETRIEVE COMMITMENTS */
	$stmt = $comm_db->prepare($q);
	
	if (!$stmt) {
		trigger_error('Statement failed: ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try {
		if ($planning_horizon != 'all') $stmt->bindParam(':horizon', $planning_horizon, PDO::PARAM_INT);
		$stmt->bindParam(':user', $_SESSION['id'], PDO::PARAM_INT);
		$stmt->execute();		
		$commitments = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) {
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
	
	/*	RETRIEVE USER_ID & NAMES */ //move this to config & pass into this script?
	$user_res = $comm_db->query("SELECT user_id, name FROM users ORDER BY email ASC");
	if (!$user_res) trigger_error('Statement failed : ' . E_USER_ERROR);
	else {
		$users = $user_res->fetchAll(PDO::FETCH_ASSOC);
		foreach ($users as $row) $username_lookup[$row["user_id"]] = $row["name"];
	}
	
	/*	RETRIEVE PROJECT NUMBERS & PROJECT SHORTNAMES */ //move this to config & pass into this script?
	$proj_res = $comm_db->query("SELECT project_number, project_shortname FROM projects");
	if (!$proj_res) trigger_error('Statement failed : ' . E_USER_ERROR);
	else {
		$rows = $proj_res->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) $projects[$row["project_number"]] = $row["project_shortname"];
		foreach ($commitments as $com) {
			//$com['project_shortname'] = $projects[$com['project_number']];
			error_log($com['project_shortname'].' -- '.$projects[$com['project_number']]);
		}
	}

	// create grid
	$grid = new EditableGrid();
	
	//declare grid columns
	$grid->addColumn('unique_id', 'U_ID #', 'integer', NULL, false);
	$grid->addColumn('is_closed', '?', 'integer', NULL, false);
	$grid->addColumn('project_number', 'PROJECT #', 'string', NULL, false);
	$grid->addColumn('project_shortname', 'PROJECT NAME', 'string', NULL, false);
	$grid->addColumn('task_id', 'ID #', 'double(,2,dot,comma,)', NULL, false);
	$grid->addColumn('description', 'COMMITMENT', 'string');
	
	if ($p_or_r == 'promises') $grid->addColumn('requester','REQUESTER','string', $username_lookup);
	else $grid->addColumn('promiser','PROMISER','string', $username_lookup);

	$grid->addColumn('due_by','DUE BY','date');
	$grid->addColumn('priority_h', '!','boolean');
	$grid->addColumn('status','STAT','string');
	$grid->addColumn('actions', 'DO', 'html', NULL, false, 'id');

	//render grid
	$grid->renderXML($commitments);