var statuses = {'open':'open', 'open-high':'open-high', 'open-low': 'open-low', 'closed':'closed', 'in progress':'in progress', 'deferred':'deferred', 'unknown':'unknown', 'n/a':'n/a'};

function highlightRow(rowId, bgColor, after)
{
	var rowSelector = $("#grid_" + rowId);
	rowSelector.css("background-color", bgColor);
	rowSelector.fadeTo("normal", 0.5, function() { 
		rowSelector.fadeTo("fast", 1, function() { 
			rowSelector.css("background-color", '');
		});
	});
}

function highlight(div_id, style) {
	highlightRow(div_id, style == "error" ? "#e5afaf" : style == "warning" ? "#ffcc00" : "#8dc70a");
}
   

function CommitmentGrid() 
{ 	
	var self = this;
	
	$.datepicker.setDefaults({
		dateFormat: $.datepicker.W3C,
		numberOfMonths: 2,
		gotoCurrent: true,
		showAnim: 'puff'
	});
	
	self.grid = new EditableGrid('grid', {
		enableSort: true,
		dateFormat: $.datepicker.W3C,
      	pageSize: 10,
		editmode: 'absolute',

        tableRendered:  function() { 
			// activate tooltips onto rendered grid
			$('th.editablegrid-status').attr("title", 'Open: Commitment not complete. \n\
				Closed: Requester is satisfied that promiser has met commitment. \n\
				In Progress: Work on request has begun but is incomplete. \n\
				Deferred: Request is set aside indefinitely. \n\
				Unknown: Promiser and/or requester not available to status commitment.').attr('data-placement', 'left').tooltip();
			
			$('th.editablegrid-metric').attr('title', 'Overdue, complete, anticipated, improvised.').attr('data-placement', 'left').tooltip();
			
			$('th.editablegrid-actions').attr('title', 'Duplicate / Delete. Note: \
				Only delete a commitment if it is truly mistaken. Otherwise, enter its status and/or variance.').attr('data-placement', 'left').tooltip();
				
			updatePaginator(self.grid); 
		},
		tableLoaded: function() { 

			this.setEnumProvider('status', new EnumProvider({
				getOptionValuesForEdit: function (grid, column, rowIndex) {	
					return statuses;
				}}));
					
			this.setCellRenderer('actions', new CellRenderer({
				render: function(cell, id) { 
					cell.innerHTML+= "<i onclick=\""+self.name+".DuplicateRow("+cell.rowIndex+");\" class='fa fa-files-o' >&nbsp;</i>";
					cell.innerHTML+= "<i onclick=\"ConfirmDeleteRow("+cell.rowIndex+");\" class='fa fa-minus-square-o' ></i>";
				}}));
				
			this.setCellRenderer('metric', new CellRenderer({ 
				render: function(cell, value) {
					cell.innerHTML= "<i class=\'fa fa-circle\'></i>";
				}
				}));
			
			this.setCellRenderer('due_by', new CellRenderer({ //shades row based on how soon commitment is due
				render: function(cell, value) {
					date_due=moment(value, 'YYYY-MM-DD')
					cell.innerHTML=date_due.format("\'YY.MM.DD");
					row=self.grid.getRow(cell.rowIndex);
					status=self.grid.getValueAt(cell.rowIndex, 6);
					how_soon=date_due.diff(moment(),'days');
					if (status == 'closed') {
						$(row).addClass('closed');
					}
					else {
						due_class = how_soon < -7 ? 'overdue_2w' : (how_soon < 0 ? 'overdue_1w' : (how_soon < 8 ? 'due_nextweek' : 'due_future'));
						$(cell).addClass(due_class);
					}
				}}));
				
			this.renderGrid('project_commitments', 'table', 'commitments'); 
		},
		modelChanged: function(rowIndex, columnIndex, oldValue, newValue, row) {
   	    	updateCellValue(this, rowIndex, columnIndex, oldValue, newValue, row);
       	}
 	});
}


