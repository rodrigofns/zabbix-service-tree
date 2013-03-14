<div id="dialogChooseTrigger">
	<style type="text/css" scoped="scoped">
		div#dialogChooseTrigger { display:none; }
		div#chooseTriggerWnd {
			/*width:680px;
			height:320px;*/
			overflow-x:auto;
			overflow-y:auto;
		}
	</style>
	<div id="chooseTriggerWnd">
		<table>
		<tr><td>Atual</td><td><input name="curTriggerName" size="28" readonly="readonly"/></td></tr>
		<tr><td>Nó</td><td><select name="node"></select></td></tr>
		<tr><td>Grupo</td><td><select name="group"></select></td></tr>
		<tr><td>Host</td><td><select name="host"></select></td></tr>
		<tr><td>Trigger</td><td><select name="trigger"></select></td></tr>
		</table>
	</div>
</div>

<script type="text/javascript">
function ShowChooseTrigger(curTriggerId, curTriggerName) {
	var div = $('div#dialogChooseTrigger'), // pointer to DIV
		cmbNode = div.find('select[name=node]'), // pointers to combos
		cmbGroup = div.find('select[name=group]'),
		cmbHost = div.find('select[name=host]'),
		cmbTrigger = div.find('select[name=trigger]');

	div.find('select').html(''); // empty combos
	div.find('select[name=node]').html('<option value="0">-- selecione --</option>');
	div.find('input[name=curTriggerName]').val(curTriggerName); // display purposes only

	var popup = div.modalForm({ hasCancel:true, title:'Escolher trigger' });

	var events = {
		nodeChanged: function() {
			cmbGroup.html(''); // empty combos
			cmbHost.html('');
			cmbTrigger.html('');
			var nodeid = cmbNode.val(); // ID of selected node
			if(nodeid == 0) return;

			var xhr = $.post('ajaxTriggers.php', { r:'groups', node:nodeid }); // get groups list
			xhr.error(function(response) {
				$('<span>Erro na consulta dos grupos de host.<br/>' +
					response.status + ': ' + response.statusText + '<br/>' +
					response.responseText + '</span>'
				).modalForm({ title:'Oops...' });
			});
			xhr.success(function(data) {
				// Fill combo with host groups belonging to the chosen distributed node.
				cmbGroup.append('<option value="0">-- selecione --</option>');
				for(var i = 0; i < data.length; ++i)
					cmbGroup.append('<option value="' + data[i].groupid + '">' + data[i].name + '</option>');
				popup.centerOnPage();
				cmbGroup.focus();
			});
		},

		groupChanged: function() {
			cmbHost.html(''); // empty combos
			cmbTrigger.html('');
			var groupid = cmbGroup.val(); // ID of selected host group
			if(groupid == 0) {
				popup.centerOnPage();
				return;
			}

			var xhr = $.post('ajaxTriggers.php', { r:'hosts', group:groupid }); // get hosts list
			xhr.error(function(response) {
				$('<span>Erro na consulta dos hosts.<br/>' +
					response.status + ': ' + response.statusText + '<br/>' +
					response.responseText + '</span>'
				).modalForm({ title:'Oops...' });
			});
			xhr.success(function(data) {
				// Fill combo with hosts belonging to the chosen host group.
				cmbHost.append('<option value="0">-- selecione --</option>');
				for(var i = 0; i < data.length; ++i)
					cmbHost.append('<option value="' + data[i].hostid + '">' + data[i].name + '</option>');
				popup.centerOnPage();
				cmbHost.focus();
			});
		},

		hostChanged: function() {
			cmbTrigger.html(''); // empty combos
			var hostid = cmbHost.val(); // ID of selected host
			if(hostid == 0) {
				popup.centerOnPage();
				return;
			}

			var xhr = $.post('ajaxTriggers.php', { r:'triggers', host:hostid }); // get triggers list
			xhr.error(function(response) {
				$('<span>Erro na consulta das triggers.<br/>' +
					response.status + ': ' + response.statusText + '<br/>' +
					response.responseText + '<span>'
				).modalForm({ title:'Oops...' });
			});
			xhr.success(function(data) {
				// Fill combo with triggers belonging to the chosen host.
				cmbTrigger.append('<option value="0">-- selecione --</option>');
				for(var i = 0; i < data.length; ++i)
					cmbTrigger.append('<option value="' + data[i].triggerid + '">' + data[i].description + '</option>');
				popup.centerOnPage();
				cmbTrigger.focus();
			});
		}
	};

	var unbindEvents = function() {
		cmbNode.off('change');
		cmbGroup.off('change');
		cmbHost.off('change');
	};

	popup.ok(unbindEvents);
	popup.cancel(unbindEvents);

	popup.ready(function() {
		var xhr = $.post('ajaxTriggers.php', { r:'nodes' });
		xhr.error(function(response) {
			$('<span>Erro na consulta das triggers.<br/>' +
				response.status + ': ' + response.statusText + '<br/>' +
				response.responseText + '</span>'
			).modalForm({ title:'Oops...' }).ok(function() {
				popup.abort();
			});
		});
		xhr.success(function(data) {
			cmbNode.on('change', events.nodeChanged); // setup events
			cmbGroup.on('change', events.groupChanged);
			cmbHost.on('change', events.hostChanged);

			for(var i = 0; i < data.length; ++i) { // fill combo with distributed nodes
				div.find('select[name=node]').append(
					'<option value="' + data[i].nodeid + '">' + data[i].name + '</option>');
			}
			popup.centerOnPage();
		});
	});

	popup.validateSubmit(function() {
		var triggerid = div.find('select[name=trigger]').val();
		if(triggerid === null || triggerid == 0) {
			$('<span>Nenhuma trigger selecionada.</span>')
				.modalForm({ title:'Oops...' });
		} else if(triggerid == curTriggerId) {
			$('<span>A trigger selecionada é a mesma trigger<br/>já atrelada ao serviço.</span>')
				.modalForm({ title:'Oops...' });
		} else {
			unbindEvents();
			popup.continueSubmit({ // build return trigger object for ok() event
				triggerid: div.find('select[name=trigger]').val(),
				name: div.find('select[name=trigger] option:selected').text()
			});
		}
	});

	return popup; // can call ok() event
}
</script>