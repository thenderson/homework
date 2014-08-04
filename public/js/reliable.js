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
        
/**
   updateCellValue calls the PHP script that will update the database. 
 */
function updateCellValue(editableGrid, rowIndex, columnIndex, oldValue, newValue, row, onResponse)
{      
	$.ajax({
		url: '../includes/commitment_update.php',
		type: 'POST',
		dataType: "html",
		data: {
			tablename : editableGrid.name,
			id: editableGrid.getRowId(rowIndex), 
			newvalue: editableGrid.getColumnType(columnIndex) == "boolean" ? (newValue ? 1 : 0) : newValue, 
			colname: editableGrid.getColumnName(columnIndex),
			coltype: editableGrid.getColumnType(columnIndex)			
		},
		success: function (response) 
		{ 
			// reset old value if failed then highlight row
			var success = onResponse ? onResponse(response) : (response == "ok" || !isNaN(parseInt(response))); // by default, a sucessfull reponse can be "ok" or a database id 
			if (!success) editableGrid.setValueAt(rowIndex, columnIndex, oldValue);
		    highlight(row.id, success ? "ok" : "error"); 
		},
		error: function(XMLHttpRequest, textStatus, exception) { alert("Ajax failure\n" + errortext); },
		async: true
	});
   
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
		}
	}));

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

	// renderers for the action column
	
	grid.setCellRenderer('actions', new CellRenderer({ 
		render: function(cell, value) {                 
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('actions');
		}
	}));
	
	grid.setCellRenderer('actions', new CellRenderer({ 
		render: function(cell, id) { 
		    cell.innerHTML+= "<i onclick=\"datagrid.deleteRow("+id+");\" class='fa fa-trash-o' ></i>";
		}
	})); 
	
	grid.setHeaderRenderer('actions', new CellRenderer({
		render: function(cell, value) {
			CellRenderer.prototype.render.call(this, cell, value);
			$(cell).addClass('actions');
		}
	}));

	grid.renderGrid("tablecontent", "commitments");
}

DatabaseGrid.prototype.deleteRow = function(id) 
{

  var self = this;
  var taskId = self.editableGrid.getValueAt(id, 2);
  var uniqueId = self.editableGrid.getValueAt(id, 0);

  if ( confirm('Confirm deletion of row id ' + taskId )  ) {

        $.ajax({
		url: '../includes/commitment_delete.php',
		type: 'POST',
		dataType: "html",
		data: {
			unique_id: uniqueId 
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

        $.ajax({
		url: '../includes/commitment_add.php',
		type: 'POST',
		dataType: "html",
		data: {
			tablename : self.editableGrid.name,
			name:  $("#name").val(),
			firstname:  $("#firstname").val()
		},
		success: function (response) 
		{ 
			if (response == "ok" ) {
   
                // hide form
                showAddForm();   
        		$("#name").val('');
                $("#firstname").val('');
			    
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




  



