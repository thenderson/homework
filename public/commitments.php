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
	
	if ($stmt)
	{
		$stmt->bind_param("i", $planning_horizon);
		$stmt->execute();
		$stmt->bind_result($project_numbers, $task_ids, $descriptions, $requesters, $promisers, $due_bys, $requested_ons, $statuses, $types, $metrics);
		$stmt->close();
	}
	else
	{
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	}
	
	/*	RETRIEVE USERNAMES & EMAIL ADDRESSES */ //move this to config & pass into this script?
	$users_res = $comm_db->query("SELECT email, name FROM users ORDER BY email ASC");
	
	if (!$users_res)
	{
		trigger_error('Statement failed : ' . E_USER_ERROR);
	}
	else
	{
		$users = $users_res->fetch_assoc();
	}
	
	/*	RETRIEVE PROJECT NUMBERS & PROJECT SHORTNAMES */ //move this to config & pass into this script?
	$projects_res = $comm_db->query("SELECT project_number, project_shortname FROM projects");
	
	if (!$projects_res)
	{
		trigger_error('Statement failed : ' . E_USER_ERROR);
	}
	else
	{
		$projects = $projects_res->fetch_assoc();
	}

		debug_view($users_res);
		debug_view($users);
		debug_view($projects_res);
		debug_view($projects);
//		dbug($comm_db, $users, $projects); 
//		echo dbug('print');

//	render("commitments_form.php", ["project_numbers"=>$project_numbers, "task_ids"=>$task_ids, "descriptions"=>$descriptions, "requesters"=>$requesters, "promisers"=>$promisers, "due_bys"=>$due_bys, "requested_ons"=>$requested_ons, "statuses"=>$statuses, "types"=>$types, "metrics"=>$metrics, "users" => $users, "projects" => $projects]);