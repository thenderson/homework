/**
 *  highlightRow and highlight are used to show a visual feedback. If the row has been successfully modified, it will be highlighted in green. Otherwise, in red
 */
function highlightRow(rowId, bgColor, after)
{
	console.log('highlightRow selector: #grid_'+rowId);
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
        

function DatabaseGrid() 
{ 
	this.editableGrid = new EditableGrid("grid", {
		enableSort: true,
	    // define the number of row visible by page
      	pageSize: 10,
      // Once the table is displayed, we update the paginator state
        tableRendered:  function() {  updatePaginator(this); },
   	    tableLoaded: function() { datagrid.initializeGrid(this); },
		modelChanged: function(rowIndex, columnIndex, oldValue, newValue, row) {
   	    	updateCellValue(this, rowIndex, columnIndex, oldValue, newValue, row);
       	}
 	});
	this.fetchGrid();
}

DatabaseGrid.prototype.fetchGrid = function()  {
	// call a PHP script to get the data
	this.editableGrid.loadXML("load_data.php");
};

DatabaseGrid.prototype.initializeGrid = function(grid) {

  var self = this;
 
	//renderers for the due_by column
	
	grid.addCellValidator('due_by', new CellValidator({ 
		isValid: function(value) { 
			today = new Date();
			d = new Date(value);
			return d >= today; }
	}));

	//renderers for the status column
	
	grid.setEnumProvider('status', new EnumProvider({ 
		getOptionValuesForEdit: function (grid, column, rowIndex) {	
			return { 'open':'open', 'closed':'closed', 'in progress':'in progress', 'deferred':'deferred', 'unknown':'unknown', 'n/a':'n/a' };
		}

		// the function getOptionValuesForEdit is called each time the cell is edited
		// here we do only client-side processing, but you could use Ajax here to talk with your server
		// if you do, then don't forget to use Ajax in synchronous mode 
		// getOptionValuesForEdit: function (grid, column, rowIndex) {
			// var continent = editableGrid.getValueAt(rowIndex, editableGrid.getColumnIndex("continent"));
			// if (continent == "eu") return { "be" : "Belgique", "fr" : "France", "uk" : "Great-Britain", "nl": "Nederland"};
			// else if (continent == "am") return { "br" : "Brazil", "ca": "Canada", "us" : "USA" };
			// else if (continent == "af") return { "ng" : "Nigeria", "za": "South Africa", "zw" : "Zimbabwe" };
			// return null;
		// }
	}));

	//renderers for the actions column
	
	grid.setCellRenderer('actions', new CellRenderer({ 
		render: function(cell, id) { 
		    cell.innerHTML+= "<i onclick=\"datagrid.addRow("+cell.rowIndex+");\" class='fa fa-plus-square-o' >&nbsp;</i>";
			cell.innerHTML+= "<i onclick=\"datagrid.duplicateRow("+cell.rowIndex+");\" class='fa fa-files-o' >&nbsp;</i>";
			cell.innerHTML+= "<i onclick=\"datagrid.ConfirmDeleteRow("+cell.rowIndex+");\" class='fa fa-minus-square-o' ></i>";
		}
	}));

	grid.renderGrid('tablecontent', 'table table-striped', 'commitments');
}


/**
   updateCellValue calls the PHP script that will update the database. 
 */
function updateCellValue(editableGrid, rowIndex, columnIndex, oldValue, newValue, row, onResponse)
{     
	var rowId = editableGrid.getRowId(rowIndex);
	
	$.ajax({
		url: '../includes/commitment_update.php',
		type: 'POST',
		dataType: "html",
		data: {
			uniqueid: editableGrid.getValueAt(rowIndex, 0), 
			newvalue: newValue, 
			colname: editableGrid.getColumnName(columnIndex),
		},
		success: function (response) 
		{ 
			// reset old value if failed then highlight row
			var success = onResponse ? onResponse(response) : (response == "ok" || !isNaN(parseInt(response))); // by default, a successful response can be "ok" or a database id 
			if (!success) editableGrid.setValueAt(rowIndex, columnIndex, oldValue);
		    highlight(rowId, success ? "ok" : "error"); 
		},
		error: function(XMLHttpRequest, textStatus, exception) { alert("Ajax failure\n" + errortext); },
		async: true
	});
}


DatabaseGrid.prototype.ConfirmDeleteRow = function(id) 
{
	$("#delete-confirm")
		.data("id", id)
		.dialog("open");
}


DatabaseGrid.prototype.DeleteRow = function(index) 
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


DatabaseGrid.prototype.addRow = function(index) 
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
			self.editableGrid.insertAfter(index, newRowId, response);
			//self.editableGrid.refreshGrid();
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


DatabaseGrid.prototype.duplicateRow = function(index) 
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
			self.editableGrid.insertAfter(index, newRowId, response);
			self.editableGrid.refreshGrid();
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


function showAddForm() {
  if ( $("#addform").is(':visible') ) 
      $("#addform").hide();
  else
      $("#addform").show();
}


// helper function to display a message
function displayMessage(text, style) { 
	_$("message").innerHTML = "<p class='" + (style || "ok") + "'>" + text + "</p>"; 
} 


// this will be used to render our table headers
function InfoHeaderRenderer(message) { 
	this.message = message; 
	this.infoImage = new Image();
	this.infoImage.src = image("information.png");
};


InfoHeaderRenderer.prototype = new CellRenderer();
InfoHeaderRenderer.prototype.render = function(cell, value) 
{
	if (value) {
		// here we don't use cell.innerHTML = "..." in order not to break the sorting header that has been created for us (cf. option enableSort: true)
		var link = document.createElement("a");
		link.href = "javascript:alert('" + this.message + "');";
		link.appendChild(this.infoImage);
		cell.appendChild(document.createTextNode("\u00a0\u00a0"));
		cell.appendChild(link);
	}
};


// helper function to get path of a demo image
function image(relativePath) {
	return "img/" + relativePath;
}

