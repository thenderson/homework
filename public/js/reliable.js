var statuses = {'O':'open', 'C0':'complete - improvised', 'C1':'complete', 'C2':'complete - anticipated', 'CL':'complete - closed late',
	'V1':'variance - time', 'V2':'variance - waiting, internal', 'V3':'variance - waiting, external', 'V4':'variance - COS', 
	'V5':'variance - superseded, internal', 'V6':'variance - superseded, external', 'V7':'variance - forgot', 'V8':'variance - not needed', 
	'V9':'variance - tech failure', 'D':'deferred', '?':'unknown', 'NA':'n/a'};

function highlightRow(gridname, rowId, bgColor, after) {
	var rowSelector = $("#"+gridname+"_" + rowId);
	rowSelector.css("background-color", bgColor);
	rowSelector.fadeTo("fast", 0.9, function() { 
		rowSelector.fadeTo("fast", 1, function() { 
			rowSelector.css("background-color", '');
		});
	});
}

function highlight(gridname, div_id, style) {
	highlightRow(gridname, div_id, style == "error" ? "#e5afaf" : style == "warning" ? "#ffcc00" : "#8dc70a");
}
   

function CommitmentGrid(name) { 	
	var self = this;
	this.name = name;
	
	$.datepicker.setDefaults({
		dateFormat: $.datepicker.W3C,
		numberOfMonths: 2,
		gotoCurrent: true,
		showAnim: 'puff'
	});
	
	self.grid = new EditableGrid(name, {
		enableSort: true,
		dateFormat: $.datepicker.W3C,
      	pageSize: parseInt(getCookie(self.name+'PageSize') || 10),
		editmode: 'absolute',

        tableRendered:  function() { 
			// // activate tooltips onto rendered grid
			// $('th.editablegrid-priority_h').attr('title', 'high priority.').attr('data-placement', 'left').attr('data-container', 'body').tooltip();

			// $('th.editablegrid-is_closed').attr('title', 'close a completed commitment').attr('data-placement', 'left').attr('data-container', 'body').tooltip();	

			// $('th.editablegrid-status').attr('title', 
				// '<strong>O:</strong> Open commitment\n\
				// <strong>C0:</strong> Closed, improvised\n\
				// <strong>C1:</strong> Closed, 1 week plan\n\
				// <strong>C2:</strong> Closed, anticipated\n\
				// <strong>D:</strong> Deferred indefinitely\n\
				// <strong>V*:</strong> Variance for plan failure.\n\
				// <strong>?:</strong> Status unknown').attr('data-placement', 'left').attr('data-container', 'body').tooltip({html: true}).tooltip();
			
			// $('th.editablegrid-actions').attr('title', 'Duplicate / Delete').attr('data-placement', 'left').attr('data-container', 'body').tooltip();

			// add event listeners to delete and duplicate spans
			$('i.duplicate').not('i.eventAttached').click(function() { 
				var row = $(this).closest('tr');
				var rowId = row[0].rowId;
				var rowIndex = self.grid.getRowIndex(rowId);
				self.DuplicateRow(rowIndex);
			});
			$('i.duplicate').addClass('eventAttached');
			
			$('i.delete').not('i.eventAttached').click(function () {
				var row = $(this).closest('tr');
				var rowId = row[0].rowId;
				var rowIndex = self.grid.getRowIndex(rowId);
				ConfirmDeleteRow(self, rowIndex);
			});
			$('i.delete').addClass('eventAttached');

			updatePaginator(self.grid, self.name+'_paginator', self.grid.pageSize); 
		},
		tableLoaded: function() { 

			var pnum_col = self.grid.getColumnIndex('project_number');
			var closed_col = self.grid.getColumnIndex('is_closed');
			var priority_col = self.grid.getColumnIndex('priority_h');
			var status_col = self.grid.getColumnIndex('status');
			
			if (this.hasColumn('project_number')) {
				this.setCellRenderer('project_number', new CellRenderer({ 
					render: function(cell, value) { 
						cell.innerHTML= "<a title=\"go to project page\" href=\"#\" onclick=\"goto_project_view(\'"+value+"\'); return false;\">"+value+"</a>";
console.log(self.sortedColumnName);
						if (self.sortedColumnName == 'project_number') {	
							if (getValueAt(cell.rowIndex-1, pnum_col) != value && cell.rowIndex>1) {
								row=self.grid.getRow(cell.rowIndex);
								row.addClass('border-dark');
							}
						}
					}}));
			};
					
			this.setCellRenderer('actions', new CellRenderer({
				render: function(cell, id) { 
					cell.innerHTML+= "<i class='duplicate fa fa-files-o' >&nbsp;</i><i class='delete fa fa-minus-square-o' ></i>";
				}}));
			
			this.setCellRenderer('due_by', new CellRenderer({ //shades cells based on how soon commitment is due
				render: function(cell, value) {
					row=self.grid.getRow(cell.rowIndex);
					
					if (value == '0000-00-00') { //handle deferred & recently un-deferred items
						if (self.grid.getValueAt(cell.rowIndex, status_col) == 'D') {
							cell.innerHTML = '-';
							$(cell).removeClass('status_me_now');
						}
						else {
							cell.innerHTML = '!';
							$(cell).addClass('status_me_now');
						}
					}
					else { // assign due_class based on how overdue / soon due the task is
						date_due=moment(value, 'YYYY-MM-DD');
						cell.innerHTML=date_due.format("\'YY.MM.DD");
						how_soon=date_due.diff(moment(),'days');
						due_class = how_soon < 0 ? 'overdue' : (how_soon < 2 ? 'due_tomorrow' : (how_soon < 8 ? 'due_nextweek' : 'due_future'));
						$(cell).addClass(due_class).removeClass('status_me_now');
					}
				}}));
				
			this.setCellRenderer('description', new CellRenderer ({ //shades cells based on priority
				render: function(cell, value) {
					cell.innerHTML=value;
					priority = self.grid.getValueAt(cell.rowIndex, priority_col);
					if (priority == true) {
						$(cell).addClass('priority-h');
					}
					else $(cell).removeClass('priority-h');
					
					if (value == '') {
						cell.innerHTML = '!';
						$(cell).addClass('status_me_now');
					}
					else $(cell).removeClass('status_me_now');
				}}));
			
			this.setEnumProvider('status', new EnumProvider({
				getOptionValuesForEdit: function (grid, column, rowIndex) {
					status = self.grid.getValueAt(rowIndex, status_col);
					if (status == 'O') return {'C':'Close','D':'Defer', '?':'Unknown'};
					else if (status == 'D') return {'O':'reOpen', 'C':'Close', '?':'Unknown'};
					else if (status == '?') return {'O':'reOpen', 'C':'Close', 'D':'Defer'};
					else if (/C[L012]/.test(status)) return {'O':'reOpen'}; 
					else if (status == 'V?') return {'V1':'V1 time','V2':'V2 waiting, int.','V3':'V3 waiting, ext.','V4':'V4 COS','V5':'V5 fire, int.',
						'V6':'V6 fire, ext.','V7':'V7 forgot','V8':'V8 not needed','V9':'V9 tech failure'};
					else if (/V[123456789]/.test(status)) return {'V1':'V1 time','V2':'V2 waiting, int.','V3':'V3 waiting, ext.','V4':'V4 COS','V5':'V5 fire, int.',
						'V6':'V6 fire, ext.','V7':'V7 forgot','V8':'V8 not needed','V9':'V9 tech failure','V?':'V?'};
					return;
				} // V1, V2, V3, V4, V5, V6, V7, & V9 all suggest replanning the task. V2 & V3 suggest logging requests of others; V4 suggests a better description; V7 suggests reminders
			}));
				
			this.setCellRenderer('status', new CellRenderer ({ 
				render: function(cell, value) {
					cell.innerHTML = value;
					row=self.grid.getRow(cell.rowIndex);
					
					if (value == 'D') $(row).addClass('deferred');
					else $(row).removeClass('deferred');
					
					if (value == 'V?' || value == '?') $(cell).addClass('status_me_now');
					else $(cell).removeClass('status_me_now');
					
					if (/C[L012]/.test(value) || /V[0123456789]/.test(value)) $(row).addClass('closed');
					else $(row).removeClass('closed');
				}
			}));
			
			this.setCellRenderer('task_id', new CellRenderer ({
				render: function(cell, value) {
					var floor = Math.floor(value);
					var dec = (value-floor)*100;
					if (dec < 10) decstr = '.0' + dec;
					else decstr = '.' + dec;
					
					if (dec != 0) cell.innerHTML = value;
					else cell.innerHTML = floor + "<span class='zerozero'>" + decstr + '</span>';
			}}));
		
	/*		this.setCellRenderer('requester', new CellRenderer ({
				render: function(cell, value) {
					if (value == '0') {
						cell.innerHTML = '!';
						$(cell).addClass('status_me_now');
					}
					else {
						//cell.innerHTML=value;
						$(cell).removeClass('status_me_now');
					}
				}}));
				
				this.setCellRenderer('promiser', new CellRenderer ({
				render: function(cell, value) {
					if (value == '0') {
						cell.innerHTML = '!';
						$(cell).addClass('status_me_now');
					}
					else {
						//cell.innerHTML=value;
						$(cell).removeClass('status_me_now');
					}
				}}));
	*/		
			this.setCellRenderer('visual', new CellRenderer ({
				render: function(cell, value) {

					var row=self.grid.getRow(cell.rowIndex);
					var magnitude_col = self.grid.getColumnIndex('magnitude');
					var magnitude = self.grid.getValueAt(cell.rowIndex, magnitude_col);
					var date_due_col = self.grid.getColumnIndex('due_by');
					var due_by = moment(self.grid.getValueAt(cell.rowIndex, date_due_col));
					var status_col = self.grid.getColumnIndex('status');
					var status = self.grid.getValueAt(cell.rowIndex, status_col);

					var lookahead = Math.max(3, (horizon == 'all' ? 26 : horizon/7 + 1));
					var lookback = (show_closed == true) ? lookahead : 2;

					var last_monday = moment().startOf('ISOweek');
					var min_date = last_monday.clone().subtract(lookback, 'weeks');
					var max_date = last_monday.clone().add(Math.max(3, lookahead), 'weeks');
					var requested_on = moment(value);

					var height = 32;
					var midline = height / 2;

					var width = $('.editablegrid-visual').width();
					var ypad = 6;
					var xpad = 12;

					var graph = d3.select(cell)
						.append("svg:svg")
						.attr("width", width)
						.attr("height", height);

					var x = d3.time.scale()
						.domain([min_date, max_date])
						.range([0, width - xpad]);
						
					var r = d3.scale.linear() // radius of circles
						.domain([0, 50])
						.clamp(false)
						.range([3, height*.47]);
						
					var op = d3.scale.linear() // fill opacity for circles
						.domain([0, 100])
						.clamp(true)
						.range([1, .4]);
			
					y1 = midline - 2;
					y2 = midline + 2;
										
					// requested_on marker
					graph.append('circle')
						.attr('class', 'req_circle')
						.attr('cx', x(requested_on))
						.attr('cy', midline)
						.attr('r', r(1));
					
					// draw timescale
					for (j=-lookback; j<lookahead+1; j++) { // build homebrewed axis
						xx = x(last_monday.clone().add(j, 'weeks'));
						if (j == 0 || j == 1) {
							graph.append('svg:line')
								.attr('x1', xx)
								.attr('x2', xx)
								.attr('y1', y1-2)
								.attr('y2', y2+2)
								.attr('class', 'tick_chart_thiswk');
						}
						else {
							graph.append('svg:line')
								.attr('x1', xx)
								.attr('x2', xx)
								.attr('y1', y1)
								.attr('y2', y2)
								.attr('class', 'tick_chart');
						}
					}
					
					// draw today on timescale
					xx = x(moment());
					graph.append('svg:line')
						.attr('x1', xx)
						.attr('x2', xx)
						.attr('y1', 0)
						.attr('y2', height)
						.attr('class', 'tick_chart_today');
					
					// draw commitment due_on
					if (/V[0123456789]/.test(status)) {
						graph.append('text')
							.attr('x', x(due_by))
							.attr('y', midline + 4)
							.attr('text-anchor', 'middle')
							.text('X')
							.attr('class', 'due_variance');
					}
					else {
						graph.append('svg:circle')
							.attr('class', (/C[L012]/.test(status) ? 'due_circle_closed' : 
								(status == 'V?' ? 'due_circle_overdue' : 
									(/V[0123456789]/.test(status) ? 'due_circle_variance' : 'due_circle'))))
							.attr('cx', x(due_by))
							.attr('cy', midline)
							.attr('r', r(magnitude))
							.attr('fill-opacity', String(op(magnitude)));
					}
				}
			}));
			
			this.renderGrid(self.name+'_d', 'table', self.name); 
			$('[id^='+self.name+'_total]').html('total: <strong>'+self.grid.getTotalRowCount()+'</strong>');
		},
		modelChanged: function(rowIndex, columnIndex, oldValue, newValue, row) {
			if (/V[012345679]/.test(newValue) && !(/V[012345679]/.test(oldValue))) { // if setting a new variance, unless changing from a previous replannable variance
				requestReplan(self, rowIndex, columnIndex, oldValue, newValue);} // V8 not included
   	    	else updateCellValue(self, rowIndex, columnIndex, oldValue, newValue);
       	}
 	});
}


