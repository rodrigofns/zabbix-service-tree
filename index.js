// Main page, with top menu.

$(document).ready(function() {
	$.modalForm({ // global settings for modal popups
		labelCancel: 'Cancelar',
		titleIcon: 'include/zabbix16.png',
		titleColor: '#444'
	});

	if(localStorage.getItem('currentView') === null)
		localStorage.setItem('currentView', 'view_html5'); // HTML5 is the default view
	SetView(localStorage.getItem('currentView')); // set current view

	LoadLoginMenu();
	LoadServicesCombo();

	$(window).resize(FitContentDiv);
	$('ul#viewOptions li').click(onChangeView);
	$('select#refreshTime, select#serviceName').change(LoadApplet);
	$('#loginMenu').on('click', 'a#lnkLogin', onLoginClick);
	$('#loginMenu').on('click', 'a#lnkLogoff', onLogoffClick);
	$(window).trigger('resize'); // force
});

function FitContentDiv() {
	var border = 1;
	var cyMenu = $('div#menu').outerHeight();
	$('div#contents')
		.offset({ left:border, top:border + cyMenu })//.hide()
		.width(400).height(300) // avoid Java reload; resizing smalling than this will fail
		.width($(document).width() - border * 2 - 2)
		.height($(document).height() - border * 2 - cyMenu - 2);
		//.show();
	var fitInside = function(obj) {
		obj.width(400).height(300) // avoid Java reload; resizing smalling than this will fail
			.width($('div#contents').width() - 2)
			.height($('div#contents').height() - 2);
	};
	if($('iframe#h5tree').length) fitInside($('iframe#h5tree'));
	else if($('applet').length) fitInside($('applet'));
}

function LoadLoginMenu() {
	var xhr = $.post('ajaxLoginMenu.php');
	xhr.error(function(response) { // should be pretty rare...
		$('<span>Erro ao trazer o menu de login.<br/>' +
			response.status + ': ' + response.statusText + '<br/>' +
			response.responseText + '</span>'
		).modalForm({ title:'Oops...' });
	});
	xhr.success(function(html) {
		$('#loginMenu').html(html);
	});
}

function LoadServicesCombo() {
	var xhr = $.post('ajaxServiceTree.php', { root:1 });
	xhr.error(function(response) {
		$('<span>Erro ao trazer a lista de serviços.<br/>' +
			response.status + ': ' + response.statusText + '<br/>' +
			response.responseText + '</span>'
		).modalForm({ title:'Oops...' });
	});
	xhr.success(function(json) {
		var options = '';
		for(var i = 0; i < json.length; ++i)
			options += '<option value="' + json[i] + '">' + json[i] + '</option>';
		$('select#serviceName').append(options); // loads combo with available services
	});
}

function LoadApplet() {
	if($('select#serviceName').val() == '') return;
	var xhr = $.post('ajaxApplet.php', {
		viewType: localStorage.getItem('currentView'),
		refreshTime: $('select#refreshTime').val(),
		serviceName: $('select#serviceName').val()
	});
	xhr.success(function(bloco) {
		$('div#contents').html(bloco); // load applet HTML code
		FitContentDiv();
	});
}

function SetView(view) {
	$('ul#viewOptions li').not('[id=' + view + ']').removeClass('viewSelected').addClass('viewUnselected');
	$('li#' + view).removeClass('viewUnselected').addClass('viewSelected');
	localStorage.setItem('currentView', view);
}

function onChangeView(ev) {
	if(!$(this).hasClass('viewSelected')) {
		SetView($(this).attr('id'));
		LoadApplet();
	}
}

function onLoginClick(ev) {
	$('div#contents').hide(); // hide applet so it won't mess with the popup
	var showAppletBack = function() {
		$('div#contents').show(); // show applet back
		if($('iframe#h5tree').length) // if HTML5 tree is loaded, refresh it
			$('iframe#h5tree')[0].contentWindow.location.reload(true);
	};
	var dlgLogin = ShowLogin(ev);
	dlgLogin.ok(function() { // login successful
		LoadLoginMenu();
		showAppletBack();
	});
	dlgLogin.cancel(showAppletBack); // user cancelled login
	return false;
}

function onLogoffClick(ev) {
	$('div#contents').hide(); // hide applet so it won't mess with the popup
	var showAppletBack = function() {
		$('div#contents').show(); // show applet back
		if($('iframe#h5tree').length) // if HTML5 tree is loaded, refresh it
			$('iframe#h5tree')[0].contentWindow.location.reload(true);
	};

	var dlgLogoff = $('<span>Deseja encerrar a sessão do usuário <b>' + $('span#userName').text() + '</b>?</span>')
		.modalForm({ hasCancel:true, event:ev, title:'Logoff' });

	dlgLogoff.validateSubmit(function() {
		var xhr = $.post('ajaxLogin.php', { logoff:true });
		xhr.error(function(response) { // logoff failed (should be pretty rare...)
			$('<span>Não foi possível efetuar logoff.<br/>' +
				response.status + ': ' + response.statusText + '<br/>' +
				response.responseText + '</span>'
			).modalForm({ title:'Oops...' }).ok(function() { dlgLogoff.abort(); });
		});
		xhr.success(function(data) {
			dlgLogoff.continueSubmit();
		});
	});

	dlgLogoff.ok(function() { // logoff successful
		LoadLoginMenu();
		showAppletBack();
	});

	dlgLogoff.cancel(showAppletBack); // user cancelled logoff
	return false;
}