<?php
require('include/Connection.class.php');
header('Content-Type:text/html; charset=utf-8');

// Uses old PHP tree code as a webservice.
$baseUrl = Connection::BaseUrl().'legacy/arvore/arvore_grafica';
$codeBase = 'legacy';

$cx = '400'; // containers will be resized through JavaScript, these values are dummy
$cy = '300';

switch($_POST['viewType'])
{
case 'view_html5': ?>
	<!-- HTML5 tree. -->
	<div><iframe id="h5tree" width="<?=$cx?>" height="<?=$cy?>"
		src="treegraph/?service=<?=$_POST['serviceName']?>&refresh=<?=$_POST['refreshTime']?>"></iframe></div>
	<? break;
case 'view_jnormal': ?>
	<!-- Normal Java tree applet. -->
	<applet codebase="<?=$codeBase?>" code="br.gov.serpro.arvoreGrafica.teste.GenericApplet.class"
	width="<?=$cx?>" height="<?=$cy?>" archive="prefuse.jar,libgraphtree.jar">
		<param name="arvore" value="<?=$_POST['serviceName']?>"/>
		<param name="path" value="<?=$baseUrl?>/graph_tree_xml.php?nome="/>
		<param name="time" value="<?=$_POST['refreshTime']?>"/>
		<param name="resource" value=""/>
	</applet>
	<? break;
case 'view_jext': ?>
	<!-- Extended Java tree applet. -->
	<applet codebase="<?=$codeBase?>" code="br.gov.serpro.arvoreGrafica.teste.ExtendApplet.class"
	width="<?=$cx?>" height="<?=$cy?>" archive="prefuse.jar,libgraphtree.jar">
		<param name="arvore" value="<?=$_POST['serviceName']?>"/>
		<param name="path" value="<?=$baseUrl?>/graph_tree_xml.php?nome="/>
		<param name="time" value="<?=$_POST['refreshTime']?>"/>
		<param name="resource" value=""/>
	</applet>
	<? break;
case 'view_jhyper': ?>
	<!-- Hyperbolic Java tree applet. -->
	<applet codebase="<?=$codeBase?>" code="br.gov.serpro.hyperbolic.applet.HyperView.class"
	archive="hyper.jar" id="Treebolic" width="<?=$cx?>" height="<?=$cy?>">
		<param name="doc" value="<?=$baseUrl?>/graph_tree_xml_hyper.php?nome=<?=$_POST['serviceName']?>"/>
		<param name="urlbase" value="<?=$baseUrl?>/tree_status.php"/>
		<param name="time" value="<?=$_POST['refreshTime']?>"/>
	</applet>
	<? break;
}