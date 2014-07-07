<div class="container">

	<h4>C O M M I T M E N T S</h4>

	<div class="alert alert-error hide">
		That would cost too much
	</div>

	<table class="table table-striped table-hover table-condensed" id="commitments">
		<thead>
			<tr>
				<th>project</th>
				<th>commitment</th>
				<th>promiser</th>
				<th>requester</th>
				<th>
					<table>
						<tr><h6>requested on</h6></tr>
						<tr>due by</tr>
					</table>
				<th>status</th>
				<th>type</th>
				<th>metric</th>
				<th>days</th>
			</th>
		</thead>
	
		<tbody>
			<?php

			$now = new DateTime();		
			
			foreach ($commitments as $commitment)
			{				
				$days_til_due = date_diff($now, new DateTime($commitment['due_by']))->days;
				
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
						<?
				} ?>
					<td>
						<table>
							<tr><td><h6><?= $commitment['project_number']?></h6></td></tr>
							<tr><td><?= $projects[$commitment['project_number']]?></td></tr>
						</table>
					</td>
					<td>
						<table>
							<tr><td><h6><?= $commitment['task_id']?></h6></td></tr>
							<tr><td><?= $commitment['description']?></td></tr>
						</table>
					<td><select><option selected='selected' value="<?=$commitment['requester'].'">'.$users[array_search($commitment['requester'], $users['user_id'])]['name']?></option>
						<? foreach ($users as $row) echo('<option value="' . $row['user_id'] . '">' . $row['name'] . '</option>'); ?></select></td>
					<td><select><option selected='selected' value="<?=$commitment['promiser'].'">'.$users[array_search($commitment['promiser'], $users['user_id'])]['name']?></option>
						<? foreach ($users as $row) echo('<option value="' . $row['user_id'] . '">' . $row['name'] . '</option>'); ?></select></td>
					<td>
						<table>
							<tr><td><h6><?= $commitment['requested_on']?><h6></td></tr>
							<tr><td><?= $commitment['due_by']?></td></tr>
						</table>
					<td><?= $commitment['status']?></td>
					<td><?= $commitment['type']?></td>
					<td><?= $commitment['metric']?></td>
					<td><?= $days_til_due?></td>
				</tr>
			<?php } ?>
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