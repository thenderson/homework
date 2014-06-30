<!--
 * This file makes use of EditableGrid. http://editablegrid.net
 * (c) 2011 Webismymind SPRL; http://editablegrid.net/license
-->

<div class="container">
	<div id="wrap">
		<h4>C O M M I T M E N T S</h4>
		<div id="message"></div> <!-- Feedback message zone -->
		<div id="tablecontent"></div> <!-- Grid contents -->
		<div id="paginator"></div> <!-- Paginator control -->
	</div>  
	
	<script src="js/editablegrid-2.0.1.js"></script>   
	<script src="js/jquery-1.7.2.min.js" ></script>
	<script src="js/demo.js" ></script>

	<script type="text/javascript">
		window.onload = function() { 
			datagrid = new DatabaseGrid();
		}; 
	</script>
</div>