function requestReplan(comgrid, rowIndex, columnIndex, oldValue, newValue) {
	
	var oldRowValues = comgrid.grid.getRowValues(rowIndex);
	
	var msg_general = "Please replan this task or cancel to record its closing status as V8 'Not Needed.'";
	var msg_date_due = 'Enter new due date.';
	var msg_description = '';

	switch (newValue) {
		case 'V1': // time: replan w/ sufficient time
			msg_description = 'Consider defining the scope of the task more narrowly if it was too broad.'
			break;
			
		case 'V2': // waiting, internal: same as V3
		case 'V3': // waiting, external: replan + ask to set commitment for person being waited on
			msg_general += " <strong>Consider requesting a commitment from the person you're waiting on for the information you need.</strong>";
			break;
			
		case 'V4': // COS: replan w/ better description
			msg_description = ' <strong>Confirm the commitment description with the requester.</strong>';
			break;
			
		case 'V5': // superseded, internal: replan, message about deeper planning
		case 'V6': // superseded, external: replan, message about broader planning
			break;
			
		case 'V7': // forgot: replan, message about use of workplan to help remember
			msg_general += ' Consider referring to your workplan more frequently.';
			break;
			
		case 'V9': // tech failure: replan + ask to set commitment for IT/tech person
			msg_general += ' Consider requesting a commitment to resolve the source of the technical failure.';
			
		default:
	}
	
	$.ajax({ //load commitment information and pass to dialog box for input.
		url: '../includes/load_one_commitment.php',
		type: 'POST',
		dataType: 'JSON',
		data: {id: oldRowValues['unique_id']},
		success: function (response) {
			$("#add-commitment")
				.data('replan', 1)
				.data('duplicate', 0)
				.data('msg-general', msg_general)
				.data('msg-description', msg_description)
				.data('msg-date-due', msg_date_due)
				.data('commitmentgrid', comgrid)
				.data('rowIndex', rowIndex)
				.data('columnIndex', columnIndex)
				.data('oldValue', oldValue)
				.data('newValue', newValue)
				.data('oldRowValues', response)
				.dialog({
					show: { effect: "puff", duration: 150},
					title: 'Replan this Commitment',
					height: 600
				})
				.dialog("open"); 
		},
		error: function(XMLHttpRequest, textStatus, exception) { 
			alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception);},
		async: true
	});
}


