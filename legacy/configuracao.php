<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Arvore de Servico - Configuracao</title>
		<link rel="stylesheet" type="text/css" href="http://www.jeasyui.com/easyui/themes/default/easyui.css">
		<link rel="stylesheet" type="text/css" href="http://www.jeasyui.com/easyui/themes/icon.css">
		<link rel="stylesheet" type="text/css" href="index.css">

		<script type="text/javascript" src="arvore/js/jquery-1.8.2.min.js"></script>
		<script type="text/javascript" src="arvore/js/jquery.easyui.min.js"></script>

		<script>

			$(function(){
				$('#test').treegrid({
					title:'Arvore de Servicos - Configuracao',
					iconCls:'icon-save',
					width:1000,
					height:450,
					nowrap: false,
					rownumbers: true,
					animate:true,
					collapsible:true,
					//url:'http://10.200.118.68/zabbix/dev/arvore_teste/arvore/arvore_grafica/graph_tree_json.php',//'http://localhost/zabbix2/graph_tree_json.php',
					//url:'arvore/arvore_grafica/graph_tree_json.php',
					idField:'id',
					treeField:'name',

					columns:[[
							{field:'name',title:'Name',width:450},
							{field:'size',title:'Status calculation',width:200,rowspan:2},
							{field:'date',title:'Trigger',width:200,rowspan:2}
						]],
					onBeforeLoad:function(row,param){
						//if (row){
						//	$(this).treegrid('options').url = 'arvore/data/treegrid_subdata.json';
						//} else {
							$(this).treegrid('options').url = 'arvore/arvore_grafica/graph_tree_json.php';//'http://localhost/zabbix2/graph_tree_json.php';
						//}
					},
					onContextMenu: function(e,row){
						e.preventDefault();
						$(this).treegrid('unselectAll');
						$(this).treegrid('select', row.id);
						$('#mm').menu('show', {
							left: e.pageX,
							top: e.pageY
						});
					},
					onLoadSuccess:function(row,data){
						//$(this).treegrid('collapseAll');
					}
				});
			});

			function reload(){
				var node = $('#test').treegrid('getSelected');
				if (node){
					$('#test').treegrid('reload', node.id);

				} else {
					$('#test').treegrid('reload');
				}
			}
			function getChildren(){
				var node = $('#test').treegrid('getSelected');
				if (node){
					var nodes = $('#test').treegrid('getChildren', node.id);
				} else {
					var nodes = $('#test').treegrid('getChildren');
				}
				var s = '';
				for(var i=0; i<nodes.length; i++){
					s += nodes[i].id + ',';
				}
				alert(s);
			}
			function getSelected(){
				var node = $('#test').treegrid('getSelected');
				if (node){
					alert(node.id+":"+node.name);
				}
			}
			function collapse(){
				var node = $('#test').treegrid('getSelected');
				if (node){
					$('#test').treegrid('collapse', node.id);
				}
			}
			function expand(){
				var node = $('#test').treegrid('getSelected');
				if (node){
					$('#test').treegrid('expand', node.id);
				}
			}
			function collapseAll(){
				var node = $('#test').treegrid('getSelected');
				if (node){
					$('#test').treegrid('collapseAll', node.id);
				} else {
					$('#test').treegrid('collapseAll');
				}
			}
			function expandAll(){
				var node = $('#test').treegrid('getSelected');
				if (node){
					$('#test').treegrid('expandAll', node.id);
				} else {
					$('#test').treegrid('expandAll');
				}
			}
			function expandTo(){
				$('#test').treegrid('expandTo', '02013');
				$('#test').treegrid('select', '02013');
			}
			var idIndex = 1000;

			function append(){
				idIndex++;
				var data = [{
						id: 'id'+idIndex,
						name: 'name'+idIndex,
						size: 'size'+idIndex,
						date: 'date'
					}];
				var node = $('#test').treegrid('getSelected');
				$('#test').treegrid('append', {
					parent: (node?node.id:null),
					data: data
				});
			}
			function remove(){
				var node = $('#test').treegrid('getSelected');
				if (node){
					$('#test').treegrid('remove', node.id);
				}
			}
			//

			var url;
			function criarServico(){
				$('#dlg').dialog('open').dialog('setTitle','Novo Servico');

				var node = $('#test').treegrid('getSelected');
				var pai = $('#test').treegrid('getParent', node.id);

				$('#fm').form('clear');
				$('#fm').form('load',
				{
					pai:pai.name,
					parentid:pai.id,
					cb_algoritmo:0,
					showsla:0,
					goodsla:0,
					triggerid:0,
					sort:0,
					peso_desastre:0,
					peso_alta:0,
					peso_media:0,
					peso_alerta:0,
					peso_info:0,
					peso_normal:0,
					limiar_desastre:0,
					limiar_alta:0,
					limiar_media:0,
					limiar_alerta:0,
					limiar_info:0,
					limiar_normal:0
				});
				url = 'arvore/controle/save_service.php';
			}
			function editarServico(){
				var row = $('#test').datagrid('getSelected');

				//teste
				var node = $('#test').treegrid('getSelected');
				var pai = $('#test').treegrid('getParent', node.id);

				var id = 1;
				var nodeinfo;

				$.getJSON("arvore/controle/busca_service.php",{id:node.id}, function(service)
				{
					//console.log(service);
					//alert(service.name);
					if (row)
					{
						$('#dlg').dialog('open').dialog('setTitle','Editar Servico');

						//preenche aqui os dados
						//$('#fm').form('load',row);

						$('#fm').form('load',
						{
							name:service[0].name,
							id:node.id,
							pai:pai.name,
							parentid:pai.id,
							cb_algoritmo:service[0].algoritm,
							showsla:service[0].showsla,
							goodsla:service[0].goodsla,
							triggerid:service[0].triggerid,
							sortorder:service[0].sortorder,
							peso_desastre:0,
							peso_alta:0,
							peso_media:0,
							peso_alerta:0,
							peso_info:0,
							peso_normal:0,
							limiar_desastre:0,
							limiar_alta:0,
							limiar_media:0,
							limiar_alerta:0,
							limiar_info:0,
							limiar_normal:0
						});
						url = 'arvore/controle/update_service.php';
					}
				})

				//fim teste
			}
			function salvarServico(){

				$('#fm').form('submit',{
					url: url,
					onSubmit: function(){
						return $(this).form('validate');
					},
					success: function(result)
					{
						var result = eval('('+result+')');
						if (result.success){
							$('#dlg').dialog('close');		// close the dialog
							$('#test').treegrid('reload');


						} else {
							$.messager.show({
								title: 'Erro',
								msg: result.msg
							});
						}

						//reload();
					}
				});
			}
			function removerServico(){
				var row = $('#test').datagrid('getSelected');
				if (row){
					$.messager.confirm('Confirm','Você quer remover este serviço?',function(r){
						if (r){
							$.post('arvore/controle/remove_service.php',{id:row.id},function(result){
								if (result.success){
									$('#test').datagrid('reload');	// reload the service data
								} else {
									$.messager.show({	// show error message
										title: 'Error',
										msg: result.msg
									});
								}
							},'json');
						}
					});
				}
			}

			function escolheTrigger()
			{
				$('#dlgTrigger').dialog('open').dialog('setTitle','Escolhe Trigger');
			}

			function abrir(programa,janela)
			{
				if(janela=="") janela = "janela";
				window.open(programa,janela,'height=350,width=640');
			}

		</script>

	</head>

	<body>
		<?php include('header.php'); ?>
		<div id="content">
			<h1>Arvore de Servicos de TI - Configuracao</h1>

			<table id="test"  toolbar="#toolbar"  >
			</table>
			<div id="toolbar">
				<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="criarServico()">Adicionar Servico</a>
				<a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editarServico()">Editar Servico</a>
				<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="removerServico()">Remover Servico</a>
			</div>

			<div id="mm" class="easyui-menu" style="width:120px;">
				<div onclick="criarServico()">Adicionar</div>
				<div onclick="editarServico()">Editar</div>
				<div onclick="removerServico()">Remover</div>
			</div>

			<div id="dlgTrigger" class="easyui-dialog" style="background:#fafafa;width:400px;height:400px" data-options="iconCls:'icon-save',resizable:true,modal:true" closed="true" buttons="#dlg-buttons">
				<form id="fmTrigger" method="post">
				</form>
			</div>

			<div id="dlg" class="easyui-dialog" style="background:#fafafa;width:400px;height:400px" data-options="iconCls:'icon-save',resizable:true,modal:true" closed="true" buttons="#dlg-buttons">
				<form id="fm" method="post">
					<div class="easyui-tabs" style="width:380px;height:320px;">
						<div title="Servico" style="padding:10px;background:#fafafa;">
							<strong>Name:</strong>
							<input name="name" type="text" id="name" size="20" /> <br /><br />
							<input type="hidden" name="id" class="easyui-validatebox" type="text" id="id" size="10" />

							<strong>Parent Service</strong>
							<input name="pai" class="easyui-validatebox" type="text" id="pai" size="20" />
							<input type="hidden" name="parentid" class="easyui-validatebox" type="text" id="parentid" size="10" />
							<input type="button" class="easyui-validatebox" value="Escolher" onclick=javascript:abrir('arvore/poupup/services.php'); />	<br /><br />

							<strong>Algoritmo:</strong>
							<!--
							<input name="Algoritmo" type="text" id="algoritmo" size="10" />
							!-->
							<select class="input select" id="algorithm" name="cb_algoritmo" size="1">
								<option class="" value="1">Problem, if at least one child has a problem</option>
								<option class="" value="2">Problem, if all children have problems</option>
								<option class="" value="0" selected="selected">Do not calculate</option>
							</select>
							<br /><br />

							<strong>Calc. SLA</strong>
							<input name="showsla" type="text" id="showsla" size="5" />
							<br /><br />

							<strong>Good SLA</strong>
							<input name="goodsla" type="text" id="goodsla" size="5" />
							<br /><br />

							<strong>Trigger</strong>
							<input name="trigger" type="text" id="trigger" size="35" />
							<input type="hidden" name="triggerid" class="easyui-validatebox" type="text" id="triggerid" size="10"/>
							<input type="button" value="Escolher" onclick=javascript:abrir('arvore/poupup/trigger.php');  /><br /><br />

							<strong>Sort Order (0-999)</strong>
							<input name="sortorder" type="text" id="sortorder" size="5" /> <br /><br />
						</div>

						<div title="Pesos e Limiar" closable="true" style="padding:10px;">
							<table id="dados" class="borda" >
								<thead>
									<tr>
										<th>Severidade</th>
										<th>Peso</th>
										<th>Limiar</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Desastre</td>
										<td><input name="peso_desastre" type="text" id="peso_desastre" size="10" /></td>
										<td><input name="limiar_desastre" type="text" id="limiar_desastre" size="10" /></td>
									</tr>
									<tr>
										<td>Alta</td>
										<td><input name="peso_alta" type="text" id="peso_alta" size="10" /></td>
										<td><input name="limiar_alta" type="text" id="limiar_alta" size="10" /></td>
									</tr>
									<tr>
										<td>Media</td>
										<td><input name="peso_media" type="text" id="peso_media" size="10" /></td>
										<td><input name="limiar_media" type="text" id="limiar_media" size="10" /></td>
									</tr>
									<tr>
										<td>Alerta</td>
										<td><input name="peso_alerta" type="text" id="peso_alerta" size="10" /></td>
										<td><input name="limiar_alerta" type="text" id="limiar_alerta" size="10" /></td>
									</tr>
									<tr>
										<td>Informacao</td>
										<td><input name="peso_info" type="text" id="peso_info" size="10" /></td>
										<td><input name="limiar_info" type="text" id="limiar_info" size="10" /></td>
									</tr>
									<tr>
										<td>Normal</td>
										<td><input name="peso_normal" type="text" id="peso_normal" size="10" /></td>
										<td><input name="limiar_normal" type="text" id="limiar_normal" size="10" /></td>
									</tr>
								</tbody>
							</table>
						</div>
						<div title="Dependencies" iconCls="icon-reload" closable="true" style="padding:10px;">
						</div>
						<div title="Time" iconCls="icon-reload" closable="true" style="padding:10px;">
						</div>
					</div>

				</form>
			</div>
			<div id="dlg-buttons">
				<a href="#" class="easyui-linkbutton" iconCls="icon-ok" onclick="salvarServico()">Salvar</a>
				<a href="#" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')">Cancelar</a>
			</div>
		</div>
	</body>
</html>