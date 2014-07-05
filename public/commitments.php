<?php

    // configuration
    require("../includes/config.php");

    //TODO adjustable filters for projects, planning horizon, status & type
    //TODO ability to update status, description, etc.
    //TODO ability to sort & filter
	//TODO input validation

	/*	RETRIEVE COMMITMENTS */
	$planning_horizon = 14; // days
	$stmt = $comm_db->prepare("SELECT * FROM commitments WHERE due_by <= DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY promiser, project_number, due_by");
	
	if ($stmt)
	{
		$stmt->bind_param("i", $planning_horizon);
		$stmt->execute();
		$stmt->bind_result($commitments);
		$stmt->close();
	}
	else
	{
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	}
	
	/*	RETRIEVE USERNAMES & EMAIL ADDRESSES */
	$stmt = $comm_db->prepare("SELECT email, usernamme FROM users ORDER BY email ASC");
	
	if ($stmt)
	{
//		$stmt->bind_param("i", $planning_horizon); bind project number when that become functional.
		$stmt->execute();
		$stmt->bind_result($users);
		$stmt->close();
	}
	else
	{
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	}
	
	/*	RETRIEVE PROJECT NUMBERS & PROJECT SHORTNAMES */
	$project_list="*";
	$stmt = $comm_db->prepare("SELECT project_shortname FROM projects WHERE project_number = ?");
	
	if ($stmt)
	{
		$stmt->bind_param("s", $project_list); //bind project number when that become functional.
		$stmt->execute();
		$stmt->bind_result($projects);
		$stmt->close();
	}
	else
	{
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	}
	
//      $commitments = $comm_db->query("SELECT * FROM commitments WHERE due_by <= DATE_ADD(CURDATE(), INTERVAL $planning_horizon DAY) ORDER BY promiser, project_number, due_by");
//		$users = $comm_db->query("SELECT email, usernamme FROM users ORDER BY email ASC");
//      var_dump($commitments);
//		var_dump($comm_db);

	render("commitments_form.php", ["commitments" => $commitments, "users" => $users, "projects" => $projects]);