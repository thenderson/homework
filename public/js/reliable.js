var statuses = {'O':'open', 'C0':'complete - improvised', 'C1':'complete', 'C2':'complete - anticipated', 'CL':'complete - closed late',
	'V1':'variance - time', 'V2':'variance - waiting, internal', 'V3':'variance - waiting, external', 'V4':'variance - COS', 
	'V5':'variance - superseded, internal', 'V6':'variance - superseded, external', 'V7':'variance - forgot', 'V8':'variance - not needed', 
	'V9':'variance - tech failure', 'D':'deferred', '?':'unknown', 'NA':'n/a'};

function highlightRow(rowId, bgColor, after)
{
	var rowSelector = $("#grid_" + rowId);
	rowSelector.css("background-color", bgColor);
	rowSelector.fadeTo("fast", 0.5, function() { 
		rowSelector.fadeTo("fast", 1, function() { 
			rowSelector.css("background-color", '');
		});
	});
}

function highlight(div_id, style) {
	highlightRow(div_id, style == "error" ? "#e5afaf" : style == "warning" ? "#ffcc00" : "#8dc70a");
}
   

function CommitmentGrid(name) 
{ 	
	var self = this;
	this.name = name;
	
	$.datepicker.setDefaults({
		dateFormat: $.datepicker.W3C,
		numberOfMonths: 2,
		gotoCurrent: true,
		showAnim: 'puff'
	});
	
	self.grid = new EditableGrid('grid', {
		enableSort: true,
		dateFormat: $.datepicker.W3C,
      	pageSize: getCookie(self.name+'PageSize') || 10,
		editmode: 'absolute',

        tableRendered:  function() { 
			// activate tooltips onto rendered grid
			$('th.editablegrid-priority_h').attr('title', 'high priority.').attr('data-placement', 'left').attr('data-container', 'body').tooltip();

			$('th.editablegrid-is_closed').attr('title', 'close a completed commitment').attr('data-placement', 'left').attr('data-container', 'body').tooltip();	

			$('th.editablegrid-status').attr('title', 
				'<strong>O:</strong> Open commitment\n\
				<strong>C0:</strong> Closed, improvised\n\
				<strong>C1:</strong> Closed, 1 week plan\n\
				<strong>C2:</strong> Closed, anticipated\n\
				<strong>D:</strong> Deferred indefinitely\n\
				<strong>V*:</strong> Variance for plan failure.\n\
				<strong>?:</strong> Status unknown').attr('data-placement', 'left').attr('data-container', 'body').tooltip({html: true}).tooltip();
			
			$('th.editablegrid-actions').attr('title', 'Duplicate / Delete').attr('data-placement', 'left').attr('data-container', 'body').tooltip();

			updatePaginator(self.grid, self.name+'_paginator'); 
		},
		tableLoaded: function() { 

			var closed_col = self.grid.getColumnIndex('is_closed');
			//var desc_col = self.grid.getColumnIndex('description');
			var priority_col = self.grid.getColumnIndex('priority_h');
			
			if (this.hasColumn('project_number')) {
				this.setCellRenderer('project_number', new CellRenderer({ 
					render: function(cell, value) { 
						cell.innerHTML= "<a title=\"go to project page\" href=\"#\" onclick=\"goto_project_view(\'"+value+"\'); return false;\">"+value+"</a>";
					}}));
			};
					
			this.setCellRenderer('actions', new CellRenderer({
				render: function(cell, id) { 
					cell.innerHTML+= "<i onclick=\""+self.name+".DuplicateRow("+cell.rowIndex+");\" class='fa fa-files-o' >&nbsp;</i>";
					cell.innerHTML+= "<i onclick=\"ConfirmDeleteRow("+cell.rowIndex+");\" class='fa fa-minus-square-o' ></i>";
				}}));
			
			this.setCellRenderer('due_by', new CellRenderer({ //shades cells based on how soon commitment is due
				render: function(cell, value) {
					date_due=moment(value, 'YYYY-MM-DD')
					cell.innerHTML=date_due.format("\'YY.MM.DD");
					row=self.grid.getRow(cell.rowIndex);
					status=self.grid.getValueAt(cell.rowIndex, closed_col);
					how_soon=date_due.diff(moment(),'days');
					if (status == 'true') {
						$(row).addClass('closed');
					}
					else {
						due_class = how_soon < -7 ? 'overdue_2w' : (how_soon < 0 ? 'overdue_1w' : (how_soon < 8 ? 'due_nextweek' : 'due_future'));
						$(cell).addClass(due_class);
						$(row).removeClass('closed');
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
				}}));
			
			// this.setCellRenderer('status', new CellRenderer ({ //overdue rows
				// render: function(cell, value) {
					// if (value == 'V?' || value == '?') {
						// $(row).addClass('status_me_now');
					// }
				// }
			// }));
			
			this.setEnumProvider('status', new EnumProvider({
				getOptionValuesForEdit: function (grid, column, rowIndex) {
					var status = editableGrid.getValueAt(rowIndex, column);
					console.log('status: '+status);
					if (status == 'O') return { 'O' : 'open', 'C' : 'close', 'D':'defer', '?':'unknown'};
					else if (status == 'V?') return { 'V1':'variance - time','V2':'variance - waiting, internal', 
						'V3':'variance - waiting, external','V4':'variance - COS', 
						'V5':'variance - superseded, internal','V6':'variance - superseded, external', 
						'V7':'variance - forgot','V8':'variance - not needed','V9':'variance - tech failure','V?':'variance - unknown'};
					return status;
				}
			}));
			
			this.renderGrid(self.name, 'table', self.name); 
			$('[id^='+self.name+'_total]').html('total: <strong>'+self.grid.getTotalRowCount()+'</strong>');
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
		dataType: "json",
		data: {
			uniqueid: grid.getValueAt(rowIndex, 0), 
			projectnumber: grid.getValueAt(rowIndex, 1),
			newvalue: newValue, 
			colname: grid.getColumnName(columnIndex),
			date_due: grid.getValueAt(rowIndex, 6)
		},
		success: function (response) 
		{ 
			// reset old value if failed then highlight row
			if (response == 'error') {
				grid.setValueAt(rowIndex, columnIndex, oldValue);
				highlight(rowId, "error"); 
			}
			else {
				values = response[0];
				$.each(values, function(key, value) {
					columnIndex = grid.getColumnIndex(key);
					if (columnIndex != -1) grid.setValueAt(rowIndex, columnIndex, value);
				});
				highlight(rowId, "ok");
				console.log('Test modify: [id^='+self.name+'_total] = '+self.grid.getTotalRowCount());
				$('[id^='+self.name+'_total]').html('total: <strong>'+self.grid.getTotalRowCount()+'</strong>');
			};
		},
		error: function(XMLHttpRequest, textStatus, exception) { 
			highlight(rowId, "error");
			alert("Ajax failure\n" + XMLHttpRequest + "\n Textstatus: " + textStatus + "\n Exception:" + exception);
		},
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
			console.log('Test deleterow: [id^='+self.name+'_total] = '+self.grid.getTotalRowCount());
			$('[id^='+self.name+'_total]').html('total: <strong>'+self.grid.getTotalRowCount()+'</strong>');
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
			projectnumber: values['projectnumber'],
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
			console.log('Test Addrow: [id^='+self.name+'_total] = '+self.grid.getTotalRowCount());
			$('[id^='+self.name+'_total]').html('total: <strong>'+self.grid.getTotalRowCount()+'</strong>');
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
			console.log('Test duplicate: [id^='+self.name+'_total] = '+self.getTotalRowCount());
			$('[id^='+self.name+'_total]').html('total: <strong>'+self.getTotalRowCount()+'</strong>');
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

function populate_select_names(element, objects) {
	$.each(objects, function(key, object) {
		$(element).append($("<option>").attr('value',object['user_id']).text(object['name']));
	});
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
	console.log('changepagesize: cookie '+cookiename+' value '+this.pageSize);
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