/**
 * Description:
 * 		JavaScript (jQuery) functions and scripts
 * 
 * @author joao.guiomar
 */

/**
 * Show/Hide element with id
 *  
 * @param {String} id
 * @param {DOM} ctrl
 */
function checkShowHide(id,ctrl) {
	document.getElementById(id).style.display = (ctrl.checked) ? "":"none";
}

/**
 * Show/Hide elements id1 and id2
 * 
 * @param {String} id1
 * @param {String} id1
 * @param {DOM} ctrl
 */
function checkShowHide2(id1,id2,ctrl) {
	document.getElementById(id2).style.display = (ctrl.checked) ? "none":"";
	document.getElementById(id1).style.display = (ctrl.checked) ? "":"none";
}

/**
 * Create div when button is pressed
 * 
 * @param {String} divName
 * @param {Int} elementAdded
 */

var counter = 0;
function addInput(divName,elementAdded){
	var newdiv = document.createElement('div');
	newdiv.innerHTML = (counter + 1) + elementAdded;
    document.getElementById(divName).appendChild(newdiv);
    counter++;
}

var counter = 0;
function moreFields() {
	counter++;
	var newFields = document.getElementById('readroot').cloneNode(true);
	newFields.id = '';
	newFields.style.display = 'block';
	var newField = newFields.childNodes;
	for (var i=0;i<newField.length;i++) {
		var theName = newField[i].name
		if (theName)
			newField[i].name = theName + counter;
	}
	var insertHere = document.getElementById('writeroot');
	insertHere.parentNode.insertBefore(newFields,insertHere);
}

/**
 * Open Link with combo-boxes
 * 
 * @param {String} id
 */
function openUrl(id) {
	window.location=document.getElementById(id).value;
}

/**
 * Limit text counter
 * 
 * @param {DOM} limitField
 * @param {DOM} limitCount
 * @param {Int} limitNum
 * @param {Boolean} checkURL
 */
function limitText(limitField,limitCount,limitNum,checkURL) {
	var length = limitField.value.length;
	if(checkURL) {
		var fieldArray = limitField.value.split(" ");
		for(var i=0;i<fieldArray.length;i++) {
			if(new RegExp("([a-zA-Z0-9]+://)?([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?").test(fieldArray[i])) {
				length = length - fieldArray[i].length + 22;
			}
		}
	}
	if (length > limitNum) {
		limitField.style.borderColor = "red";
		limitCount.value = limitNum - length;
	} else {
		limitField.style.borderColor = "";
		limitCount.value = limitNum - length;
	}
}

/**
 * Insert date
 */
function insertNewsDate() {
	var months = ['January','February','March','April','May','June','July',
	              'August','September','October','November','December'];       
	var current = new Date();
	document.getElementById("news_date").value = months[current.getMonth()] + " " + current.getDate()+ ", " + current.getFullYear();
}
function insertScheduleDate() {
	var current = new Date();
	var month = ((current.getMonth()+1)<10?"0"+(current.getMonth()+1):(current.getMonth()+1));
	var day = (current.getDate()<10?"0"+current.getDate():current.getDate());
	document.getElementById("schedule_date").value = current.getFullYear()+"-"+month+"-"+day;
}
function insertStartDate() {
	var current = new Date();
	var month = ((current.getMonth()+1)<10?"0"+(current.getMonth()+1):(current.getMonth()+1));
	var day = (current.getDate()<10?"0"+current.getDate():current.getDate());
	document.getElementById("start_date").value = current.getFullYear()+"-"+month+"-"+day;
}
function insertEndDate() {
	var current = new Date();
	var month = ((current.getMonth()+1)<10?"0"+(current.getMonth()+1):(current.getMonth()+1));
	var day = (current.getDate()<10?"0"+current.getDate():current.getDate());
	document.getElementById("end_date").value = current.getFullYear()+"-"+month+"-"+day;
}

/**
 * Set popup window size
 *  
 * @param {String} w
 * @param {String} h
 */
var width = "500";
var height = "250";
function setPopupWindowSize(w,h) {
	width = w;
	height = h;
}

/**
 * Popup window and redirect form submit
 * 
 * @param {DOM} myForm
 * @returns {Boolean}
 */