function updateCellValue(comgrid, rowIndex, columnIndex, oldValue, newValue) {
	var rowId = comgrid.grid.getRowId(rowIndex);
	var date_due_col = comgrid.grid.getColumnIndex('due_by');
	var uniqueid_col = comgrid.grid.getColumnIndex('unique_id');
	var taskid_col = comgrid.grid.getColumnIndex('task_id');
	
	$.ajax({
		url: '../includes/commitment_update.php',
		type: 'POST',
		dataType: "json",
		data: {
			uniqueid: comgrid.grid.getValueAt(rowIndex, uniqueid_col),
			newvalue: newValue, 
			oldvalue: oldValue,
			colname: comgrid.grid.getColumnName(columnIndex),
			date_due: comgrid.grid.getValueAt(rowIndex, date_due_col)
		},
		success: function (response) 
		{
			// reset old value if failed then highlight row
			if (response == 'error' || response == "") {
				comgrid.grid.setValueAt(rowIndex, columnIndex, oldValue);
				highlight(comgrid.grid.name, rowId, "error"); 
			}
			else {
				values = response[0];
				$.each(values, function(key, value) {
					columnIndex = comgrid.grid.getColumnIndex(key);
					if (columnIndex != -1) comgrid.grid.setValueAt(rowIndex, columnIndex, value);
				});
				highlight(comgrid.grid.name, rowId, "ok");
			};
		},
		error: function(XMLHttpRequest, textStatus, exception) { 
			alert("Session expired. Please reload page.");
			comgrid.grid.setValueAt(rowIndex, columnIndex, oldValue);
			highlight(comgrid.grid.name, rowId, "error");
		},
		complete: function () {
			$('[id^='+comgrid.name+'_total]').animate({opacity: .8}, 500, function() {
				$('[id^='+comgrid.name+'_total]').html('total: <strong>'+comgrid.grid.getTotalRowCount()+'</strong>');
				$('[id^='+comgrid.name+'_total]').animate({opacity: 1}, 100);
			});
		},
		async: true
	});
}


