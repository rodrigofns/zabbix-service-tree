<? session_start(); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<link rel="icon" type="image/x-icon" href="include/favicon.ico"/>
	<title>Árvore de Serviços de TI</title>
	<style type="text/css">
		body,table,input,select { font:10pt Arial; color:#111; }
		body { margin:0; }
		a { color:#1853AD; }
		a:hover { color:#FE5E00; }
		hr { height:1px; border:0; color:#CCC; background-color:#CCC; }
		div#menuBg { /*background-image:url('include/leaves.jpg');*/ width:100%; z-index:-10; height:36px; position:fixed; }
		div#menu { background:rgba(90,90,90,.6); height:36px; padding:0 12px; }
		div#contents { position:absolute; border:1px solid white; overflow:auto; background:url('include/serpro.png') no-repeat bottom right; }
		iframe#h5tree { border:1px solid white; }
		table#menuTable { color:#F2F2F2; border-collapse:collapse; border-spacing:0; width:100%; height:100%; }
		table#menuTable a { color:#A4BFF2; }
		table#menuTable a:hover { color:#FF9E6A; }
		table#menuTable td { padding:0; }
		table#menuTable td#appTitle { font:bold 10pt Arial; color:#C8C8C8; }
		table#menuTable ul#viewOptions { padding:0; margin:0; }
		table#menuTable ul#viewOptions li { display:inline; float:left; margin:6px 0 0 0; height:24px; padding:5px 7px 0 7px; }
		.viewSelected { border-top:1px solid #78A3D6; color:#666; background:white; }
		.viewUnselected { border-top:1px solid #BBB; }
		[class=viewUnselected]:hover { background:rgba(205,205,205,.5); cursor:pointer; }
	</style>
	<script type="text/javascript" src="include/jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="include/jquery.requestAnimationFrame.min.js"></script>
	<script type="text/javascript" src="include/jquery.modalForm.js"></script>
	<script type="text/javascript" src="index.js"></script>
</head>
<body><div id="menuBg"></div>
	<div id="menu">
		<table id="menuTable">
		<tr>
			<td id="appTitle">ÁRVORE DE SERVIÇOS DE TI</td>
			<td>
				<ul id="viewOptions">
				<li id="view_html5">HTML5</li>
				<li id="view_jnormal">Java normal</li>
				<li id="view_jext">Java extendida</li>
				<li id="view_jhyper">Java hiperbólica</li>
				</ul>
			</td>
			<td>Atualização <select id="refreshTime" title="Tempo de atualização da árvore, em segundos">
					<option value="60">1 min</option>
					<option value="120">2 min</option>
					<option value="180">3 min</option>
					<option value="300">5 min</option>
					</select> &nbsp;
				Serviço <select id="serviceName" title="Serviço de TI a ser exibido na árvore">
					<option class="" value="">--- selecione ---</option>
					</select>
			</td>
			<td style="text-align:right;" width="260">
				<div id="loginMenu"></div><!-- login/logoff links -->
			</td>
		</tr>
		</table>
	</div>
	<div id="contents"></div>
	<? include('dialogLogin.php'); ?>
</body>
</html>