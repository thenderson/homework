<?php

    // configuration
    require("../includes/config.php");

    // if form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        // query database for user
        $rows = query("SELECT hash FROM users WHERE id = ?", $_SESSION["id"]);
        
        // first (and only) row
        $hash = $rows[0]["hash"];
            
        //validate form data
        if (empty($_POST["old_password"]) || empty($_POST["new_password"]) || empty($_POST["new_password_confirm"]))
        {
            // ID-10-T error
            apologize("Missing one or more required fields.");
        }
        else if ($_POST["new_password"] != $_POST["new_password_confirm"])
        {
            // ID-10-T error
            apologize("Password & confirmation don't match.");
        }
        else if (crypt($_POST["old_password"], $hash) != $hash)
        {
            // ID-10-T error
            apologize("Incorrect password.");
        }
        else
        {
            // update password
            $result = query("UPDATE users SET hash = ? WHERE id = ?", crypt($_POST["new_password"]), $_SESSION["id"]);
            
            if ($result === false)
            {
                apologize("Crap. Something went wrong ... odd.");
            }
            redirect('/');
        }
    }
    else
    {
        // else render form
        render("change_password_form.php");
    }

?>