var counter = 1;
function redirectOutput(myForm) {
	var w = window.open('about:blank','Popup_Window_' + counter,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,copyhistory=no,width='+width+',height='+height+',top=20,left=100');
	myForm.target = 'Popup_Window_' + counter;
	counter++;
	return true;
}

/**
 * Check if Warning Images Exist
 * 
 * @returns {Boolean}
 */
function warningExits() {
	var divs = document.getElementsByTagName("img");
	for(var i=0;i<divs.length;i++) {
		if(divs[i].id.indexOf("Warning")==0) {
			return true;
		}
	}
	return false;
}

/**
 * Disable submit button if Warning exists
 * 
 * @param {String} buttonId The submit button ID
 */
function checkWarnings(buttonId) {
	var warnings = warningExits();
	var inputs = document.getElementsByTagName("input");
	for(var i=0;i<inputs.length;i++) {
		if((inputs[i].type=='submit') && (inputs[i].id==buttonId)) {
			if(warnings) {
				inputs[i].disabled = true;
			} else {
				inputs[i].disabled = false;
			}
		}
	}
}

/** jQuery **/

/**
 * Check if element exits
 * 
 * @param {String} id
 * @returns {Boolean}
 */
function elementExists(id) {
	if($("#"+id).length == 0) {
		return false;
	} else {
		return true;
	}
}

/**
 * Password strength indicator
 * 
 * @param {Int} strength
 */
function updateStrength(strength){
    var status = new Array('short', 'bad', 'good', 'strong', 'mismatch');
    var dom = jQuery("#pass-strength-indicator");
    switch(strength){
	    case 1:
	      	dom.removeClass().addClass(status[0]).text('Too Short');
	      	break;
	    case 2:
	    	dom.removeClass().addClass(status[1]).text("Bad Password");
	      	break;
	    case 3:
	      	dom.removeClass().addClass(status[2]).text("Good Password");
	      	break;
	    case 4:
	     	dom.removeClass().addClass(status[3]).text("Strong Password");
	      	break;
	    case 5:
	      	dom.removeClass().addClass(status[4]).text("Mismatch");
	      	break;
	    default:
	      	break;
    }
}

/** Google Charts API **/

/**
 * Set data for the list of charts to be
 * created
 */
var chartList = [ ];
function setChartData(type,json,options,chartDiv,dashboardDiv,controlDiv) {
	var chart = {};
	chart.type = type;
	chart.json = json;
	chart.options = options;
	chart.chartDiv = chartDiv;
	chart.dashboardDiv = dashboardDiv;
	chart.controlDiv = controlDiv;
	chartList.push(chart);
}

/**
 * Function to load the necessary packages and
 * set the drawCharts callback
 */
function createCharts() {
	var packages = [ ];
	var total = chartList.length;
	for(var index=0;index<total;index++) {
		switch(chartList[index].type) {
			case "areaChart":
			case "barChart":
			case "pieChart":
			case 'comboChart':
			case "lineChart":
				// Load the Visualization API and the piechart package.
//				google.load('visualization', '1', {'packages':['corechart']});
				packages[index] = 'corechart';
				break;
			case "timeLine":
				// Load the Visualization API and the timeline package.
//				google.load('visualization', '1', {'packages':['annotatedtimeline']});
				packages[index] = 'annotatedtimeline';
				break;
			case "treeMap":
//				google.load('visualization', '1', {'packages': ['treemap']});
				packages[index] = 'treemap';
				break;
		}		
	}
	packages[total] = 'controls';
	google.load('visualization', '1', {'packages': packages});
	// Set a callback to run when the Google Visualization API is loaded.
	google.setOnLoadCallback(drawCharts);
}

/**
 * Callback that creates and populates a data table,
 * instantiates the charts, passes in the data and
 * draws it.
 */ 
function drawCharts() {
	var total = chartList.length;
	for(var index=0;index<total;index++) {
		// Create our data table out of JSON string.
		var data = new google.visualization.DataTable(chartList[index].json,0.5);    
		// Instantiate and draw our chart, passing in some options.
		var chart = null;
		var dashboard = null;
		var control = null;
		switch(chartList[index].type) {
			case "barChart":
				chart = new google.visualization.BarChart(document.getElementById(chartList[index].chartDiv));
				break;
			case "pieChart":
				chart = new google.visualization.PieChart(document.getElementById(chartList[index].chartDiv));
				break;
			case "timeLine":
				chart = new google.visualization.AnnotatedTimeLine(document.getElementById(chartList[index].chartDiv));
				break;
			case "areaChart":
				if((chartList[index].dashboardDiv==null) || (chartList[index].controlDiv==null)) {
					chart = new google.visualization.AreaChart(document.getElementById(chartList[index].chartDiv));
				} else {
					dashboard = new google.visualization.Dashboard(document.getElementById(chartList[index].dashboardDiv));
					control = new google.visualization.ControlWrapper({
						     'controlType': 'ChartRangeFilter',
						     'containerId': chartList[index].controlDiv,
						     'options': {
						       // Filter by the date axis.
						       'filterColumnIndex': 0,
						       'ui': {
						         'chartType': 'LineChart',
						         'chartOptions': {
						           'chartArea': {'width': '90%'},
						           'hAxis': {'baselineColor': 'none'}
						         },
						         'chartView': {
						           'columns': [0, 1]
						         }
						       }
						     }
						   });
	
						chart = new google.visualization.ChartWrapper({
						     'chartType': 'AreaChart',
						     'containerId': chartList[index].chartDiv,
						     'options': {
						       // Use the same chart area width as the control for axis alignment.
						       'chartArea': {'height': '80%', 'width': '90%'},
						       'hAxis': {'slantedText': false},
						       'vAxis': {'viewWindow':{'min':0}},
						       'legend': {'textStyle': {'fontSize': 9}}
						     }
						   });
				}
				break;
			case "treeMap":
				chart = new google.visualization.TreeMap(document.getElementById(chartList[index].chartDiv));
				break;
			case "comboChart":
				if((chartList[index].dashboardDiv==null) || (chartList[index].controlDiv==null)) {
					chart = new google.visualization.ComboChart(document.getElementById(chartList[index].chartDiv));
				} else {
					dashboard = new google.visualization.Dashboard(document.getElementById(chartList[index].dashboardDiv));
					control = new google.visualization.ControlWrapper({
						     'controlType': 'ChartRangeFilter',
						     'containerId': chartList[index].controlDiv,
						     'options': {
						       // Filter by the date axis.
						       'filterColumnIndex': 0,
						       'ui': {
						         'chartType': 'LineChart',
						         'chartOptions': {
						           'chartArea': {'width': '80%'},
						           'hAxis': {'baselineColor': 'none'}
						         },
						         'chartView': {
						           'columns': [0, 1]
						         }
						       }
						     }
						   });
	
						chart = new google.visualization.ChartWrapper({
						     'chartType': 'ComboChart',
						     'containerId': chartList[index].chartDiv,
						     'options': {
						    	 'seriesType' : 'bars',
						    	 // Use the same chart area width as the control for axis alignment.
						    	 'chartArea': {'height': '80%', 'width': '80%'},
						    	 'hAxis': {'slantedText': false},
						    	 'vAxis': {'viewWindow':{'min':0}},
						       	 'legend': {'textStyle': {'fontSize': 9}}
						     }
						   });
				}
				break;
			case "lineChart":
				if((chartList[index].dashboardDiv==null) || (chartList[index].controlDiv==null)) {
					chart = new google.visualization.LineChart(document.getElementById(chartList[index].chartDiv));
				} else {
					dashboard = new google.visualization.Dashboard(document.getElementById(chartList[index].dashboardDiv));
					control = new google.visualization.ControlWrapper({
						     'controlType': 'ChartRangeFilter',
						     'containerId': chartList[index].controlDiv,
						     'options': {
						       // Filter by the date axis.
						       'filterColumnIndex': 0,
						       'ui': {
						         'chartType': 'LineChart',
						         'chartOptions': {
						           'chartArea': {'width': '80%'},
						           'hAxis': {'baselineColor': 'none'}
						         },
						         'chartView': {
						           'columns': [0, 1]
						         }
						       }
						     }
						   });
	
						chart = new google.visualization.ChartWrapper({
						     'chartType': 'LineChart',
						     'containerId': chartList[index].chartDiv,
						     'options': {
						       // Use the same chart area width as the control for axis alignment.
						       'chartArea': {'height': '80%', 'width': '80%'},
						       'hAxis': {'slantedText': false},
						       'vAxis': {'viewWindow':{'min':0}},
						       'legend': {'textStyle': {'fontSize': 9}}
						     }
						   });
				}
				break;
		}
		if(dashboard!=null) {
			dashboard.bind(control,chart);
	        dashboard.draw(data);
		} else if(chart!=null) {
			chart.draw(data,chartList[index].options);
		}
	}
}