<?php

    // configuration
    require("../includes/config.php"); 

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
        $rows = $comm_db->query("SELECT * FROM users WHERE username = {$_POST["username"]}");
		
		pr("SELECT * FROM users WHERE username = {$_POST["username"]}");
		pr($rows, "var_dump");
		pr($comm_db, 'var_dump');
		pr($comm_db->host_info, 'var_dump');
		pr($comm_db->get_connection_stats, 'var_dump');
		
        // if we found user, check password
        if (mysqli_num_rows($rows) == 1)
        {
            // first (and only) row
            $row = $rows->mysqli_fetch_assoc();

            // compare hash of user's input against hash that's in database
            if (crypt($_POST["password"],$row["hash"]) == $row["hash"])
            {
                // remember that user's now logged in by storing user's ID in session
                $_SESSION["id"] = $row["user_id"];
				//error_log("successful login; session id=".$_SESSION["id"]);

                // Successful login ... redirect to commitment view
                redirect("/commgr/public/index.php");
            }
        }
		else
		{
			apologize("Multiple users exist?");
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