ConfirmDeleteRow = function(commitmentgrid, rowIndex) {
	$("#delete-confirm")
		.data('rowIndex', rowIndex)
		.data('commitmentgrid', commitmentgrid)
		.dialog("open");
}


CommitmentGrid.prototype.DeleteRow = function(index) {
	var self = this;
	var uniqueid_col = self.grid.getColumnIndex('unique_id');
	var uniqueId = self.grid.getValueAt(index, uniqueid_col);
	var rowId = self.grid.getRowId(index);
	
    $.ajax({
		url: '../includes/commitment_delete.php',
		type: 'POST',
		dataType: "html",
		data: {
			uniqueid: uniqueId
		},
		success: function (response) {
			var rowSelector = $('#' + self.name+'_' + rowId);
			rowSelector.css("text-decoration", "line-through");
			rowSelector.fadeOut(500, function() { 
				self.grid.remove(index);
			});
		},
		error: function(XMLHttpRequest, textStatus, exception) { 
			highlight(self.name, rowId, "error");
			alert("Ajax failure\n" + XMLHttpRequest + "\n Textstatus: " + textStatus + "\n Exception: " + exception); 
		},
		complete: function () {
			$('[id^='+self.name+'_total]').animate({opacity: 0}, 500, function() {
				$('[id^='+self.name+'_total]').html('total: <strong>'+self.grid.getTotalRowCount()+'</strong>');
				$('[id^='+self.name+'_total]').animate({opacity: 1}, 100);
			});
		},
		async: true
	});
};


