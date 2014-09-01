<?php
      
require_once('config.php');         
                      
// Get POST data
$unique_id = strip_tags($_POST['uniqueId']);

$stmt = $comm_db->prepare('INSERT INTO `commitments` ( project_number, task_id, description, requester, promiser, due_by, status, type, metric )
							SELECT project_number, task_id, description, requester, promiser, due_by, "OPEN", type, NULL
							FROM `commitments` WHERE `unique_id`=?');
							
//						'CREATE TEMPORARY TABLE temp_table ENGINE=MEMORY;
//						SELECT * FROM commitments WHERE unique_id=?;
//						UPDATE temp_table SET status="OPEN", metric=NULL;
//						INSERT INTO commitments SELECT * FROM temp_table;
//						DROP TABLE temp_table');

if (!$stmt)
{
	trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
	echo 'error - statement failed';
	exit;
}

try 
{
	$stmt->bindParam(1, $unique_id, PDO::PARAM_INT);
	$stmt->execute();
}             

catch(PDOException $e) 
{
	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
	echo 'error - query failed';
	exit;
}      

echo 'ok';