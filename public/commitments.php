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
		SELECT unique_id, project_number, task_id, description, requester, promiser, DATE_FORMAT(due_by,'%d/%m/%y'), DATE_FORMAT(requested_on, '%d/%m/%y'), status, type, metric 
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
	
//	dbug('$commitments', $commitments);
//	dbug('$users', $users); 
//	dbug('$projects', $projects); 
//	echo dbug('print');

	render("commitments_form.php", ["commitments"=>$commitments, "users" => $users, "username_lookup"=>$username_lookup, "projects" => $projects]);

	//redirect("../templates/commitments_form.php");
	//header('location: /commgr/templates/commitments_form.php');
	//header('Content-Type: text/html; charset=UTF-8');
	//header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	//header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	//require('../templates/commitments_form.php');
	exit;