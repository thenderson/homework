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
	$user_res = $comm_db->query("SELECT email, name FROM users ORDER BY email ASC");
	if (!$user_res) trigger_error('Statement failed : ' . E_USER_ERROR);
	else 
	{
		$rows = $user_res->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row)
		{
			$users[$row["email"]] = $row["name"];
		}
	}
	
	/*	RETRIEVE PROJECT NUMBERS & PROJECT SHORTNAMES */ //move this to config & pass into this script?
	$proj_res = $comm_db->query("SELECT project_number, project_shortname FROM projects");
	if (!$proj_res) trigger_error('Statement failed : ' . E_USER_ERROR);
	else 
	{
		$rows = $proj_res->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row)
		{
			$projects[$row["email"]] = $row["name"];
		}
	}
	
//	dbug('$commitments', $commitments);
	dbug('$users', $users); 
	dbug('$projects', $projects); 
	echo dbug('print');

//	render("commitments_form.php", ["commitments"=>$commitments, "users" => $users, "projects" => $projects]);