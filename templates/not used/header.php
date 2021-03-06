<!DOCTYPE html public>

<html>
    <head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="reliable promising, a web-hosted commitment management system for individuals and teams.">
		<meta name="keywords" content="reliability, promising, lean, last planner system, commitment, project management">
		<meta name="author" content="Todd Henderson">
		
		<link href="/commgr/favicon.ico" rel="shortcut icon" type="image/x-icon"/>
		
		<!-- load bootstrap CSS & fontawesome -->
        <link href="/commgr/public/css/bootstrap.min.css" rel="stylesheet"/>
		<!-- <link rel="stylesheet" href="css/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css" media="screen">
		<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
		<link rel="stylesheet" href="css/responsive.css" type="text/css" media="screen">
		-->
		
		<!-- include javascript files for the EditableGrid library -->
		<script src="/commgr/public/js/editablegrid.js"></script>
		<script src="/commgr/public/js/editablegrid_renderers.js" ></script>
		<script src="/commgr/public/js/editablegrid_editors.js" ></script>
		<script src="/commgr/public/js/editablegrid_validators.js" ></script>
		<script src="/commgr/public/js/editablegrid_utils.js" ></script>
		<script src="/commgr/public/js/editablegrid_charts.js" ></script>
		<link rel="stylesheet" href="/commgr/public/css/editablegrid.css" type="text/css" media="screen">

		<!-- include javascript & css files for jQuery, needed for the datepicker and autocomplete extensions -->
		<script src="/commgr/public/js/jquery-1.11.1.min.js" ></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
		<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />

		<!-- include javascript and css files for the autocomplete extension -->
		<script src="/commgr/public/extensions/autocomplete/autocomplete.js" ></script>
		<link rel="stylesheet" href="/commgr/public/extensions/autocomplete/autocomplete.css" type="text/css" media="screen">

		<link href="/commgr/public/css/reliable_stylesheet.css" rel="stylesheet"/>
		<script src="/commgr/public/js/bootstrap.min.js"></script>

        <title>reliable promising</title>
    </head>

    <body>		
		<header class="navbar navbar-default" id="top" role="navigation">
			<div class="container">
				<div class="navbar-header margin-8px">
					<button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a href="../" class="navbar-brand">reliable promising</a>
				</div>
				<nav class="collapse navbar-collapse margin-8px" id="collapse" role="navigation">
					<ul class="nav navbar-nav">
						<li></li>
						<li><a href="index.php">home</a></li>
						<li class="disabled"><a href="settings.php"><span class="glyphicon glyphicon-wrench"></span></a></li>
						<li><a href="logout.php">logout</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<?php
						if (empty($_POST["username"]))
						{ ?>
							<li class="navbar-text navbar-right">guest</li>
						<?php
						}
						else
						{ ?>
							<li class="navbar-text navbar-right"><?=$_POST["username"]?></li>
						<?php
						} ?>
					</ul>
				</nav>
			</div>
		</header>