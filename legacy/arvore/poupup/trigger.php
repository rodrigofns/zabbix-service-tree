<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="keywords" content="jquery,ui,easy,easyui,web">
	<meta name="description" content="easyui help you build your web page easily!">
	<title>Triggers</title>
	<link rel="stylesheet" type="text/css" href="http://www.jeasyui.com/easyui/themes/default/easyui.css">
	<link rel="stylesheet" type="text/css" href="http://www.jeasyui.com/easyui/themes/icon.css">
	<style type="text/css">
	#table-5 {
		background-color: #f5f5f5;
		padding: 5px;
		border-radius: 5px;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
		border: 1px solid #ebebeb;
	}
	#table-5 td, #table-5 th {
		padding: 1px 5px;
	}
	#table-5 thead {
		font: normal 15px Helvetica Neue,Helvetica,sans-serif;
		text-shadow: 0 1px 0 white;
		color: #999;
	}
	#table-5 th {
		text-align: left;
		border-bottom: 1px solid #fff;
	}
	#table-5 td {
		font-size: 14px;
	}
	#table-5 td:hover {
		background-color: #fff;
	}
	</style>
	<script type="text/javascript" src="jquery-1.8.0.min.js"></script>
	<script type="text/javascript" src="jquery.easyui.min.js"></script>
	<script>
	function retorna(retorno)
        {
		//alert("s");
           //window.opener.document.fm.trigger.value = retorno;
	    window.opener.document.getElementById('trigger').value = "/etc/passwd has been changed on Zabbix server";
	    window.opener.document.getElementById('triggerid').value = retorno;
           window.self.close();
        }
	
	 $(document).ready(function(){
	 
	 //Carrega os Grupos
	 
	 $("#hosts").change(function(){
		$.ajax({
		 type: "POST",
		 url: "exemplo.php",
		 //data: {montadora: $("#montadora").val()},
		 dataType: "json",
		 success: function(json){
		    var options = "";
		    $.each(json, function(key, value){
		       options += '<option value="' + key + '">' + value + '</option>';
		    });
		    //$("#veiculo").html(options);
		 }
	      });
   });
	});
	
	</script>

</head>

<body>
		<label for="grupos">Grupos:</label>
		<select name="grupos" id="grupos">
		</select>
		<label for="hosts">Host:</label>
		<select name="hosts" id="hosts">
		</select>
			 
			<table id="table-5" class="borda" >
				<thead>
					<tr>
						<th>Nome</th>
						<th>Severidade</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
				<tr>
						<th>
						<a href=javascript:retorna("10");>/etc/passwd has been changed on Zabbix server</a>
						</th>
						<th style="background-color: #900;color:white">Alta</th>
						<th>Enable</th>
					</tr>
					<tr>
						<th>
						<a href=javascript:retorna("10");>/etc/passwd has been changed on Zabbix server</a>
						</th>
						<th style="background-color: #900;color:white">Alta</th>
						<th>Enable</th>
					</tr>
				</tbody>		
			</table>
		
	
</body>
</html>