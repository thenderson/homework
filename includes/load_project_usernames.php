<?php

    // configuration
    require_once('../includes/config.php');

	// Get POST data
	$project_number = strip_tags($_POST['p']);	

	/*	RETRIEVE USERNAME LIST */
	$stmt = $comm_db->prepare("
		SELECT user_id, name
		FROM users
		NATURAL JOIN users_projects
		WHERE project_number = ?
		ORDER BY name");
	
	if (!$stmt) {
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try {
		$stmt->bindParam(1, $project_number, PDO::PARAM_STR);
		$stmt->execute();		
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) {
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
	
	$users = [];
	foreach ($result as $res) $users[$res['user_id'] => $res['name']];
	
	echo json_encode($users);