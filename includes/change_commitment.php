<?php

    // configuration
    require("../includes/config.php");

    // if form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {   
        //validate form data
        if (empty($_POST["u_id"]) || empty($_POST["field"]) || empty($_POST["new_value"]))
		{
			echo 'missing data';
			exit;
		}

		$stmt = $comm_db->prepare("UPDATE commitments WHERE unique_id = ? SET ? = ?");
		
		if (!$stmt)
		{
			echo "statement failed: ".$stmt->error, E_USER_ERROR;
			//trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
			exit;
		}
		
		try 
		{
			$stmt->bindParam(1, $_POST["u_id"], PDO::PARAM_INT);
			$stmt->bindParam(2, $_POST["field"], PDO::PARAM_STR);
			$stmt->bindParam(3, $_POST["new_value"], PDO::PARAM_STR);
			$stmt->execute();		
			//$commitments = $stmt->fetchAll(PDO::FETCH_ASSOC);
		} 
		catch(PDOException $e) 
		{
			echo 'Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
			//trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
		}
		
		echo 'success';
	}
?>
