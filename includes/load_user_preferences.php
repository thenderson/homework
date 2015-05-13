<?php
    // configuration
    require_once('../includes/config.php');       
	
	// Load user preferences
	$q = "SELECT pref_show_id, pref_show_imp, pref_show_mag, pref_show_timeline FROM users WHERE user_id = :user";
	
	$stmt = $comm_db->prepare($q);
	
	if (!$stmt) {
		trigger_error('Statement failed: ' . $stmt->error, E_USER_ERROR);
		exit;
	}
	try {
		$stmt->bindParam(':user', $_SESSION['id'], PDO::PARAM_INT);
		$stmt->execute();		
		$prefs = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	catch(PDOException $e) {
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	}
	
	echo json_encode($prefs[0]);