CommitmentGrid.prototype.AddRow = function(values) {
	var self = this;
    $.ajax({
		url: '../includes/commitment_add.php',
		type: 'POST',
		dataType: "json",
		data: {
			projectnumber: values['projectnumber'],
			desc: values['description'],
			mag: 1, //TODO: request magnitude in modal
			prom: values['promiser'],
			req: values['requester'],
			due: values['date_due'],
			stat: values['status'],
			mag: values['magnitude'] || 1,
			replan: (values['replan']) ? values['replan'] : -1
		},
		success: function (response) { 
			// get id for new row (max id + 1)
			var newRowId = 0;
			var rowCount = self.grid.getRowCount();
			for (var r = 0; r < rowCount; r++) newRowId = Math.max(newRowId, parseInt(self.grid.getRowId(r)) + 1);
			
			// add new row
			self.grid.insertAfter(rowCount, newRowId, response[0]);
			highlight(self.name, newRowId, "ok");
		},
		error: function(XMLHttpRequest, textStatus, exception) { 
			//highlight(rowId, "error");
			alert("Ajax failure\n" + "\n Textstatus: " + textStatus + "\n Exception:" + exception); 
		},
		complete: function () {
			$('[id^='+self.name+'_total]').animate({opacity: 0}, 500, function() {
				$('[id^='+self.name+'_total]').html('total: <strong>'+self.grid.getTotalRowCount()+'</strong>');
				$('[id^='+self.name+'_total]').animate({opacity: 1}, 100);
			});
			if (values['recordanother'] == 1) { // capture another new commitment if the user hit the 'submit+' button
				$("#add-commitment")
					.data('replan', 0)
					.dialog({
						show: { effect: "puff", duration: 150},
						height: 550,
						title: 'Record New Commitment' })
					.dialog("open"); 
			}
		},
		async: true
	});		
}; 


