
	<form class="form-horizontal" action="register.php" method="post" role="form">
		<fieldset>
			<legend><h5>r e g i s t e r</h5></legend>
			<div class="form-group">
				<label for="name" class="col-sm-1 control-label">name</label>
				<div class="col-sm-4">
					<input autofocus class="form-control" name="name" id="name" placeholder="Firstname Lastname" type="text"/>
				</div>
			</div>
			<div class="form-group">
				<label for="email" class="col-sm-1 control-label">email</label>
				<div class="col-sm-4">
					<input class="form-control" name="email" id="email" placeholder="email address" type="text"/>
				</div>
			</div>
			<div class="form-group">
				<label for="company" class="col-sm-1 control-label">company</label>
				<div class="col-sm-4">
					<input class="form-control" name="company" id="company" placeholder="company" type="text"/>
				</div>
			</div>
			<div class="form-group">
				<label for="username" class="col-sm-1 control-label">username</label>
				<div class="col-sm-4">
					<input class="form-control" name="username" id="username" placeholder="username" type="text"/>
				</div>
			</div>
			<div class="form-group"> 
				<label for="password" class="col-sm-1 control-label">password</label>
				<div class="col-sm-4">
					<input class="form-control" name="password" id="password" placeholder="password" type="password"/>
				</div>
			</div>
			<div class="form-group"> 
				<label for="password-conf" class="col-sm-1 control-label">password</label>
				<div class="col-sm-4">
					<input class="form-control" name="password-conf" id="password-conf" placeholder="confirm password" type="password"/>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-1 col-sm-11">
					<button type="submit" class="btn btn-default">go</button>
				</div>
			</div>
		</fieldset>
	</form>
	<br>
	<div class="row">
		<div class="col-sm-11 col-sm-offset-1">
			or <a href="login.php">login</a> if you already have an account
		</div>
	</div>