<?php
session_start();
header('Content-type:text/html; charset=UTF-8');

if(!isset($_SESSION['user'])) { ?>
	<!-- User is NOT logged in. -->
	<a href="#" id="lnkLogin" title="Login no Zabbix para edição da árvore">Efetuar login</a>
<? } else { ?>
	<!-- User is logged in. -->
	<span>
		<span id="userName" title="Você está logado"><?=$_SESSION['user']?></span> |
		<a href="#" id="lnkLogoff" title="Efetuar logoff">Logoff</a>
	</span>
<? }