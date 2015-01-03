<?php 
	require '../includes/config.php';
	require 'header.html'; 
?>

	<body>
		<div class="container-fluid">
			<div class="col-sm-12 nopadding" id="table_title">
				<h3><strong>PROJECT COMMITMENTS &nbsp;&nbsp;</strong><i class='fa fa-spinner fa-spin'></i></h3>
			</div>
		</div>
		<div class="container-fluid">
			<table id="bar-top">
				<tr>
					<td class="col-sm-3 filter nopadding">
						<div class="input-group">
							<div class="input-group-addon"><i class='fa fa-filter'></i></div>
							<input class="form-control input-sm" type="text" id="filter_all" name="filter"/>
						</div>
					</td>
					<td class="col-sm-3 add_commitment">
						<button id="new_com_btn" type="button" class="btn btn-default btn-sm">New Commitment</button>
					</td>
					<td class="col-sm-6 paginator" id="paginator"></td>
				</tr>
			</table>
		</div>
		
		<div class="container-fluid">
			<div id="project_commitments">
				<br><br><br><br><br><br><i class='fa fa-spinner fa-spin fa-3x text-center'></i>
			</div>
		</div>
			
		<script src="../public/js/editablegrid.js"></script>
		<script src="../public/js/editablegrid_renderers.js" ></script>
		<script src="../public/js/editablegrid_editors.js" ></script>
		<script src="../public/js/editablegrid_validators.js" ></script>
		<script src="../public/js/editablegrid_utils.js" ></script>
		<!--<script src="../public/js/editablegrid_charts.js" ></script>-->
		<script src="../public/js/reliable.js" ></script>
							
		<div id="delete-confirm" class="dialog" title="Delete commitment?" data-role="dialog">   
			<div data-role="content" id="text">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><h4>Deleting commitments cannot be undone. Are you sure?</h4></p>
			</div>
		</div>
		
		<div id="add-commitment" class="dialog" title="Record New Commitment" data-role="dialog">
			<form class='form-horizontal' id='comm_form'>
			
				<div class="control-group">
					<label class="control-label" for="inp-comm">Description</label>
					<div class="controls">
						<textarea class="form-control input-sm" id="inp-comm" name="description" rows="4"></textarea>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="inp-req">Requester</label>
					<div class="controls">
						<select class="input-sm" id="inp-req" name="requester" style="width: 200px">
						</select>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="inp-prom">Promiser</label>
					<div class="controls">
						<select class="input-sm" id="inp-prom" name="promiser" style="width: 200px">
						</select>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="inp-due">Date Due</label>
					<div class="controls">
						<input type="text" class="input-sm" id="inp-due" name="date_due" style="width: 200px">
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="inp-stat">Status</label>
					<div class="controls">
						<select class="input-sm" id="inp-stat" name="status" style="width: 200px">
						</select>
					</div>
				</div>
			</form>			
		</div>
	</body>
	
