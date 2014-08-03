<!DOCTYPE html>

<html>
    <head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="reliablepromising, a web-hosted reliable promising system for individuals and teams.">
		<meta name="keywords" content="reliable promising, reliability, promising, lean, last planner system, commitment, project management">
		<meta name="author" content="Todd Henderson">
		<title>reliable promising</title>
		
		<!-- include default bootsrap css -->
		<link href="/commgr/public/css/bootstrap.min.css" rel="stylesheet"/>
				
		<!-- include javascript and css files for the EditableGrid library -->
		<script src="/commgr/public/js/editablegrid.js"></script>
		<!-- [DO NOT DEPLOY] --> <script src="/commgr/public/js/editablegrid_renderers.js" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="/commgr/public/js/editablegrid_editors.js" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="/commgr/public/js/editablegrid_validators.js" ></script>
		<!-- [DO NOT DEPLOY] --> <script src="/commgr/public/js/editablegrid_utils.js" ></script>
		<!-- [DO NOT DEPLOY] <script src="../../editablegrid_charts.js" ></script>  -->
		
		<link rel="stylesheet" href="/commgr/public/css/editablegrid.css" type="text/css" media="screen">

		<!-- include javascript and css files for jQuery, needed for the datepicker and autocomplete extensions -->
		<script src="/commgr/public/extensions/jquery/jquery-1.6.4.min.js" ></script>
		<script src="/commgr/public/extensions/jquery/jquery-ui-1.8.16.custom.min.js" ></script>
		<link rel="stylesheet" href="/commgr/public/extensions/jquery/jquery-ui-1.8.16.custom.css" type="text/css" media="screen">
		
		<!-- include javascript and css files for the autocomplete extension -->
		<script src="/commgr/public/extensions/autocomplete/autocomplete.js" ></script>
		<link rel="stylesheet" href="/commgr/public/extensions/autocomplete/autocomplete.css" type="text/css" media="screen">
		
		<!-- include custom js functions and css -->
		<script src="/commgr/public/js/reliable.js"></script>
		<link href="/commgr/public/css/reliable_stylesheet.css" rel="stylesheet"/>

		<script type="text/javascript">
			window.onload = function() { 
				// you can use "datasource/demo.php" if you have PHP installed, to get live data from the demo.csv file
				editableGrid.onloadJSON("x"); 
			}; 
		</script>
    </head>

    <body>
		<script src="/commgr/public/js/bootstrap.min.js"></script>
		
		<!-- add navbar -->
		<?php require("nav.html");?> 

		<div class="container">

		<!-- TODO
			adjustable column widths
			collapse projects
			date picker
			editable v. not
			sort, filter
			individual view (across projects)
		-->
		</div>
	</body>