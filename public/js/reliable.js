/**
 *  highlightRow and highlight are used to show a visual feedback. If the row has been successfully modified, it will be highlighted in green. Otherwise, in red
 */
function highlightRow(rowId, bgColor, after)
{
	var rowSelector = $("#" + rowId);
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
	
	//renderers for the unique_id column
	grid.setCellRenderer('unique_id', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('unique_id');
		}
	}));
	
	grid.setHeaderRenderer('unique_id', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('unique_id');
		}
	}));
	
	//renderers for the project_number column
	grid.setCellRenderer('project_number', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('project_number');

		}
	}));
		
	grid.setHeaderRenderer('project_number', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('project_number');
			//$(cell).prop('title', 'Project that the work is part of.');
		}
	}));
	
	//grid.setHeaderRenderer('project_number', new InfoHeaderRenderer('Project that the work is part of.'));

	//renderers for the task_id column
	grid.setCellRenderer('task_id', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('task_id');
		}
	}));
		
	grid.setHeaderRenderer('task_id', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('task_id');
		}
	}));
	
	grid.setHeaderRenderer('task_id', new InfoHeaderRenderer('Unique ID for the request.'));

	//renderers for the description column
	grid.setCellRenderer('description', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('description');
		}
	}));
		
	grid.setHeaderRenderer('description', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('description');
		}
	}));
	
	grid.setHeaderRenderer('description', new InfoHeaderRenderer('Descibe what work products \
		shall be handed-off to whom in what form, via what method & at what level of completion.'));

	//renderers for the promiser column
	grid.setCellRenderer('promiser', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('promiser');
		}
	}));
		
	grid.setHeaderRenderer('promiser', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('promiser');
		}
	}));
	
	grid.setHeaderRenderer('promiser', new InfoHeaderRenderer('Select the person who is promising the work described.'));

	//renderers for the requester column
	grid.setCellRenderer('requester', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('requester');
		}
	}));
		
	grid.setHeaderRenderer('requester', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('requester');
		}
	}));
	
	grid.setHeaderRenderer('requester', new InfoHeaderRenderer('Select the person who is asking \
		for the work described. If the promiser = the requester, this will be considered a \
		personal workplan item and may not appear on the team workplan.'));

	//renderers for the due_by column
	grid.setCellRenderer('due_by', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('due_by');
		}
	}));
		
	grid.setHeaderRenderer('due_by', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('due_by');
		}
	}));
	
	grid.setHeaderRenderer('due_by', new InfoHeaderRenderer('Select the date when the work product described be handed-off.'));
	
	grid.addCellValidator('due_by', new CellValidator({ 
		isValid: function(value) { 
			today = new Date();
			d = new Date(value);
			return d >= today; }
	}));

	//renderers for the status column
	grid.setCellRenderer('status', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('status');
		}
	}));
		
	grid.setHeaderRenderer('status', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('status');
		}
	}));
	
	grid.setHeaderRenderer('status', new InfoHeaderRenderer('Open: Commitment is not complete. \n\
		Closed: The requester is satisfied that the promiser has met the commitment described. \n\
		In Progress: Work on the request has begun but is incomplete. \n\
		Deferred: The request is set aside indefinitely. \n\
		Unknown: The promiser and/or requester are not available to status the commitment.'));
	
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

	//renderers for the metric column
	grid.setCellRenderer('metric', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('metric');
		}
	}));
	
	grid.setHeaderRenderer('metric', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('metric');
		}
	}));
	
	grid.setHeaderRenderer('metric', new InfoHeaderRenderer('Overdue, complete, anticipated, improvised.'));
	
	grid.setCellRenderer('actions', new CellRenderer({ 
		render: function(cell, id) { 
		    cell.innerHTML+= "<i onclick=\"datagrid.addRow("+cell.rowIndex+");\" class='fa fa-plus-square-o' >&nbsp;</i>";
			cell.innerHTML+= "<i onclick=\"datagrid.duplicateRow("+cell.rowIndex+");\" class='fa fa-files-o' >&nbsp;</i>";
			cell.innerHTML+= "<i onclick=\"datagrid.deleteRow("+cell.rowIndex+");\" class='fa fa-minus-square-o' ></i>";
			//CellRenderer.prototype.render.call(this, cell, id);
			$(cell).addClass('actions');
		}
	})); 
	
	grid.setHeaderRenderer('actions', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('actions');
		}
	}));
	
	grid.setHeaderRenderer('actions', new InfoHeaderRenderer('Delete or duplicate. Note: \
		Only delete a commitment if it is truly messed-up. Otherwise, its status and/or variance should be entered.'));

	grid.renderGrid('tablecontent', 'table-striped', 'commitments');
}


/**
   updateCellValue calls the PHP script that will update the database. 
 */
function updateCellValue(editableGrid, rowIndex, columnIndex, oldValue, newValue, row, onResponse)
{     
	//console.log(editableGrid.getValueAt(rowIndex, 0), newValue, editableGrid.getColumnName(columnIndex), editableGrid.getColumnType(columnIndex));
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
		    highlight(row.id, success ? "ok" : "error"); 
		},
		error: function(XMLHttpRequest, textStatus, exception) { alert("Ajax failure\n" + errortext); },
		async: true
	});
}

DatabaseGrid.prototype.deleteRow = function(id) 
{
  var self = this;
  var taskId = self.editableGrid.getValueAt(id, 2);
  var uniqueId = self.editableGrid.getValueAt(id, 0);

  if (confirm('Confirm deletion of task id #' + taskId )) {

        $.ajax({
		url: '../includes/commitment_delete.php',
		type: 'POST',
		dataType: "html",
		data: {
			uniqueid: uniqueId 
		},
		success: function (response) 
		{ 
			if (response == "ok" )
		        self.editableGrid.removeRow(id);
		},
		error: function(XMLHttpRequest, textStatus, exception) { alert("Ajax failure\n" + errortext); },
		async: true
	});     
  }
}; 


DatabaseGrid.prototype.addRow = function(id) 
{
	var self = this;
	var uniqueId = self.editableGrid.getValueAt(id, 0);
	var projectNumber = self.editableGrid.getValueAt(id, 1);
	
	console.log(id, uniqueId, projectNumber);  

        $.ajax({
		url: '../includes/commitment_add.php',
		type: 'POST',
		dataType: "html",
		data: {
			uniqueid: -1,
			projectnumber: projectNumber
		},
		success: function (response) 
		{ 
			if (response == "ok" ) {
   
                // hide form
                showAddForm();   
        		//$("#name").val('');
                //$("#firstname").val('');
			    
                alert("Row added : reload model");
                self.fetchGrid();
           	}
            else 
              alert("error");
		},
		error: function(XMLHttpRequest, textStatus, exception) { alert("Ajax failure\n" + errortext); },
		async: true
	});		
}; 


DatabaseGrid.prototype.duplicateRow = function(rowIndex) 
{
	// copy values from given row
	var values = this.getRowValues(rowIndex);
	//values['name'] = values['name'] + ' (copy)';

	// get id for new row (max id + 1)
	var newRowId = 0;
	for (var r = 0; r < this.getRowCount(); r++) newRowId = Math.max(newRowId, parseInt(this.getRowId(r)) + 1);
	
	// add new row
	this.insertAfter(rowIndex, newRowId, values); 
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
