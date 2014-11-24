<?php

    // configuration
    require_once('../includes/config.php');     
	require_once('../includes/EditableGrid.php');     

	/*	RETRIEVE PROJECT LIST */
	
	$stmt = $comm_db->prepare("
		SELECT project_number, project_name
		FROM projects
		NATURAL JOIN users_projects
		WHERE user_id = ?
		ORDER BY project_number");
	
	if (!$stmt)
	{
		trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	
	try 
	{
		$stmt->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
		$stmt->execute();		
		$commitments = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} 
	catch(PDOException $e) 
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}

	// create grid
	$grid = new EditableGrid();
	
	//declare grid columns TODO add columns for due/overdue, variance
	$grid->addColumn('project_number', 'PROJECT #', 'string', NULL, false);
	$grid->addColumn('project_name', 'PROJECT NAME', 'string', NULL, false);

	//render grid
	$grid->renderXML($commitments);