<?php require 'footer.php'; ?>

	<script type="text/javascript">
		jQuery.datepicker.setDefaults({dateFormat:$.datepicker.W3C});   
		
		project_commitments = new CommitmentGrid();
		project_commitments.name = "project_commitments";
		
		window.onload = function() {
			pnum = getparam('project');
			
			$.ajax({
				url: '../includes/load_project_name.php',
				type: 'POST',
				dataType: 'text',
				data: { p: pnum },
				success: function (response) {
					$('#table_title').html("<h3><strong>PROJECT COMMITMENTS: "+response+"</strong> | #"+pnum+"</h3>");},
				error: function(XMLHttpRequest, textStatus, exception) { 
					alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception);},
				async: true
			});
			
			$.ajax({
				url: '../includes/load_project_usernames.php',
				type: 'POST',
				dataType: 'JSON',
				data: { p: pnum },
				success: function (proj_users) {
					populate_select_obj("#inp-req", proj_users);
					populate_select_obj("#inp-prom", proj_users);
					},
				error: function(XMLHttpRequest, textStatus, exception) { 
					alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception);},
				async: true
			});
			
			$("#filter_all").keyup(function() { project_commitments.grid.filter($(this).val(), [1,2,3,4,5,6]); });

			$("#delete-confirm").dialog({
				resizable: false,
				autoOpen: false,
				height:180,
				modal: true,
				buttons: {
					"Delete": function() {
						$(this).dialog("close");
						project_commitments.DeleteRow($(this).data('id'));
					},
					Cancel: function() {
						$(this).dialog("close");
					}
				}
			});
			
			$("#add-commitment").dialog({
				resizable: true,
				autoOpen: false,
				height:450,
				width:300,
				modal: true,
				buttons: {
					"Submit": function() {
						var data = $('#comm_form').serializeArray();
						$(this).dialog("close");
						console.debug(data);
						project_commitments.AddRow([data[0],data[1],data[2],data[3],data[4]]);
					},
					"Submit+": function() {
						$(this).dialog("close");
						//project_commitments.AddRow(???);
						$(this).dialog("open");
					},
					Cancel: function() {
						$(this).dialog("close");
					}
				}
			});
			
			$("#new_com_btn").on("click", function() {
				$("#add-commitment")
					.dialog({show: { effect: "puff", duration: 300 }})
					.dialog("open"); 
			});
			
			$("#inp-due").datepicker({
				dateFormat: $.datepicker.W3C,
				numberOfMonths: 2,
				gotoCurrent: true,
				showAnim: 'puff'
			});
			
			$.ajax({
				url: '../includes/load_project_commitments.php',
				type: 'POST',
				dataType: "text",
				data: {
					horizon: 30,
					p: getparam('project')
				},
				success: function (response) 
				{
					project_commitments.grid.loadXMLFromString(response); //synchronous function
					project_commitments.grid.tableLoaded();
				},
				error: function(XMLHttpRequest, textStatus, exception) 
				{ 
					alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception); 
				},
				async: true
			});
			
			$("#inp-req").selectmenu();
			$("#inp-prom").selectmenu();
			$("#inp-stat").selectmenu();
			
			populate_select("#inp-stat", statuses);
		}; 
		
		// activate tooltip
		$(document).ready(function() {
			$('.editablegrid-task_id').tooltip({
				content: '<span>Unique ID for the request. Not editable.</span>'});
			
			$('th .editablegrid-description a').tooltip({
				content: '<span>Descibe what work products shall be handed-off to whom in \
				what form, via what method & at what level of completion.</span>'});
			
			$('th .editablegrid-promiser a').tooltip({
				content: '<span>Select the person who is promising the work described.</span>'});
			
			$('th .editablegrid-requester a').tooltip({
				content: '<span>Select the person who is asking \
				for the work described. If the promiser = the requester, this will be considered a \
				personal workplan item and may not appear on the team workplan.</span>'});
				
			$('th .editablegrid-due_by a').tooltip({
				content: '<span>Select the date when the work product described be handed-off.</span>'});
			
			$('th .editablegrid-status a').tooltip({
				content: '<span>Open: Commitment is not complete. \n\
				Closed: The requester is satisfied that the promiser has met the commitment described. \n\
				In Progress: Work on the request has begun but is incomplete. \n\
				Deferred: The request is set aside indefinitely. \n\
				Unknown: The promiser and/or requester are not available to status the commitment.</span>'});
				
			$('th .editablegrid-metric a').tooltip({
				content: '<span>Overdue, complete, anticipated, improvised.</span>'});
			
			$('th .editablegrid-actions a').tooltip({
				content: 'Delete or duplicate. Note: \
				Only delete a commitment if it is truly messed-up. Otherwise, its status and/or variance should be entered.'});
				
			$('#add_commitment').tooltip({
				content: 'Record a new commitment'});	
		});
	</script>