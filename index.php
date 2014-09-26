<?php
	require_once('__conf.php');
	require_once('inc/Connection.class.php');
	require_once('inc/Install.class.php');
	require_once('inc/ServiceTree.class.php');
	session_start();
	function_exists('curl_init') or die('cURL module not found.');
	$dbh = Connection::GetDatabase();
	Install::CheckDbTables($dbh);
	$colors = ServiceTree::GetColors($dbh);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<link rel="icon" type="image/x-icon" href="img/favicon.ico"/>
	<?php require_once('i18n/i18n.php'); i18n_set_map('en', $LANG); ?>
	<style type="text/css">
		html,body { height:100%; margin:0; overflow:hidden; }
		body,table,input,select { font:10pt Arial; color:#181818; }
		a { color:#1853AD; }
		a:hover { color:#FE5E00; }
		hr { height:1px; border:0; color:#CCC; background-color:#CCC; }
		div#theBigOne { width:100%; height:100%; background:url('img/serpro.png') no-repeat bottom right; }
		#treeLoading { position:fixed; top:50%; left:50%; margin-top:-75px; margin-left:-75px; }
		/*canvas#treePlot { width:100%; height:100%; }*/
		div#topLeftOne { background:rgba(250,250,250,0.5); border:1px solid #EEE; padding:4px; position:fixed; top:8px; left:8px; font:bold 13pt Arial; color:#BBB; }
		div#topRiteOne { background:rgba(230,230,230,0.5); border:1px solid #D4D4D4; padding:6px; position:fixed; top:10px; right:10px; }
		div#toolbox { position:fixed; left:10px; bottom:10px; border:1px solid #CCC; background:rgba(230,230,230,0.5); padding:1px 6px; }
		.numStatus { border:1px solid #CCC; padding:0px 4px; }
		#statusTxt { font-size:8pt; }
	</style>
	<title><?=I('IT Services Tree')?></title>
	<script type="text/javascript" src="js/jquery-2.0.3.min.js"></script>
	<script type="text/javascript" src="js/jquery.dateFormat-1.0.min.js"></script>
	<script type="text/javascript" src="js/jquery.modalForm.min.js"></script>
	<script type="text/javascript" src="js/TreeGraph.min.js"></script>
	<script type="text/javascript" src="index.js"></script>
</head>
<body>
	<div id="theBigOne">
		<canvas id="treePlot"></canvas>
		<img id="treeLoading" src="img/loadingbig.gif"/>
	</div>
	<div id="topLeftOne"><?=I('IT SERVICES TREE')?><br/>
		<span id="statusTxt"><?=I('Last updated in')?> <span id="lastUpdate">yyyy-MM-dd HH:mm:ss</span>.</div>
	<div id="topRiteOne">
		<?=I('Refresh')?> <select id="refreshTime" title="<?=I('Tree refresh time, in minutes')?>">
			<option value="60">1 <?=I('min')?></option>
			<option value="120">2 <?=I('min')?></option>
			<option value="180">3 <?=I('min')?></option>
			<option value="300">5 <?=I('min')?></option>
			</select> &nbsp;
		<?=I('Service')?> <select id="serviceName" title="<?=I('IT service to be loaded')?>">
			<option class="" value="">--- <?=I('choose')?> ---</option>
			</select> &nbsp;
		<span id="loginMenu"></span>
	</div>
	<div id="toolbox"><span id="numNodes">0</span> <?=I('nodes')?>
		<?php for($i = 0; $i <= count($colors) - 1; ++$i) { ?>
		<span class="numStatus" id="numStatus<?=$i?>" style="background:<?=$colors[$i]?>;">0</span>
		<?php } ?>
		<a id="collapse" href="#" title="<?=I('Collapse all tree nodes')?>"><?=I('collapse all')?></a></span>
	</div>
	<?php include('dialogLogin.php'); ?>
	<?php include('dialogEditService.php'); ?>
	<?php include('dialogChooseIcon.php'); ?>
	<?php include('dialogChooseTrigger.php'); ?>
</body>
</html>
