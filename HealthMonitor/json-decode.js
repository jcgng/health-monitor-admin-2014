var JsonInfo = {
	_dataReceivedCallback : null,
	_xmlHttp : null,
	_serverAddress : "http://localhost/HealthMonitor/get-health.php",
	_data : null
};

JsonInfo.initialize = function () {
	if(this._xmlHttp) {
		this._xmlHttp.destroy();
		this._xmlHttp = null;
	}
};

JsonInfo.fetchInfoAsync = function(idBoards,idPatients,user,password) {
	if(this._xmlHttp == null) {
		this._xmlHttp = new XMLHttpRequest();
	}

	if(this._xmlHttp) {
		this._xmlHttp.onreadystatechange = function() {
			if(JsonInfo._xmlHttp.readyState == 4 && JsonInfo._xmlHttp.status == 200) {
				JsonInfo.createList();
			}
		};     
		this._xmlHttp.open("GET", this._serverAddress+"?board="+idBoards+"&patient="+idPatients+"&user="+user+"&pass="+password, true);
		this._xmlHttp.send(null);
	}
};

JsonInfo.fetchInfoSync = function(idBoards,idPatients,user,password) {
	if(this._xmlHttp == null) {
		this._xmlHttp = new XMLHttpRequest();
	}

	if(this._xmlHttp) {
		this._xmlHttp.open("GET", this._serverAddress+"?board="+idBoards+"&patient="+idPatients+"&user="+user+"&pass="+password, false);
		this._xmlHttp.send(null);
		this.createList();
	}
};

JsonInfo.createList = function() {
	var response = JSON.parse(this._xmlHttp.responseText);
	if(response) {
		this._data = response;
	}       
	if (this._dataReceivedCallback) {
		this._dataReceivedCallback(); /* Notify all data is received and stored */
	}
};

JsonInfo.printHtml = function(div) {
	var resultDiv = document.getElementById(div);
	var html = '<ul>';
	for(var li in this._data) {
	    html += '<li>';
	    var sep = '';
	    for(var col in this._data[li]) {
		html += sep + col + "=" + this._data[li][col];
		sep = ',';
	    }
	    html += '</li>';
	}
	html += '</ul>';
	// and so on
	resultDiv.innerHTML = html;
};

JsonInfo.getBPM = function() {
	return this._data[0]['bpm'];
}

function getHealthSync() {
	JsonInfo.initialize();
	JsonInfo.fetchInfoSync("arduino1",1,"joao.guiomar","ola");
	JsonInfo.printHtml("results");
}