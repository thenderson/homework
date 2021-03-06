<?php
    // configuration
    require_once('../includes/config.php');     
	require_once('../includes/EditableGrid.php');     

	// Get POST data
	$planning_horizon = strip_tags($_POST['horizon']);
	$showClosed = strip_tags($_POST['showClosed']);
	$project_number = strip_tags($_POST['p']);
	$showDeferred = $planning_horizon == 'all' ? 'true' : 'false';
	
	/*  COMPOSE QUERY */
	$q = "SELECT unique_id, task_id, description, magnitude, requester, promiser, due_by, requested_on as visual,
		priority_h, status, IF(status IN ('O', '?', 'D', 'NA', 'V?', 'V*'),0,1) as is_closed FROM commitments";
	
	if ($planning_horizon == 'all') {
		$q = $q . " WHERE project_number = :projnum";
	}
	else {
		$q = $q . " WHERE due_by <= DATE_ADD(CURDATE(), INTERVAL :horizon DAY) AND project_number = :projnum";
	}
	
	if ($showClosed == 'false') {
		if ($showDeferred == 'true') $q = $q . " and status IN ('O', '?', 'D', 'NA', 'V?', 'V*')";
		else $q = $q . " and status IN ('O', '?', 'NA', 'V?', 'V*')";
	}
	else { // showClosed = true
		if ($showDeferred == 'false') $q = $q . " and status != 'D'";
	} // if showClosed & showDeferred == true, then we're showing everything, so no filter needed.
	
	$q = $q . ' ORDER BY due_by, project_number';
	
	/*	RETRIEVE COMMITMENTS */	
	$stmt = $comm_db->prepare($q);
	
	if (!$stmt) {
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try {
		if ($planning_horizon != 'all') $stmt->bindParam(':horizon', $planning_horizon, PDO::PARAM_INT);
		$stmt->bindParam(':projnum', $project_number, PDO::PARAM_STR);
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
	else {
		$users = $user_res->fetchAll(PDO::FETCH_ASSOC);
		foreach ($users as $row) $username_lookup[$row['user_id']] = $row['name'];
	}

	// create grid
	$grid = new EditableGrid();
	
	//declare grid columns TODO add columns for due/overdue, variance
	$grid->addColumn('unique_id', 'U_ID #', 'integer', NULL, false);
	$grid->addColumn('is_closed', '?', 'integer', NULL, false);
	$grid->addColumn('task_id', 'ID #', 'double(,2,dot,comma,)', NULL, false);
	$grid->addColumn('status','STAT','string');
	$grid->addColumn('actions', 'DO', 'html', NULL, false, 'id');
	$grid->addColumn('priority_h', '!','boolean');
	$grid->addColumn('description', 'COMMITMENT', 'string');
	$grid->addColumn('magnitude', 'MAG', 'double(,,dot,coma,)');
	$grid->addColumn('requester','REQUESTER','string', $username_lookup);
	$grid->addColumn('promiser','PROMISER','string', $username_lookup);
	$grid->addColumn('due_by','DUE BY','date');
	$grid->addColumn('visual', 'TIMELINE', 'date', NULL, false);

	//render grid
	$grid->renderXML($commitments);