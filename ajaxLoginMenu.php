<?php
session_start();
header('Content-type:text/html; charset=UTF-8');

require_once('__conf.php');
require_once('i18n/i18n.php');
i18n_set_map('en', $LANG, false);

if(!isset($_SESSION['user'])) { ?>
	<!-- User is NOT logged in. -->
	<a href="#" id="lnkLogin" title="<?=I('Log into Zabbix to edit the tree')?>"><?=I('Log in')?></a>
<?php } else { ?>
	<!-- User is logged in. -->
	<span>
		<span id="userName" title="<?=I('You are logged in')?>"><?=$_SESSION['user']?></span> |
		<a href="#" id="lnkLogoff" title="<?=I('Log off application')?>"><?=I('Log off')?></a>
	</span>
<?php }