CommitmentGrid.prototype.DuplicateRow = function(rowIndex) 
{
	var self = this;
	var oldRowValues = self.grid.getRowValues(rowIndex);
	var msg_general = "Make any needed changes before adding this as a new commitment.";

	$.ajax({ //load commitment information and pass to dialog box for input.
		url: '../includes/load_one_commitment.php',
		type: 'POST',
		dataType: 'JSON',
		data: {id: oldRowValues['unique_id']},
		success: function (response) {
			$("#add-commitment")
				.data('replan', 0)
				.data('duplicate', 1)
				.data('msg-general', msg_general)
				.data('commitmentgrid', self)
				.data('rowIndex', rowIndex)
				.data('oldRowValues', response)
				.dialog({
					show: { effect: "puff", duration: 150},
					title: 'Duplicate Commitment',
					height: 600
				})
				.dialog("open"); 
		},
		error: function(XMLHttpRequest, textStatus, exception) { 
			alert("Ajax FAIL!\n" + "\nTextstatus: " + textStatus + "\nException: " + exception);},
		async: true
	});
};


function updatePaginator(grid, divId)
{
    divId = divId || "paginator";	
	var paginator = $("#" + divId).empty();
	var nbPages = grid.getPageCount();
	
	// get interval
	var interval = (nbPages <= 0) ? null : grid.getSlidingPageInterval(nbPages);
	if (interval == null) return;
	
	// get pages in interval (with links except for the current page)
	var pages = grid.getPagesInInterval(interval, function(pageIndex, isCurrent) {
		if (isCurrent) return "<span id='currentpageindex'>" + (pageIndex + 1)  +"</span>";
		return $("<a>").css("cursor", "pointer").html(pageIndex + 1).click(function(event) { grid.setPageIndex(parseInt($(this).html()) - 1); });
	});
		
	// "first" link
	var link = $("<a class='nobg'>").html("<i class='fa fa-fast-backward'></i>");
	if (!grid.canGoBack()) link.css({ opacity : 0.4, filter: "alpha(opacity=40)" });
	else link.css("cursor", "pointer").click(function(event) { grid.firstPage(); });
	paginator.append(link);

	// "prev" link
	link = $("<a class='nobg'>").html("<i class='fa fa-backward'></i>");
	if (!grid.canGoBack()) link.css({ opacity : 0.4, filter: "alpha(opacity=40)" });
	else link.css("cursor", "pointer").click(function(event) { grid.prevPage(); });
	paginator.append(link);

	// pages
	for (p = 0; p < pages.length; p++) paginator.append(pages[p]).append(" ");
	
	// "next" link
	link = $("<a class='nobg'>").html("<i class='fa fa-forward'>");
	if (!grid.canGoForward()) link.css({ opacity : 0.4, filter: "alpha(opacity=40)" });
	else link.css("cursor", "pointer").click(function(event) { grid.nextPage(); });
	paginator.append(link);

	// "last" link
	link = $("<a class='nobg'>").html("<i class='fa fa-fast-forward'>");
	if (!grid.canGoForward()) link.css({ opacity : 0.4, filter: "alpha(opacity=40)" });
	else link.css("cursor", "pointer").click(function(event) { grid.lastPage(); });
	paginator.append(link);
}; 

