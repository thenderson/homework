<?php

    // configuration
    require_once('../includes/config.php');

	/*	RETRIEVE USERNAME LIST */
	$stmt = $comm_db->prepare('
		SELECT project_number, project_name
		FROM projects
		NATURAL JOIN users_projects
		WHERE user_id = :user
		ORDER BY project_number');
	
	if (!$stmt) {
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try {
		$stmt->bindParam(':user', $_SESSION['id'], PDO::PARAM_STR);
		$stmt->execute();		
		$user_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) {
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
	
	// load performance metrics for user projects
	$pnums = "";
	foreach ($user_projects as $proj) $pnums = $pnums."'".$proj['project_number']."',";
	$pnums = rtrim($pnums, ',');

	$q = "SELECT project_number, date, PPC, PTA, PTI FROM `project_metrics` 
	WHERE project_number IN ($pnums) AND date BETWEEN date_sub(curdate(), INTERVAL 6 WEEK) and CURDATE()";

	$stmt = $comm_db->prepare($q);
	
	if (!$stmt) {
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try {
		$stmt->execute();		
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) {
		trigger_error('Wrong SQL: ' . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
	
	$last_monday = new DateTime(strtotime('last Monday'));
	
error_log('last monday was '.$last_monday);

	foreach ($rows as $row) {
		$weeknum = date_diff($last_monday, strtotime($row['date']))->format('%r%a') / 7;
error_log($weeknum);
		$project_metrics[$row['project_number']]['PPC'][$weeknum] = $row['PPC'];
		$project_metrics[$row['project_number']]['PTA'][$weeknum] = $row['PTA'];
		$project_metrics[$row['project_number']]['PTI'][$weeknum] = $row['PTI'];
	}
	
	echo json_encode(array('user_projects'=>$user_projects, 'project_metrics'=>$project_metrics));