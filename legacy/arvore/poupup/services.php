<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="keywords" content="jquery,ui,easy,easyui,web">
	<meta name="description" content="easyui help you build your web page easily!">
	<title>IT Service Parent</title>
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
	<script type="text/javascript" src="../js/jquery-1.8.0.min.js"></script>
	<script type="text/javascript" src="../js/jquery.easyui.min.js"></script>
	<script>
	function retorna(id, name)
        {
		//alert("s");
           //window.opener.document.fm.trigger.value = retorno;
	    window.opener.document.getElementById('pai').value = name;
	    window.opener.document.getElementById('parentid').value = id;
           window.self.close();
        }
        $(document).ready(function(){
            var statuses = new Array();
            statuses[0] = "Do not calculate";
            statuses[1] = "Problem, if at least one child has a problem";
            statuses[2] = "Problem, if all children have problems";
            
            $.getJSON("../controle/busca_service.php",{id:0,pservices:1}, function(services){
                //console.debug(services);
                var saida='';
                $.each(services,function(index,service){
                    saida += '<tr>';
                    saida += '<td>'
                    saida += '<a href=javascript:retorna("'+service.serviceid+'","'+service.name+     '");>'+service.name+'</a>';
                    saida += '</td>'
                    saida += '<td>'
                    saida += statuses[service.status];
                    saida += '</td>'
                    
                    saida += '</tr>';
                });
                $('#table_services tbody').append(saida);
                
            })
        })
	</script>

</head>

<body>				 
	<table id="table_services" class="borda" >
		<thead>
			<tr>
				<th>Service</th>
				<th>Status Calculation</th>
				
			</tr>
		</thead>
		<tbody>
                    <tr>
				<td>
                                    <a href=javascript:retorna("0","root");>root</a>
				</td>
				<td>-</td>
				
			</tr>
		</tbody>		
	</table>
</body>
</html>