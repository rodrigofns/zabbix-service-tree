<?php
session_start();
require('../include/Connection.class.php');
require('../include/StatusColor.class.php');

if(!isset($_GET['service']) || !isset($_GET['refresh']))
	Connection::HttpError(400, 'São necessários os parâmetros <b>service</b> e <b>refresh</b>.');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<style type="text/css">
		body,table,input,select { font:10pt arial; color:#222; }
		a { color:#1853AD; }
		a:hover { color:#FE5E00; }
		hr { height:1px; border:0; color:#CCC; background-color:#CCC; }
		canvas#treePlot { display:none; position:absolute; }
		div#ctrlclickbox { position:fixed; top:0; left:12px; color:#999; font-size:85%; }
		div#toolbox { position:fixed; top:0; left:10px; font-size:100%; z-index:30; border:1px solid #CCC; background:rgba(240,240,240,0.5); padding:1px 6px; }
		.numStatus { border:1px solid #CCC; padding:0px 4px; }
	</style>
	<script type="text/javascript" src="../include/jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="../include/jquery.requestAnimationFrame.min.js"></script>
	<script type="text/javascript" src="../include/jquery.modalForm.js"></script>
	<script type="text/javascript" src="../include/TreeGraph.js"></script>
	<script type="text/javascript" src="index.js"></script>
</head>
<body>
	<input type="hidden" id="serviceName" value="<?=$_GET['service']?>"/>
	<input type="hidden" id="refreshTime" value="<?=$_GET['refresh']?>"/>
	<input type="hidden" id="hash" value="<?=$_SESSION['hash']?>"/>
	<div id="loading">Carregando <?=$_GET['service']?>... <img src="../include/loading.gif"/></div>

	<!-- Informative toolbox at bottom of page. -->
	<div id="ctrlclickbox">Clique para expandir, Ctrl+clique para editar.</div>

	<!-- Toolbox placed through JavaScript at bottom of page. -->
	<div id="toolbox"><span id="numNodes">0</span> nós
		<? for($i = 0; $i <= count(StatusColor::$VALUES); ++$i) { ?>
		<span class="numStatus" id="numStatus<?=$i?>" style="background:<?=StatusColor::$VALUES[$i]?>;">0</span>
		<? } ?>
		<a id="collapse" href="#" title="Recolhe todos os nós da árvore">recolher tudo</a></div>

	<!-- DIV that contains the tree. -->
	<canvas id="treePlot"></div>

	<? include('dialogEditService.php'); ?>
	<? include('dialogChooseTrigger.php'); ?>
	<? include('dialogChooseParent.php'); ?>
</body>
</html>