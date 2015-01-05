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

<?php require 'dialogs.html'; ?>
	</body>	
<?php require 'footer.php'; ?>

	<script type="text/javascript">
		jQuery.datepicker.setDefaults({dateFormat:$.datepicker.W3C});   
		
		project_commitments = new CommitmentGrid();
		project_commitments.name = "project_commitments";
		
		window.onload = function() {
			
			$.ajax({
				url: '../includes/load_project_name.php',
				type: 'POST',
				dataType: 'text',
				data: { p: getparam('project') },
				success: function (response) {
					$('#table_title').html("<h3><strong>PROJECT COMMITMENTS: "+response+"</strong> | #"+pnum+"</h3>");},
				error: function(XMLHttpRequest, textStatus, exception) { 
					alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception);},
				async: true
			});
			
			$("#filter_all").keyup(function() { project_commitments.grid.filter($(this).val(), [1,2,3,4,5,6]); });
			
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

			//$(function () { $("[data-toggle='tooltip']").tooltip(); });
		};
	</script>