<div class="container">

	<h3 class="padding-8px"><b>C O M M I T M E N T S</b></h3>

	<div class="alert alert-error hide">
		That would cost too much
	</div>

	
<th id="project_num">project #</th>
<th id="project_shortname">project</th>
<td headers="project_num" contenteditable="false" style="width:8%" class="secondary">
<td headers="project_shortname" contenteditable="false" style="width:12%"><?= ?></td>

	<?php
	$now = new DateTime();
	$p_num_last = -1;
	
	foreach ($commitments as $commitment)
	{
		if ($commitment['project_number'] != $p_num_last) //start a new table
		{ 
			if($p_num_last != -1) //that is, this isn't the first line of data, close-out the previous open table
			{
				?>
				<tfoot>
					<tr>
						<td style="width:5%">total</td>
						<td style="width:45%"></td>
						<td style="width:14%">ppc</td>
						<td style="width:14%">ta</td>
						<td style="width:12%"></td>
						<td style="width:5%"></td>
						<td style="width:5%"></td>
					</tr>
				</tfoot>
				</tbody>
				</table><?php
			}
			
			$p_num_last = $commitment['project_number'];?>
			
			<br>
			<div><h2><?=$commitment['project_number']."  |  ".$projects[$commitment['project_number']]?></h2></div>
			<table class="table table-striped table-hover commitments">
				<thead>
					<tr>
						<th id="unique_id" class="hidden">u_id</th>
						<th id="task_id">id #</th>
						<th id="description">commitment</th>
						<th id="promiser">promiser</th>
						<th id="requester">requester</th>
						<th id="due_by" class="text-center">due by</th>
						<th id="status" class="text-center">status</th>
						<th id="metric" class="text-right">metric</th>
					</th>
				</thead>
		<?php} ?>
	
		<tbody>
			<?php
			$days_til_due = date_diff(new DateTime($commitment['due_by']), $now)->days;
			
			switch($days_til_due) //choose row formatting by task due date proximity
			{
				case($days_til_due < 0):
					?>
					<tr class="danger">
					<?php
					break;
				case($days_til_due < 8):
					?>
					<tr>
					<?php
					break;
				default: //more than one week out
					?>
					<tr class="ghost">
					<?php
			} ?>
				<td headers="unique_id" contenteditable="false" class="hidden"><?=$commitment['unique_id']?></td>
				<td headers="task_id" contenteditable="false" style="width:5%"><?= $commitment['task_id']?></td>
				<td headers="description" contenteditable="true" style="width:45% cursor:pointer;"><?= $commitment['description']?></td>

				<td headers="requester" contenteditable="true" style="width:14%">
					<select style="cursor:pointer;text-overflow:ellipsis;" class="form-control input-sm">
						<option selected='selected' value="<?=$commitment['requester'].'">'.$username_lookup[$commitment['requester']]?></option>
						<? foreach ($users as $user) 
						{
							echo('<option value="' . $user['user_id'] . '">' . $user['name'] . '</option>');
						} ?>
					</select>
				</td>
					
				<td headers="promiser" contenteditable="true" style="width:14%">
					<select style="cursor:pointer;text-overflow:ellipsis;" class="form-control input-sm">
						<option selected='selected' value="<?=$commitment['promiser'].'">'.$username_lookup[$commitment['promiser']]?></option>
						<? foreach ($users as $user) 
						{
							echo('<option value="' . $user['user_id'] . '">' . $user['name'] . '</option>');
						} ?>
					</select>
				</td>
					
				<td headers="due_by" contenteditable="true" style="width:12%;cursor:pointer;" class="text-center"><?= $commitment['due_by']?></td>
				<td headers="status" contenteditable="true" style="width:5%;cursor:pointer;" class="text-center"><?= $commitment['status']?></td>
				<td headers="metric" contenteditable="false" style="width:5%" class="text-right"><?= $commitment['metric']?></td>
			</tr>
<?php } ?>
		</tbody> <!-- close last body -->
	</table>  <!-- close last table -->
	
	<script>
		$(document).ready(function(){
			$('#commitments').editableTableWidget();
			$('#commitments').editableTableWidget({editor: $('<textarea>')});
			$('#commitments').editableTableWidget({cloneProperties: ['background', 'border', 'outline']});
			
			<!-- mark invalid data -->
			$('table td').on('validate', function(evt, newValue) {
				var cell = $(this);
				var header = cell.attr("headers");
				if (header == "unique_id" || header == "project_num" || header == "project_shortname" || header == 'task_id' || header == 'metric') { 
					return false; // mark cell as invalid 
				}
			});

			<!-- act on changed data -->
			$('table td').on('change', function(evt, newValue) {
				var cell = $(this);
				//var row = cell.parent();
				//var col_num = parseInt(cell.index());
				//var row_num = parseInt(row.index()); 
				var header = cell.attr("headers");
				//var c_class = cell.attr("class");
				var u_id = cell.siblings().first().text();
				var value = cell.text();
					
				//console.log("change detected at: ", u_id, ": ", header, " => ", value);
				
 				if (cell.attr("contenteditable"))
				{
					var response = $.ajax({
						url: '/commgr/includes/change_commitment.php',
						data: {
							u_id: u_id,
							field: header,
							new_value: value
						},
						type: 'POST',
						dataType: 'html'
					});
									
					response.done(function(result) {
						if (result=='success') {
							// flash the changed cell green for 1 second
							cell.addClass('flash-ok');
							var delay = setTimeout(function(){cell.removeClass('flash-ok')}, 500);
							return true;
						} else {
							// flash the changed cell red for 1 second & replace value
							cell.addClass('flash-error');
							var delay = setTimeout(function(){cell.removeClass('flash-error')}, 800);

							console.log(result);
							return false;
						}
					});
						
					response.fail(function(err) {
						console.log( "Request failed: " + err );
						return false;
					});
				}
			});
		});
	</script>
</div> <!-- close container -->