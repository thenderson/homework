<?php

    // configuration
    require('../includes/config.php'); 

    // if form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
	//TODO implement stronger encryption.
		
        // validate submission
        if (empty($_POST["username"]))
        {
            apologize("You must provide your username.");
        }
        else if (empty($_POST["password"]))
        {
            apologize("You must provide your password.");
        }

		$username = strip_tags($_POST['username']);
		
        // query database for user
		$stmt = $comm_db->prepare("SELECT * FROM users WHERE username = ?");

		if (!$stmt)
		{
			trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
			echo 'error';
			exit;
		}

		try
		{
			$stmt->bindParam(1, $username, PDO::PARAM_STR);	
			$stmt->execute();
		}             

		catch(PDOException $e) 
		{
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
			echo 'error';
			exit;
		}      
		
        // if we found user, check password
        if ($stmt->rowCount() == 1)
        {
            // first (and only) row
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // compare hash of user's input against hash that's in database
            if (crypt($_POST["password"],$row["hash"]) == $row["hash"])
            {
                // remember that user is now logged in by storing user's ID in session
                $_SESSION["id"] = $row["user_id"];
				$_SESSION["username"] = $row["username"];

                // Successful login ... redirect to commitment view
                redirect('../index.php');
            }
			else apologize("Invalid username and/or password.");
        }
		else //user not found or multiple matching users (somehow)
		{
			if ($stmt->rowCount() == 0) apologize('Login failed: Invalid username.');
			else apologize('Login failed: Non-unique username?');
		}
    }
    else
    {
        // else render form
        render('login_form.php');
    }
?>
