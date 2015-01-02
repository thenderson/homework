<?php

    // configuration
    require('../config.php'); 

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

        // query database for user
        $rows = $comm_db->query("SELECT * FROM users WHERE username = '{$_POST["username"]}'");
		//** wow! this appears ripe for SQL injection attack.
		
        // if we found user, check password
        if ($rows->rowCount() == 1)
        {
            // first (and only) row
            $row = $rows->fetch(PDO::FETCH_ASSOC);

            // compare hash of user's input against hash that's in database
            if (crypt($_POST["password"],$row["hash"]) == $row["hash"])
            {
                // remember that user is now logged in by storing user's ID in session
                $_SESSION["id"] = $row["user_id"];
				$_SESSION["username"] = $row["username"];

                // Successful login ... redirect to commitment view
                redirect(ROOT."/index.php");
            }
        }
		else
		{
			apologize("?");
		}

        // else apologize
        apologize("Invalid username and/or password.");
    }
    else
    {
        // else render form
        render("login_form.php");
    }

?>
