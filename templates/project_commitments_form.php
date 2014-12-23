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
			
		<script src="/commgr/public/js/editablegrid.js"></script>
		<script src="/commgr/public/js/editablegrid_renderers.js" ></script>
		<script src="/commgr/public/js/editablegrid_editors.js" ></script>
		<script src="/commgr/public/js/editablegrid_validators.js" ></script>
		<script src="/commgr/public/js/editablegrid_utils.js" ></script>
		<script src="/commgr/public/js/editablegrid_charts.js" ></script>
		<script src="/commgr/public/js/reliable.js" ></script>
							
		<div id="delete-confirm" title="Delete commitment?" data-role="dialog">   
			<div data-role="content" id="text">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><h4>Deleting commitments cannot be undone. Are you sure?</h4></p>
			</div>
		</div>
		
		<div id="add-commitment" title="Record New Commitment" data-role="dialog">
			<form role="form-horizontal">
				<div class="form-group">
					<label for="inp-req" class="col-sm-2 control-label">Requester</label>
					<div class="col-sm-10">
						<select class="form-control" id="inp-req">
							<option>1</option>
							<option>2</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="inp-prom" class="col-sm-2 control-label">Promiser</label>
					<div class="col-sm-10">
						<select class="form-control" id="inp-prom">
							<option>1</option>
							<option>2</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="inp-comm" class="col-sm-2 control-label">Commitment</label>
					<div class="col-sm-10">
						<textarea class="form-control" id="inp-comm" rows="3"></textarea>
					</div>
				</div>
				<div class="form-group">
					<label for="inp-due" class="col-sm-2 control-label">Date Due</label>
					<div class="col-sm-10">
						<input type="date" class="form-control" id="inp-due"></input>
					</div>
				</div>
				<div class="form-group">
					<label for="inp-stat" class="col-sm-2 control-label">Status</label>
					<div class="col-sm-10">
						<select class="form-control" id="inp-stat">
							<option>1</option>
							<option>2</option>
						</select>
					</div>
				</div>
			</form>

		</div>
		
	</body>
	
<?php require 'footer.php'; ?>

	<script type="text/javascript">
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
				height:400,
				width:400,
				modal: true,
				effect: highlight,
				buttons: {
					"Submit": function() {
						$(this).dialog("close");
						//project_commitments.AddRow(???);
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
				console.log("click!");
				$("#add-commitment").dialog("open"); 
			});
			
			$("#inp-due").datePicker({
				dateFormat: "yy-mm-dd",
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
		}; 
	</script>