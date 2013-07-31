// Main page JavaScript file.

$(document).ready(function() {
	$.modalForm({ // global settings for modal popups
		labelOk: I('OK'),
		labelCancel: I('Cancel'),
		titleIcon: 'img/zabbix16.png',
		titleColor: '#444',
		disableF5: true
	});

	$('#treeLoading').hide(); // animated "loading" GIF
	$('#toolbox').hide(); // node counters
	$('#statusTxt').hide(); // datetime of last update
	LoadLoginMenu();
	LoadServicesCombo();

	var timerObj = { id:null };
	var treeObj = TreeGraph('treePlot'); // instantiate tree object
	treeObj.ctrlClick(function(nodeObj, ev) { NodeCtrlClick(timerObj, treeObj, nodeObj); }); // Ctrl+click on node

	$(window).resize(function() { treeObj.fitParentContainer(); }).trigger('resize');
	$('select#refreshTime').val('180'); // default is 3 min
	$('select#refreshTime, select#serviceName').change(function() { RefreshTree(timerObj, treeObj); });
	$('#loginMenu').on('click', 'a#lnkLogin', OnLoginClick);
	$('#loginMenu').on('click', 'a#lnkLogoff', OnLogoffClick);
	$('a#collapse').click(function() { treeObj.collapseAll(); this.blur(); return false; });
});

function LoadLoginMenu() {
	var xhr = $.post('ajaxLoginMenu.php');
	xhr.fail(function(response) { // should be pretty rare...
		$('<span>'+I('Failed to load login menu')+'.<br/>' +
			response.status+': '+response.statusText+'<br/>' +
			response.responseText+'</span>'
		).modalForm({ title:I('Oops...') });
	});
	xhr.done(function(html) {
		$('#loginMenu').html(html);
	});
}

function LoadServicesCombo() {
	var xhr = $.post('ajaxServiceTree.php', { root:1 });
	xhr.fail(function(response) {
		$('<span>'+I('Failed to load services list')+'.<br/>' +
			response.status+': '+response.statusText+'<br/>' +
			response.responseText+'</span>'
		).modalForm({ title:I('Oops...') });
	});
	xhr.done(function(json) {
		var options = '';
		for(var i = 0; i < json.length; ++i)
			options += '<option value="'+json[i]+'">'+json[i]+'</option>';
		$('select#serviceName').append(options); // loads combo with available services
	});
}

function OnLoginClick(ev) {
	var dlgLogin = ShowLogin(ev);
	dlgLogin.ok(function() { // login successful
		LoadLoginMenu();
	});
	dlgLogin.cancel(function() { // user cancelled login
	});
	return false;
}

function OnLogoffClick(ev) {
	var dlgLogoff = $('<span>'+I('Terminate session for current user?')+'</span>')
		.modalForm({ hasCancel:true, event:ev, title:'Logoff' });
	dlgLogoff.validateSubmit(function() {
		var xhr = $.post('ajaxLogin.php', { logoff:true });
		xhr.fail(function(response) { // logoff failed (should be pretty rare...)
			$('<span>'+I('Failed to log off')+'.<br/>' +
				response.status+': '+response.statusText+'<br/>' +
				response.responseText+'</span>'
			).modalForm({ title:I('Oops...') }).ok(function() { dlgLogoff.abort(); });
		});
		xhr.done(function(data) {
			dlgLogoff.continueSubmit();
		});
	});
	dlgLogoff.ok(function() { // logoff successful
		LoadLoginMenu();
	});
	dlgLogoff.cancel(function() { // user cancelled logoff
	});
	return false;
}

function NodeCtrlClick(timerObj, treeObj, nodeObj) {
	if($('#userName').length == 0) {
		$('<span>'+I('You must be logged in to edit a service')+'.</span>')
			.modalForm({ title:I('No access') });
	} else {
		if(timerObj.id !== null) {
			clearTimeout(timerObj.id); // stop any pending execution
			timerObj.id = null;
		}
		var curServiceName = $('select#serviceName').val();
		var dlgEditService = ShowEditService(nodeObj, curServiceName); // dialogEditService.php
		dlgEditService.ok(function(retNode) {
			var xhr = $.post('ajaxService.php', { save:1, data:retNode });
			xhr.fail(function(response) {
				$('<span>'+I('Failed to save the service tree')+'.<br/>' +
					response.status+': '+response.statusText+'<br/>' +
					response.responseText+'</span>'
				).modalForm({ title:I('Oops...') });
			});
			xhr.done(function(data) {
				RefreshTree(timerObj, treeObj);
			});
		});
		dlgEditService.cancel(function() {
			RefreshTree(timerObj, treeObj);
		});
	}
}

function RefreshTree(timerObj, treeObj) {
	if($('select#serviceName').val() == '') // no service currently selected
		return;
	if($('select#serviceName option:first').val() == '')
		$('select#serviceName option:first').remove(); // remove the first empty option, useless
	if(timerObj.id !== null)
		clearTimeout(timerObj.id); // stop any pending execution
	(function ReloadTreeData() {
		$('#treePlot').hide();
		$('#toolbox').hide().css({ bottom:($(document).height() / 2 + 10)+'px' }); // prepare toolbox to animate
		$('select#refreshTime, select#serviceName').attr('disabled', 'disabled');
		$('#treeLoading').show().css({ top:0 }).animate({ top:'50%' }, 400, function() { // loading GIF comes up
			var xhr = $.post('ajaxServiceTree.php', { serviceName:$('select#serviceName').val() });
			xhr.fail(function(response) {
				$('#treePlot').show();
				$('<span>'+I('Failed to load the service tree')+'.<br/>' +
					response.status+': '+response.statusText+'<br/>' +
					response.responseText+'</span>'
				).modalForm({ title:I('Oops...') });
			});
			xhr.done(function(data) {
				treeObj.clear();
				$('#treePlot').show();
				treeObj.load(data.tree); // data = { tree,statusCount }
				$('span#numNodes').html(treeObj.countNodes());
				for(var i = 0; i < data.statusCount.length; ++i) // fill counts for colors
					$('span#numStatus' + i).html(data.statusCount[i]);
				timerObj.id = setTimeout(ReloadTreeData, parseInt($('select#refreshTime').val()) * 1000); // reload timer
			});
			xhr.always(function() {
				$('#statusTxt').show();
				$('#lastUpdate').text($.format.date((new Date()).getTime(), 'yyyy-MM-dd HH:mm:ss'));
				$('#toolbox').show().animate({ bottom:'10px' }, 250); // toolbox to its position at page bottom
				$('#treeLoading').animate({ top:'110%' }, 300, function() { // loading GIF goes page down
					$('#treeLoading').hide();
					$('select#refreshTime, select#serviceName').removeAttr('disabled');
				});
			});
		});
	})();
}