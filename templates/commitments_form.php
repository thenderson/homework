<div class="container">

	<h5>C O M M I T M E N T S</h5>

	<div class="alert alert-error hide">
		That would cost too much
	</div>

	<table class="table table-striped" id="commitments">
		<thead>
			<tr>
				<th>project #</th>
				<th>project name</th>
				<th>task #</th>
				<th>description</th>
				<th>promiser</th>
				<th>requester</th>
				<th>due by</th>
				<th>days</th>
				<th>requested on</th>
				<th>status</th>
				<th>type</th>
				<th>metric</th>
			</th>
		</thead>
	
		<tbody>
			<?php

			$now = new DateTime();
			
			foreach ($commitments as $commitment)
			{
				$days_til_due = date_diff($now, new DateTime($commitment["due_by"]))->days;
				
				switch($days_til_due) //choose row formatting by task due date proximity
				{
					case($days_til_due<0):
						?>
						<tr class="danger">
						<?php
						break;
					case($days_til_due<8):
						?>
						<tr class="info">
						<?php
						break;
					default:
						?>
						<tr>
						<?
				}   
				
			?>
					<td><?= $commitment["project_number"]?></td>
					<td><?= 
						$res = mysqli->query("SELECT project_shortname FROM projects WHERE project_number = ?", $commitment["project_number"]);
						echo $res;
					?></td>
					<td><?= $commitment["task_id"]?></td>
					<td><?= $commitment["description"]?></td>
					<td><?=
							$res = mysqli->query("SELECT email, name FROM users");
							while ($row = mysqli_fetch_array($res)){
								if ($row['email'] == $commitment["requester"]) {
									echo '<option selected = "selected" value="' . $row['email'] . '">' . $row['requester'] . '</option>';
								}
								else {
									echo '<option value="' . $row['email'] . '">' . $row['requester'] . '</option>';
								}
							}
					?></td>
					<td><?= $commitment["promiser"]?></td>
					<td><?= $commitment["due_by"]?></td>
					<td><?= $days_til_due?></td>
					<td><?= $commitment["requested_on"]?></td>
					<td><?= $commitment["status"]?></td>
					<td><?= $commitment["type"]?></td>
					<td><?= $commitment["metric"]?></td>
				</tr>
			<?php      
			}
			?>
		</tbody>
    </table>
	<tfoot>
		<tr>
			
		</tr>
	</tfoot>
	
    <script>
		$('#commitments').editableTableWidget();
		$('#commitments').editableTableWidget({editor: $('<textarea>')});
		$('#commitments').editableTableWidget({
		cloneProperties: ['background', 'border', 'outline']
		});
		
		<!-- mark invalid data -->
		//$('table td').on('validate', function(evt, newValue) {
		//	if (....) { 
		//		return false; // mark cell as invalid 
		//	}
		//});

		<!-- act on changed data -->
		$('table td').on('change', function(evt, newValue) {
			var cell = $(this),
				column = cell.index(),
				total = 0;
			if (column === 0) {
				return;
			}
			element.find('tbody tr').each(function () {
				var row = $(this);
				total += parseFloat(row.children().eq(column).text());
			});
			if (column === 1 && total > 5000) {
				$('.alert').show();
				return false; // changes can be rejected
			} else {
				$('.alert').hide();
				footer.children().eq(column).text(total);
			}
		});
    </script>
</div>