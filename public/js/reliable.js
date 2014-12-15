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
	$.datepicker.setDefaults({
	//	dateFormat: "mm/dd/yy",
		numberOfMonths: 2,
		gotoCurrent: true
	});
	
	this.grid = new EditableGrid('grid', {
		enableSort: true,
		dateFormat: "US",
	    // define the number of row visible by page
      	pageSize: 15,

        tableRendered:  function() {  updatePaginator(this); },
		tableLoaded: function() { 

			this.setEnumProvider('status', new EnumProvider({
				getOptionValuesForEdit: function (grid, column, rowIndex) {	
					return { 'open':'open', 'closed':'closed', 'in progress':'in progress', 'deferred':'deferred', 'unknown':'unknown', 'n/a':'n/a' };
				}}));
					
			this.setCellRenderer('actions', new CellRenderer({
				render: function(cell, id) { 
					cell.innerHTML+= "<i onclick=\"this.duplicateRow("+cell.rowIndex+");\" class='fa fa-files-o' >&nbsp;</i>";
					cell.innerHTML+= "<i onclick=\"this.ConfirmDeleteRow("+cell.rowIndex+");\" class='fa fa-minus-square-o' ></i>";
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


CommitmentGrid.prototype.ConfirmDeleteRow = function(id) 
{
	$("#delete-confirm")
		.data("id", id)
		.dialog("open");
}


CommitmentGrid.prototype.DeleteRow = function(index) 
{
	var self = this;
	var uniqueId = self.editableGrid.getValueAt(index, 0);
	var rowId = self.editableGrid.getRowId(index);
	
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
				self.editableGrid.remove(index);
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


CommitmentGrid.prototype.addRow = function(index) 
{
	var self = this;
	var projectNumber = self.editableGrid.getValueAt(index, 1);
	var rowId = self.editableGrid.getRowId(index);

    $.ajax({
		url: '../includes/commitment_add.php',
		type: 'POST',
		dataType: "json",
		data: {
			projectnumber: projectNumber
		},
		success: function (response) 
		{ 
			// get id for new row (max id + 1)
			var newRowId = 0;
			for (var r = 0; r < self.editableGrid.getRowCount(); r++) newRowId = Math.max(newRowId, parseInt(self.editableGrid.getRowId(r)) + 1);
			
			// add new row
			self.editableGrid.insertAfter(index, newRowId, response[0]);
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


CommitmentGrid.prototype.duplicateRow = function(index) 
{
	var self = this;
	var uniqueid = self.editableGrid.getValueAt(index, 0);
	var projectNumber = self.editableGrid.getValueAt(index, 1);
	var rowId = self.editableGrid.getRowId(index);

    $.ajax({
		url: '../includes/commitment_duplicate.php',
		type: 'POST',
		dataType: "json",
		data: {
			uniqueId: uniqueid,
			projectnumber: projectNumber
		},
		success: function (response) 
		{ 
			// get index for new row (max index + 1)
			var newRowId = 0;
			var rowcount = self.editableGrid.getRowCount();
			for (var r = 0; r < rowcount; r++) newRowId = Math.max(newRowId, parseInt(self.editableGrid.getRowId(r)) + 1);
			
			// add new row
			self.editableGrid.insertAfter(index, newRowId, response[0]);
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
