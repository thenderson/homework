<?php
      
require_once('config.php');         
                      
// Get POST data
$unique_id = strip_tags($_POST['uniqueid']);
$project_number = strip_tags($_POST['projectnumber']);

if ($unique_id == -1) //insert new commitment matching the project number of the one clicked on
{
	$q="INSERT INTO commitments (project_number, status) VALUES (?, 'OPEN')";
}
else // duplicate a commitment
{
	$q='CREATE TEMPORARY TABLE temp_table ENGINE=MEMORY
		SELECT * FROM commitments WHERE unique_id=?;
		UPDATE temp_table SET unique_id=NULL;
		INSERT INTO commitments SELECT * FROM temp_table;
		DROP TABLE temp_table';
}

$stmt = $comm_db->prepare($q);

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error';
	exit;
}

try
{
	if ($unique_id == -1)
	{
		$stmt->bindParam(1, $project_number, PDO::PARAM_STR);
	}
	else
	{
		$stmt->bindParam(1, $unique_id, PDO::PARAM_INT);
	}
	$stmt->execute();
}             

catch(PDOException $e) 
{
	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	echo 'error';
	exit;
}      

echo 'ok';