<div id="dialogChooseParent">
	<style type="text/css" scoped="scoped">
		div#dialogChooseParent { display:none; }
		div#chooseParentWnd {
			width:770px;
			height:330px;
			overflow-x:hidden;
			overflow-y:auto;
		}
		canvas#chooseParentTree {
		}
		div#chooseParentFooter { border-top:1px solid #DDD; }
		div.lite { color:#888; font-size:85%;  margin-bottom:3px; }
	</style>
	<div id="chooseParentWnd">
		<div id="chooseParentTreeLoading">Carregando <span id="serviceNameLoading"></span>...
			<img src="../include/loading.gif"/></div>
		<canvas id="chooseParentTree" width="760" height="320"></canvas><!-- tree will be loaded here -->
	</div>
	<div id="chooseParentFooter">
		<div class="lite">(Clique para expandir, Ctrl+clique para escolher)</div>
		<table width="100%">
			<tr><td>Serviço selecionado:
				<input type="text" readonly="readonly" size="30" value="" name="chooseParentNewName"/></td>
			<td style="text-align:right;"><a href="#" title="Recolhe todos os nós da árvore"
				id="chooseParentCollapseAll">recolher tudo</a></td></tr>
		</table>
		<input type="hidden" value="" name="chooseParentNewId"/>
	</div>
</div>

<script type="text/javascript">
var g_treeParent = null; // tree object, global in script, reloaded each time

$(document).ready(function() {
	g_treeParent = TreeGraph('chooseParentTree'); // instantiate once
	$('a#chooseParentCollapseAll').click(function() {
		if(g_treeParent !== null) g_treeParent.collapseAll();
		$(this).blur();
		return false;
	});
	//~ treeChooseParent.onNoChildren(function(node) {
		//~ $('<span>Não há nós-filho a serem expandidos em:<br/>' +
			//~ node.tooltip.substring(0, node.tooltip.indexOf('\n')) + '</span>'
		//~ ).modalForm({ title:'Oops...' }); // only 1st line from tooltip
	//~ });
});

function ShowChooseParent(rootName, serviceId, curParentServiceId) {
	var div = $('div#dialogChooseParent'); // pointer to DIV
	div.find('input[name=chooseParentNewName]').val('');
	div.find('input[name=chooseParentNewId]').val('');
	div.find('canvas#chooseParentTree').hide();
	div.find('div#chooseParentTreeLoading').show(); // show "loading" message

	var popup = div.modalForm({ hasCancel:true, title:'Trocar pai' });

	var events = {
		prepareNode: function prepareNode(node) {
			node.color =
				node.data.serviceid == serviceId || node.data.serviceid == curParentServiceId ?
				'rgba(212,212,212,0.7)' :
				'rgba(252,252,252,0.7)'; // all nodes white, cosmetic only
			for(var i = 0; i < node.children.length; ++i)
				prepareNode(node.children[i]);
		},

		ctrlClick: function(nodeObj) {
			div.find('input[name=chooseParentNewName]').val(nodeObj.data.fullname);
			div.find('input[name=chooseParentNewId]').val(nodeObj.data.serviceid);
		}
	};

	popup.ready(function() {
		var xhr = $.post('../ajaxServiceTree.php', { serviceName:rootName }); // load tree nodes
		xhr.error(function(response) {
			$('<span>Não foi possível trazer a árvore de serviço.<br/>' +
				response.status + ': ' + response.statusText + '<br/>' +
				response.responseText + '</span>'
			).modalForm({ title:'Oops...' }).ok(function() {
				popup.abort();
			});
		});
		xhr.success(function(data) {
			div.find('div#chooseParentTreeLoading').hide(); // remove "loading" message
			div.find('canvas#chooseParentTree').show();
			div.find('canvas#chooseParentTree')[0].width = div.find('canvas#chooseParentTree')[0].width; // clear canvas
			localStorage.removeItem('TreeGraph_chooseParentTree'); // avoid reload pos
			events.prepareNode(data.tree); // data.tree is the root node
			g_treeParent.ctrlClick(events.ctrlClick);
			g_treeParent.load(data.tree);
		});
	});

	popup.validateSubmit(function() {
		var newId = div.find('input[name=chooseParentNewId]').val();
		var newName = div.find('input[name=chooseParentNewName]').val();
		if(newId == '') {
			$('<span>É necessário selecionar um serviço (use Ctrl+clique).</span>')
				.modalForm({ title:'Oops...' });
		} else if(newId == curParentServiceId) {
			$('<span>O serviço "' + newName + '" já é o pai atual.</span>')
				.modalForm({ title:'Oops...' });
		} else {
			popup.continueSubmit({ id:newId, name:newName }); // ok() event will receive object
		}
	});

	return popup; // can call ok() event
}
</script>