<?php

    // configuration
    require_once('../includes/config.php');

	/*	RETRIEVE USERNAME LIST */
	$stmt = $comm_db->prepare('
		SELECT project_number, project_name
		FROM projects
		NATURAL JOIN users_projects
		WHERE user_name = :user
		ORDER BY project_number');
	
	if (!$stmt)
	{
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try 
	{
		$stmt->bindParam(':user', $_SESSION['id'], PDO::PARAM_STR);
		$stmt->execute();		
		$user_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) 
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
		
	echo json_encode($user_projects);