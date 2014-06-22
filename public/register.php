<?php

    // configuration
    require("../includes/config.php");

    // if form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        // TODO
        //validate form data
        if (empty($_POST["username"]) || empty($_POST["password"]))
        {
            // ID-10-T error
            apologize("Error ID-10t: missing username and/or password.");
        }
        else if ($_POST["password"] != $_POST["confirmation"])
        {
            // ID-10-T error
            apologize("Error ID-10t: password & confirmation don't match.");
        }
        else
        {
            // register user in database
            $result = query("INSERT INTO users (username, hash, cash) VALUES(?, ?, 10000.00)", $_POST["username"], crypt($_POST["password"]));
            
            if ($result === false)
            {
                apologize("Crap. Something went wrong ... duplicate username?");
            }
            else
            {
                $rows = query("SELECT LAST_INSERT_ID() AS id");
                $id = $rows[0]["id"];
                $_SESSION = $id;
                redirect("index.php");
            }
        }
    }
    else
    {
        // else render form
        render("register_form.php", ["title" => "Register"]);
    }

?>
