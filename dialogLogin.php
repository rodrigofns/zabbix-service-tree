<div id="dialogLogin">
	<style type="text/css" scoped="scoped">
		div#dialogLogin { display:none; }
	</style>
	<table>
	<tr><td>Usuário &nbsp;</td><td><input type="text" name="user"/></td></tr>
	<tr><td>Senha</td><td><input type="password" name="pass"/></td></tr>
	</table>
</div>

<script type="text/javascript">
function ShowLogin(ev) {
	var div = $('div#dialogLogin'); // pointer to DIV
	var popup = div.modalForm({ hasCancel:true, event:ev, title:'Login' });

	popup.validateSubmit(function() {
		if(div.find('input[name=user]').val() == '') {
			$('<span>É necessário informar o nome de usuário.</span>').modalForm({ title:'Oops...' });
		} else {
			var xhr = $.post('ajaxLogin.php', { // attempt to login
				user: div.find('input[name=user]').val(),
				pass: div.find('input[name=pass]').val()
			});
			xhr.error(function(response) { // login failed
				div.find('input[name=pass]').val(''); // clear password field
				$('<span>Ops... não foi possível efetuar login.<br/>' +
					response.status + ': ' + response.statusText + '<br/>' +
					response.responseText + '</span>'
				).modalForm({ title:'Oops...' });
			});
			xhr.success(function(data) { // login successful
				div.find('input[name=pass]').val(''); // clear password field
				popup.continueSubmit();
			});
		}
	});

	return popup; // can call ok() and cancel() events
}
</script>