<div class="container">

	<h3 class="padding-8px"><b>C O M M I T M E N T S</b></h3>

	<div class="alert alert-error hide">
		That would cost too much
	</div>

	<table class="table table-striped table-hover" id="commitments">
		<thead>
			<tr>
				<th>project</th>
				<th>commitment</th>
				<th>promiser</th>
				<th>requester</th>
				<th class="text-center">due by</th>
				<th class="text-center">status</th>
				<th class="text-center">metric</th>
			</th>
		</thead>
	
		<tbody>
			<?php

			$now = new DateTime();		
			
			foreach ($commitments as $commitment)
			{				
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
						<?
				} ?>
					<td style="width:10%">
						<table>
							<tr class="secondary">
								<td><?= $commitment['project_number']?></td>
							</tr>
							<tr>
								<td><?= $projects[$commitment['project_number']]?></td>
							</tr>
						</table>
					</td>
					<td style="width:38%">
						<table>
							<tr class="secondary">
								<td><?= $commitment['task_id']?></td>
							</tr>
							<tr>
								<td style="cursor:pointer;"><?= $commitment['description']?></td>
							</tr>
						</table>
					</td>
					<td style="width:15%">
						<select style="cursor:pointer;text-overflow:ellipsis;" class="form-control input-sm">
							<option selected='selected' value="<?=$commitment['requester'].'">'.$username_lookup[$commitment['requester']]?></option>
							<? foreach ($users as $user) 
							{
								echo('<option value="' . $user['user_id'] . '">' . $user['name'] . '</option>');
							} ?>
						</select>
					</td>
					<td style="width:15%">
						<select style="cursor:pointer;text-overflow:ellipsis;" class="form-control input-sm">
							<option selected='selected' value="<?=$commitment['promiser'].'">'.$username_lookup[$commitment['promiser']]?></option>
							<? foreach ($users as $user) 
							{
								echo('<option value="' . $user['user_id'] . '">' . $user['name'] . '</option>');
							} ?>
						</select>
					</td>
					<td style="width:12%;cursor:pointer;" class="text-center"><?= $commitment['due_by']?></td>
					<td style="width:5%;cursor:pointer;" class="text-center"><?= $commitment['status']?></td>
					<td style="width:5%" class="text-center"><?= $commitment['metric']?></td>
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
		$('#commitments').editableTableWidget({cloneProperties: ['background', 'border', 'outline']});
		
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
			
			console.log("change detected at:", column);
			
			if (column === 0) {
				return false;
			}
		});
    </script>
</div>