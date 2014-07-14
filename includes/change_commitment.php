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

		switch ($_POST['field'])
		{
			case "description":
				$qry = "UPDATE commitments SET description = ? WHERE unique_id = ?;";
				break;
			case "requester":
				$qry = "UPDATE commitments SET requester = ? WHERE unique_id = ?;";
				break;
			case "promiser":
				$qry = "UPDATE commitments SET promiser = ? WHERE unique_id = ?;";
				break;
			case "status":
				$qry = "UPDATE commitments SET status = ? WHERE unique_id = ?;";
				break;
			case "date_due":
				$qry = "UPDATE commitments SET date_due = ? WHERE unique_id = ?;";
				break;
			default:
				echo 'fail: read-only value';
				exit;
		}
		
		try
		{
			$stmt = $comm_db->prepare($qry);
		}
		
		catch(PDOException $e)
		{
			echo "fail:\n". $e->getMessage(), E_USER_ERROR;
			exit;
		}
		
		try 
		{
			$stmt->bindParam(1, $_POST["new_value"], PDO::PARAM_STR);
			$stmt->bindParam(2, $_POST["u_id"], PDO::PARAM_INT);
			$res = $stmt->execute();		
		} 
		catch(PDOException $e) 
		{
			echo 'fail:\n' . $e->getMessage(), E_USER_ERROR;
			exit;
		}
		
		if (!$res)
		{
			echo 'fail: something went wrong. :-/';
			exit;
		}
		
		echo 'success';
	}
?>
