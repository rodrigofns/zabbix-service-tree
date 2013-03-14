// Tree graph JavaScript.

$(document).ready(function() {
	$.modalForm({ // global settings for modal popups
		labelCancel: 'Cancelar',
		titleIcon: '../include/zabbix16.png',
		titleColor: '#444'
	});

	var treeObj = null;
	$(window).resize(function() { ResizeDivs(treeObj); }).trigger('resize');
	$('a#collapse').hide(); // link to collapse all nodes
	treeObj = TreeGraph('treePlot'); // instantiate tree object
	treeObj.ctrlClick(function(nodeObj, ev) { // Ctrl+click on node
		NodeCtrlClick(treeObj, nodeObj);
	});
	//~ treeObj.onNoChildren(function(node) {
		//~ $('<span>Não há nós-filho a serem expandidos em:<br/>"' + node.data.fullname + '"<br/>' +
			//~ 'Se você pretendia editar o serviço, use Ctrl+clique.</span>'
		//~ ).modalForm({ title:'Oops...' });
	//~ });

	// Load the tree and set the timer to update it.
	RefreshTree(treeObj);
	setInterval(function() { RefreshTree(treeObj); },
		parseInt($('input#refreshTime').val()) * 1000);

	$('a#collapse').click(function(ev) { // link to collapse all nodes
		treeObj.collapseAll();
		this.blur();
		return false;
	});
});

function ResizeDivs(treeObj) {
	// Arrange all elements to fit the entire page.
	var border = 0;
	$('canvas#treePlot').hide().offset({ left:border, top:border })
		.attr('width', ($(document).width() - border * 2))
		.attr('height', Math.max($(document).height(), $(window).height()) - border * 2)
		.show();
	if(treeObj !== null) treeObj.redraw();
	$('div#toolbox').css({
		top: (Math.max($(document).height(), $(window).height())
			- $('div#toolbox').outerHeight() - 4) + 'px'
	});
	$('div#ctrlclickbox').css({
		top: ($('div#toolbox').offset().top - 16) + 'px'
	});
}

function RefreshTree(treeObj) {
	var xhr = $.post('../ajaxServiceTree.php', { serviceName:$('input#serviceName').val() });
	xhr.error(function(response) {
		$('<span>Erro ao trazer a árvore de serviço.<br/>' +
			response.status + ': ' + response.statusText + '<br/>' +
			response.responseText + '</span>'
		).modalForm({ title:'Oops...' });
	});
	xhr.success(function(data) {
		if($('div#loading').length) { // "loading" animated GIF still there, so it's the first loading
			$('div#loading').remove();
			$('div#treePlot').show(); // tree DIV was initially hidden
			$('a#collapse').show(); // link to collapse all nodes
		}
		treeObj.load(data.tree); // data = { tree,statusCount }
		$('span#numNodes').html(treeObj.countNodes());
		for(var i = 0; i < data.statusCount.length; ++i) // fill counts for colors
			$('span#numStatus' + i).html(data.statusCount[i]);
	});
}

function NodeCtrlClick(treeObj, nodeObj) {
	if($('input#hash').val() == '') {
		$('<span>É necessário efetuar login para editar um serviço.</span>')
			.modalForm({ title:'Sem acesso' });
	} else {
		var curServiceName = $('input#serviceName').val(); // that's an input/hidden
		var dlgEditService = ShowEditService(nodeObj, curServiceName); // dialogEditService.php
		dlgEditService.ok(function(retNode) {
			console.log(retNode);
			var xhr = $.post('ajaxService.php', { save:1, data:retNode });
			xhr.error(function(response) {
				$('<span>Erro ao salvar a árvore de serviço.<br/>' +
					response.status + ': ' + response.statusText + '<br/>' +
					response.responseText + '</span>'
				).modalForm({ title:'Oops...' });
			});
			xhr.success(function(data) {
				RefreshTree(treeObj);
			});
		});
	}
}