function updateCellValue(grid, rowIndex, columnIndex, oldValue, newValue, row, onResponse)
{     
	var rowId = grid.getRowId(rowIndex);
	
	$.ajax({
		url: '../includes/commitment_update.php',
		type: 'POST',
		dataType: "html",
		data: {
			uniqueid: grid.getValueAt(rowIndex, 0), 
			newvalue: newValue, 
			colname: grid.getColumnName(columnIndex),
		},
		success: function (response) 
		{ 
			// reset old value if failed then highlight row
			var success = onResponse ? onResponse(response) : (response == "ok" || !isNaN(parseInt(response))); // by default, a successful response can be "ok" or a database id 
			if (!success) grid.setValueAt(rowIndex, columnIndex, oldValue);
		    highlight(rowId, success ? "ok" : "error"); 
		},
		error: function(XMLHttpRequest, textStatus, exception) { alert("Ajax failure\n" + errortext); },
		async: true
	});
}


ConfirmDeleteRow = function(index) 
{
	$("#delete-confirm")
		.data("id", index)
		.dialog("open");
}


CommitmentGrid.prototype.DeleteRow = function(index) 
{
	var self = this;
	var uniqueId = self.grid.getValueAt(index, 0);
	var rowId = self.grid.getRowId(index);
	
    $.ajax({
		url: '../includes/commitment_delete.php',
		type: 'POST',
		dataType: "html",
		data: {
			uniqueid: uniqueId
		},
		success: function (response) 
		{
			var rowSelector = $("#grid_" + rowId);
			rowSelector.css("text-decoration", "line-through");
			rowSelector.fadeOut(function() { 
				self.grid.remove(index);
			});
		},
		error: function(XMLHttpRequest, textStatus, exception) 
		{ 
			highlight(rowId, "error");
			alert("Ajax failure\n" + XMLHttpRequest + "\n Textstatus: " + textStatus + "\n Exception:" + exception); 
		},
		async: true
	});
};


CommitmentGrid.prototype.AddRow = function(values) 
{
	var self = this;
	
    $.ajax({
		url: '../includes/commitment_add.php',
		type: 'POST',
		dataType: "json",
		data: {
			projectnumber: getparam('project'),
			desc: values['description'],
			prom: values['promiser'],
			req: values['requester'],
			due: values['date_due'],
			stat: values['status']
		},
		success: function (response) 
		{ 
			// get id for new row (max id + 1)
			var newRowId = 0;
			var rowCount = self.grid.getRowCount();
			for (var r = 0; r < rowCount; r++) newRowId = Math.max(newRowId, parseInt(self.grid.getRowId(r)) + 1);
			
			// add new row
			self.grid.insertAfter(rowCount, newRowId, response[0]);
			highlight(newRowId, "ok");
		},
		error: function(XMLHttpRequest, textStatus, exception) 
		{ 
			//highlight(rowId, "error");
			alert("Ajax failure\n" + "\n Textstatus: " + textStatus + "\n Exception:" + exception); 
		},
		async: true
	});		
}; 


CommitmentGrid.prototype.DuplicateRow = function(index) 
{
	var self = this;
	var rowId = self.grid.getRowId(index);

    $.ajax({
		url: '../includes/commitment_duplicate.php',
		type: 'POST',
		dataType: "json",
		data: {
			uniqueId: self.grid.getValueAt(index, 0),
			projectnumber: getparam('project')
		},
		success: function (response) 
		{ 
			// get index for new row (max index + 1)
			var newRowId = 0;
			var rowcount = self.grid.getRowCount();
			for (var r = 0; r < rowcount; r++) newRowId = Math.max(newRowId, parseInt(self.grid.getRowId(r)) + 1);
			
			// add new row
			self.grid.insertAfter(index, newRowId, response[0]);
			highlight(newRowId, "ok");
		},
		error: function(XMLHttpRequest, textStatus, exception) 
		{ 
			highlight(rowId, "error");
			alert("Ajax failure\n" + XMLHttpRequest + "\n Textstatus: " + textStatus + "\n Exception:" + exception); 
		},
		async: true
	});
};


function updatePaginator(grid, divId)
{
    divId = divId || "paginator";	
	var paginator = $("#" + divId).empty();
	var nbPages = grid.getPageCount();
	
	// get interval
	var interval = grid.getSlidingPageInterval(20);
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
		sortDate: strDate,
		dbDate: strDate 
	};
};

function populate_select(element, values) {
	$.each(values, function(key, value) {
		$(element).append($("<option>").attr('value',key).text(key));
	});
};

function populate_select_obj(element, objects) {
	$.each(objects, function(key, object) {
		$(element).append($("<option>").attr('value',object['user_id']).text(object['name']));
	});
};