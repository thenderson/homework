<?php

    // configuration
    require_once('../includes/config.php');     
	require_once('../includes/EditableGrid.php');     

	/*	RETRIEVE PROJECT LIST */
	
	$stmt = $comm_db->prepare("
		SELECT project_number as project_number_2, project_name
		FROM projects
		NATURAL JOIN users_projects
		WHERE user_id = ?
		ORDER BY project_number_2");
		
	$stmt2 = $comm_db->prepare("
		SELECT project_number as project_number_2, project_name
		FROM projects
		NATURAL JOIN users_projects
		WHERE user_id != ?
		ORDER BY project_number_2");
	
	if (!$stmt || !$stmt2)
	{
		trigger_error('Statement failed : ' . $stmt->error, $stmt2->error, E_USER_ERROR);
		exit;
	}
	
	try 
	{
		$stmt->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
		$stmt->execute();		
		$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$stmt2->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
		$stmt2->execute();		
		$commitments = $commitments.append($stmt2->fetchAll(PDO::FETCH_ASSOC));
	} 
	catch(PDOException $e) 
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
		
	// create grid
	$grid = new EditableGrid();
	
	//declare grid columns TODO add columns for PPC & TA
	$grid->addColumn('project_number_2', 'PROJECT #', 'string', NULL, false);
	$grid->addColumn('project_name', 'PROJECT NAME', 'string', NULL, false);

	//render grid
	$grid->renderXML($commitments);