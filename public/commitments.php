<?php

    // configuration
    require("../includes/config.php");

    //TODO adjustable filters for projects, planning horizon, status & type
    //TODO ability to update status, description, etc.
    //TODO ability to sort & filter
	//TODO input validation

	/*	RETRIEVE COMMITMENTS */
	$planning_horizon = 14; // days
	
	$stmt = $comm_db->prepare("
		SELECT project_number, task_id, description, requester, promiser, due_by, requested_on, status, type, metric 
		FROM commitments 
		WHERE due_by <= DATE_ADD(CURDATE(), INTERVAL ? DAY) 
		ORDER BY promiser, project_number, due_by");
	
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
	
	/*	RETRIEVE USERNAMES & EMAIL ADDRESSES */ //move this to config & pass into this script?
	$users = $comm_db->query("SELECT email, name FROM users ORDER BY email ASC", PDO::FETCH_ASSOC);
	
	if (!$users_res)
	{
		trigger_error('Statement failed : ' . E_USER_ERROR);
	}
	else
	{
//		for ($users = array (); $row = $users_res->fetch_assoc(); $users[] = $row); //makes associative array
//		for ($users = array (); $row = $users_res->fetch_assoc(); $users[array_shift($row)] = $row); //makes array keyed to first field
	}
	
	/*	RETRIEVE PROJECT NUMBERS & PROJECT SHORTNAMES */ //move this to config & pass into this script?
	$projects = $comm_db->query("SELECT project_number, project_shortname FROM projects", PDO::FETCH_ASSOC);
	
	if (!$projects_res)
	{
		trigger_error('Statement failed : ' . E_USER_ERROR);
	}
	else
	{
//		for ($projects = array (); $row = $projects_res->fetch_assoc(); $projects[] = $row);  //makes associative array
//		for ($projects = array (); $row = $projects_res->fetch_assoc(); $projects[array_shift($row)] = $row);  //makes array keyed to first field
	}
	
	dbug('$commitments', $commitments);
	dbug('$users', $users); 
	dbug('$projects', $projects); 
	echo dbug('print');

//	render("commitments_form.php", ["project_numbers"=>$project_numbers, "task_ids"=>$task_ids, "descriptions"=>$descriptions, "requesters"=>$requesters, "promisers"=>$promisers, "due_bys"=>$due_bys, "requested_ons"=>$requested_ons, "statuses"=>$statuses, "types"=>$types, "metrics"=>$metrics, "users" => $users, "projects" => $projects]);