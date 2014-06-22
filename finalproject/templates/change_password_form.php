<form action="change_password.php" method="post">
    <fieldset>
        <div class="form-group">
            <input class="form-control" name="old_password" placeholder="old password" type="password"/>
        </div>
        <div class="form-group">
            <input class="form-control" name="new_password" placeholder="new p'word" type="password"/>
        </div>

        <div class="form-group">
            <input class="form-control" name="new_password_confirm" placeholder="confirm" type="password"/>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-default">Change Password</button>
        </div>
    </fieldset>
</form>
<div>
    or <a href="login.php">log in</a>
</div>
