<div class="container">
	<form class="form-horizontal" action="login.php" method="post" role="form">
		<fieldset>
			<legend><h5>l o g i n</h5></legend>
			<div class="form-group">
				<label for="username" class="col-sm-2 control-label">username</label>
				<div class="col-sm-4">
					<input autofocus class="form-control" name="username" id="username" placeholder="username" type="text"/>
				</div>
			</div>
			<div class="form-group"> 
				<label for="password" class="col-sm-2 control-label">password</label>
				<div class="col-sm-4">
					<input class="form-control" name="password" id="password" placeholder="password" type="password"/>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-default">go</button>
				</div>
			</div>
		</fieldset>
	</form>
	<br>
	<div>
		or <a href="register.php">register</a>
	</div>
</div> <!--close container-->