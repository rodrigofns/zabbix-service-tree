<?php
	$method = $_SERVER['SERVER_PORT'] == 80 ? "http://" : "https://";
	$var = urldecode($method.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']));
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title>Árvore de Servicos de TI</title>
	<link rel="stylesheet" type="text/css" href="http://www.jeasyui.com/easyui/themes/default/easyui.css"/>
	<link rel="stylesheet" type="text/css" href="http://www.jeasyui.com/easyui/themes/icon.css"/>
	<link rel="stylesheet" type="text/css" href="index.css"/>
	<script type="text/javascript" src="arvore/js/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="arvore/js/jquery.easyui.min.js"></script>
	<script type="text/javascript">
		var time = 150;
		var view = 'Normal';
		var service = '';

		$(document).ready(function() {
			carregaComboServices();

			$('select[name=time]').change(function() { time = $(this).val(); verifica(); });
			$('select[name=view]').change(function() { view = $(this).val(); verifica(); });
			$('select[name=service]').change(function() { service = $(this).val(); verifica(); });
		});

		function verifica(){
			if(service != '')
			{
				if(view == 'Normal')
				{
					$('div.applet').empty();
					$('div.applet').html('<applet ' +
						'url="arvore/jars/" ' +
						'code="br.gov.serpro.arvoreGrafica.teste.GenericApplet.class" ' +
						'width="100%" height="600" ' +
						'archive="prefuse.jar,libgraphtree.jar">' +
							'<param name="arvore"	value="' + service + '">' +
							'<param name="path"	value="<?=$var?>/arvore/arvore_grafica/graph_tree_xml.php?nome=">' +
							//'<param name="path"	value="http://10.200.118.68/zabbix/dev/arvorerodrigo/ajax/graphTreeXml.php?nome=">' +
							'<param name="time" value="' + time + '">' +
							'<param name="resource" value="">' +
						'</applet>');
				}
				else if(view == 'Extendida')
				{
					$('div.applet').empty();
					$('div.applet').html('<applet ' +
						'url="arvore/jars/" ' +
						'code="br.gov.serpro.arvoreGrafica.teste.ExtendApplet.class" ' +
						'width="100%" height="600" ' +
						'archive="prefuse.jar,libgraphtree.jar">' +
							'<param name="arvore" value="' + service + '">' +
							'<param name="path" value="<?=$var?>/arvore/arvore_grafica/graph_tree_xml.php?nome=">' +
							'<param name="time" value="' + time + '">' +
							'<param name="resource" value="">' +
						'</applet>');
				}
				else if(view == 'Hyper')
				{
					$('div.applet').empty();
					$('div.applet').html('<applet ' +
						'url="arvore/jars/" ' +
						'code="br.gov.serpro.hyperbolic.applet.HyperView.class" ' +
						'archive="hyper.jar" ' +
						'id="Treebolic" ' +
						'width="100%" height="600">' +
							'<param name="doc" value="<?=$var?>/arvore/arvore_grafica/graph_tree_xml_hyper.php?nome=' + service + '">' +
							'<param name="urlbase" value="<?=$var?>/arvore/arvore_grafica/tree_status.php">' +
							'<param name="time" value="' + time + '">' +
						'</applet>');
				}
			}
		}

		function carregaComboServices() {
			$.getJSON('arvore/arvore_grafica/service_root.php', {ajax:'false'}, function(json) {
				var options = '<option class="" value="">--- selecione ---</option>';
				for(var i = 0; i < json.length; ++i)
					options += '<option value="' + json[i].optionValue + '">' + json[i].optionDisplay + '</option>';
				$('select#service').html(options);
			});
		}
	</script>
</head>

<body>
	<?php include_once ('header.php'); ?>
	<div id="content">
		<strong>Refresh Time:</strong>
		<select class="input select" id="time" name="time" size="1">
			<option class="" value="150">150</option>
			<option class="" value="120">120</option>
			<option class="" value="60" selected="selected">60</option>
		</select>

		<strong>Service:</strong>
		<select class="input select" id="service" name="service" size="1">
		</select>

		<strong>View:</strong>
		<select class="input select" id="view" name="view" size="1">
			<option class="" value="Normal">Normal</option>
			<option class="" value="Extendida">Extendida</option>
			<option class="" value="Hyper">Hyper</option>
		</select>
		<div id="applet" class="applet"/>
		<!--
		<div id="applet" class="applet">

				<APPLET CODE="br.gov.serpro.arvoreGrafica.teste.GenericApplet.class" WIDTH="100%" HEIGHT="600" archive="prefuse.jar,libgraphtree.jar">';
					<PARAM NAME="arvore"	VALUE="Expresso">
										<PARAM NAME="path"	VALUE="http://localhost/arvore/arvore/arvore_grafica/graph_tree_xml.php?nome=">
										<PARAM NAME="time"  VALUE="20">
										<PARAM NAME="resource"  VALUE="">

		</div>

		<div id="applet" class="applet">
				<applet code="br.gov.serpro.hyperbolic.applet.HyperView.class" archive="hyper.jar" id="Treebolic" width="100%" height="600">
						<param name="doc" value="http://localhost/arvore/arvore/arvore_grafica/graph_tree_xml_hyper.php?nome=Expresso">
						<param name="urlbase" value="http://localhost/arvore/arvore/arvore_grafica/tree_status.php">
						<PARAM NAME="time"  value="20">
				</applet>
		</div>
		-->
	</div>
</body>
</html>