//handy function to extract a parameter from a GET request URI.
function getparam(name){
	if(name=(new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search))
	return decodeURIComponent(name[1]);
}

/**
 * Overloading the default checkDate function in order to neuter it.
 */
EditableGrid.prototype.checkDate = function(strDate, strDatestyle) {
	return { 
		formattedDate: strDate,
		sortDate: Date.parse(strDate),
		dbDate: strDate 
	};
};

function populate_select(element, values) {
	$.each(values, function(key, value) {
		$(element).append($("<option>").attr('value',key).text(key));
	});
};

function populate_select_names(element, values) {
	if (Object.keys(values).length > 1) {
		$(element).empty().append($('<option>', {value: 'placeholder', selected: true}).text(''));
		$.each(values, function(key, value) {
			$(element).append($('<option>', {value : key+''}).text(value));
		});
	}
	else if (Object.keys(values).length == 1) { // if only one choice, select it for the user to be nice
		$.each(values, function(key, value) {
			$(element).empty().append($('<option>', {value: 'placeholder'}).text(''));
			$(element).append($('<option>', {value : key+'', selected: true}).text(value));
		});
	}
	else { 
		$(element).empty().append($('<option>', {value: 'placeholder', selected: true}).text(''));
	}
	$(element).selectmenu('refresh');
};

function populate_select_projects(element, objects) {
	$.each(objects, function(key, object) {
		$(element).append($("<option>").attr('value',object['project_number']).text(object['project_name']));
	});
};

// pass to project-specific page
goto_project_view = function (p_num) { 
	window.location.href = "../templates/project_commitments_form.php?project="+p_num;}
	
/**
 * Overloading default function to add cookie for pagesize
 */
EditableGrid.prototype.setPageSize = function(cookiename, pageSize)
{
	this.pageSize = parseInt(pageSize);
	if (isNaN(this.pageSize)) this.pageSize = 0;
	this.currentPageIndex = 0;
	this.refreshGrid();
	
	setCookie(cookiename, this.pageSize, 90);
};

function setCookie(cookieName,cookieValue,nDays) {
 var today = new Date();
 var expire = new Date();
 if (nDays==null || nDays==0) nDays=42;
 expire.setTime(today.getTime() + 3600000*24*nDays);
 document.cookie = cookieName+"="+escape(cookieValue)
                 + ";expires="+expire.toGMTString();
}

function getCookie(cookieName) {
 var theCookie=" "+document.cookie;
 var ind=theCookie.indexOf(" "+cookieName+"=");
 if (ind==-1) ind=theCookie.indexOf(";"+cookieName+"=");
 if (ind==-1 || cookieName=="") return "";
 var ind1=theCookie.indexOf(";",ind+1);
 if (ind1==-1) ind1=theCookie.length; 
 return unescape(theCookie.substring(ind+cookieName.length+2,ind1));
}