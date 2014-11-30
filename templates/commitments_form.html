	<body>
		<div class="container-fluid" id="wrap">

			<!-- TODO
			collapse projects
			filter @ column level
			track variance
			replan tasks with certain variances
			-->
			
			<!-- Feedback message zone -->
			<div id="message"></div>

			<table id="filterbar">
				<tr>
					<td>
						<div class="filter filter_description">
						  <input type="text" id="filter_desc" name="filter" placeholder="filter description"  />
						</div>
					</td>
					<td>
						<div class="filter filter_requester">
						  <input type="text" id="filter_req" name="filter" placeholder="filter requester"  />
						</div>
					</td>
					<td>
						<div class="filter filter_promiser">
						  <input type="text" id="filter_pro" name="filter" placeholder="filter promiser"  />
						</div>
					</td>
				</tr>
			</table>
			<!-- Grid contents -->
			<div id="tablecontent"></div>
		
			<!-- Paginator control -->
			<div id="paginator"></div>
			
			<script src="js/editablegrid-2.1.0-b13.js"></script>
			<script src="js/editablegrid_renderers.js" ></script>
			<script src="js/editablegrid_editors.js" ></script>
			<script src="js/editablegrid_validators.js" ></script>
			<script src="js/editablegrid_utils.js" ></script>
			<script src="js/editablegrid_charts.js" ></script>
			<script src="js/reliable.js" ></script>
		</div>
							
		<div id="delete-confirm" title="Delete commitment?" data-role="dialog">   
			<div data-role="content" id="text">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><h4>Deleting commitments cannot be undone. Are you sure?</h4></p>
			</div>
		</div>
		
	</body>
	
	<script type="text/javascript">
	
		var datagrid = new DatabaseGrid();
		window.onload = function() { 

			// key typed in the filter field
			$("#filter_desc").keyup(function() {
				datagrid.editableGrid.filter($(this).val(), [3]);
			});
	
			$("#filter_req").keyup(function() {
				datagrid.editableGrid.filter($(this).val(), [4]);
			});

			$("#filter_prom").keyup(function() {
				datagrid.editableGrid.filter($(this).val(), [5]);
			});
			
			$("#delete-confirm").dialog({
				resizable: false,
				autoOpen: false,
				height:180,
				modal: true,
				buttons: {
					"Delete": function() {
						$(this).dialog("close");
						datagrid.DeleteRow($(this).data('id'));
					},
					Cancel: function() {
						$(this).dialog("close");
					}
				}
			});
		}; 
	</script>
