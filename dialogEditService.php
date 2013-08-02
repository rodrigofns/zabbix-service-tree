<div id="dialogEditService">
	<style type="text/css" scoped="scoped">
		div#dialogEditService { display:none; }
		div#serviceEditWnd {
			width:420px;
			height:340px;
			overflow-x:hidden;
			overflow-y:auto;
		}
		th { background:#EEE; }
	</style>
	<div id="serviceEditWnd">
		<input type="hidden" name="rootServiceName" value=""/>
		<table>
		<tr><td><?=I('Name')?></td><td><input type="text" size="30" name="name"/></td></tr>
		<!--tr><td><?=I('Parent service')?></td>
			<td><input type="text" size="30" name="parentServiceName" readonly="readonly"/>
				<input type="hidden" name="parentServiceId" value=""/>
				<a href="#" id="lnkChangeParent" title="<?=I('Change parent service of this service')?>"><?=I('change')?>...</a></td></tr-->
		<tr><td><?=I('Algorithm')?></td>
			<td><select name="algorithm">
				<option value="1"><?=I('Problem, if at least one child has a problem')?></option>
				<option value="2"><?=I('Problem, if all children have problems')?></option>
				<option value="0"><?=I('Do not calculate')?></option>
				</select></td></tr>
		<tr><td><?=I('Acceptable SLA')?></td>
			<td><input type="text" size="8" name="goodSla"/>
				<label><input type="checkbox" name="showSla"/><?=I('Calculate SLA?')?></label></td></tr>
		<tr><td>Trigger</td>
			<td><input type="hidden" name="triggerId" value=""/>
				<input type="text" size="28" name="triggerTxt" readonly="readonly"/>
				<a href="#" id="lnkChooseTrigger" title="<?=I('Choose a trigger to this service')?>"><?=I('choose')?>...</a></td></tr>
		</table>
		<hr/>

		<div style="float:left;">
			<table>
			<tr><th><?=I('Severity')?></th><th><?=I('Weight')?></th><th><?=I('Threshold')?></th></tr>
			<tr><td><?=I('Normal')?></td>
				<td><input type="text" size="5" name="wnormal"/></td><!-- weight -->
				<td><input type="text" size="5" name="tnormal"/></td></tr><!-- threshold -->
			<tr><td><?=I('Information')?></td>
				<td><input type="text" size="5" name="winformation"/></td>
				<td><input type="text" size="5" name="tinformation"/></td></tr>
			<tr><td><?=I('Alert')?></td>
				<td><input type="text" size="5" name="walert"/></td>
				<td><input type="text" size="5" name="talert"/></td></tr>
			<tr><td><?=I('Average')?></td>
				<td><input type="text" size="5" name="waverage"/></td>
				<td><input type="text" size="5" name="taverage"/></td></tr>
			<tr><td><?=I('Major')?></td>
				<td><input type="text" size="5" name="wmajor"/></td>
				<td><input type="text" size="5" name="tmajor"/></td></tr>
			<tr><td><?=I('Critical')?></td>
				<td><input type="text" size="5" name="wcritical"/></td>
				<td><input type="text" size="5" name="tcritical"/></td></tr>
			</table>
		</div>
		<div>&nbsp;<br/>
			&nbsp; &nbsp; &nbsp; <input type="button" name="setdef" value="<?=I('default values')?>"
				title="<?=I('Set default values to all fields')?>"/>
		</div>
		<!--<hr/>
		<?=I('Other actions')?>:<br/>
		<a href="#" id="lnkDeleteService" title="<?=I('Remove this service from tree')?>"><?=I('delete this service')?></a> |
		<a href="#" id="lnkNewChildService" title="<?=I('Create a new child service of current one')?>"><?=I('create new child')?></a>
		<br/>&nbsp;-->
	</div>
</div>

<script type="text/javascript">
function ShowEditService(nodeObj, rootServiceName) {

	// Pre-loading housekeeping.

	var div = $('div#dialogEditService'); // pointer to DIV
	var props = [ 'normal', 'information', 'alert', 'average', 'major', 'critical' ];

	function FillFields(serviceObj) {
		var y = serviceObj !== null;
		div.find('input[name=name]').val(y ? serviceObj.name : '');
		div.find('input[name=parentServiceName]').val(y ? serviceObj.parent.name : '');
		div.find('input[name=parentServiceId]').val(y ? serviceObj.parent.serviceid : '');
		div.find('select[name=algorithm]').val(y ? serviceObj.algorithm : 0);
		div.find('input[name=goodSla]').val(y ? serviceObj.goodsla : '');
		div.find('input[name=showSla]').prop('checked', y ? (serviceObj.showsla == 1) : false).triggerHandler('change');

		if(y && serviceObj.children.length === 0) { // a leaf node
			div.find('input[name=triggerId]').val(serviceObj.triggerid);
			div.find('input[name=triggerTxt]').val(serviceObj.triggername);
			div.find('a#lnkChooseTrigger').show();
		} else { // a non-leaf node
			div.find('input[name=triggerId]').val('');
			div.find('input[name=triggerTxt]').val(y ? '('+I('not a leaf node, no trigger')+')' : '');
			div.find('a#lnkChooseTrigger').hide();
		}

		for(var i = 0; i < props.length; ++i) {
			div.find('input[name=w'+props[i]+']').val(y ? serviceObj.weight[props[i]] : '');
			div.find('input[name=t'+props[i]+']').val(y ? serviceObj.threshold[props[i]] : '');
		}
	}

	div.find('input[name=rootServiceName]').val(rootServiceName); // keep service name on input/hidden
	FillFields(null); // clear fields

	// Displaying the form.

	var popup = div.modalForm({ hasCancel:true, title:I('Edit service') });

	var events = {
		lnkDeleteService: function(ev) {
			$('<span>'+I('Remove service')+'...</span>').modalForm({ event:ev });
			return false;
		},

		lnkCreateChildService: function(ev) {
			$('<span>'+I('New child')+'...</span>').modalForm({ event:ev });
			return false;
		},

		lnkChangeParentService: function(ev) {
			var rootName = div.find('input[name=rootServiceName]').val();
			var curParentId = div.find('input[name=parentServiceId]').val();

			var dlgChooseParent = ShowChooseParent(rootName, nodeObj.data.serviceid, curParentId); // dialogChooseParent.php
			dlgChooseParent.ok(function(retService) {
				div.find('input[name=parentServiceName]').val(retService.name); // put back new values
				div.find('input[name=parentServiceId]').val(retService.id);
			});
			return false;
		},

		lnkChooseTrigger: function(ev) {
			var trId = div.find('input[name=triggerId]'); // pointers to INPUTs; get current trigger ID/name
			var trName = div.find('input[name=triggerTxt]');

			var dlgChooseTrigger = ShowChooseTrigger(trId.val(), trName.val()); // dialogChooseTrigger.php
			dlgChooseTrigger.ok(function(retTrigger) {
				trId.val(retTrigger.triggerid); // put new values on INPUTs
				trName.val(retTrigger.name);
			});
			return false;
		},

		chkToggleSla: function() {
			div.find('input[name=goodSla]').prop('readonly', !$(this).prop('checked'));
		},

		btnSetDefaults: function(ev) {
			for(var i = 0; i < props.length; ++i) {
				div.find('input[name=w'+props[i]+']').val(i == 0 ? 0 : Math.pow(10, i - 1));
				div.find('input[name=t'+props[i]+']').val(i == 0 ? 0 : Math.pow(10, i - 1));
			}
		}
	};

	var unbindEvents = function() {
		div.find('a#lnkDeleteService').off('click');
		div.find('a#lnkNewChildService').off('click');
		div.find('a#lnkChangeParent').off('click');
		div.find('a#lnkChooseTrigger').off('click');
		div.find('input[name=showSla]').off('change');
		div.find('input[name=setdef]').off('click');
	};

	popup.ok(unbindEvents);     // after user clicks OK
	popup.cancel(unbindEvents); // after user clicks Cancel

	popup.ready(function() { // right before form appears
		var xhr = $.post('ajaxService.php', { serviceId:nodeObj.data.serviceid }); // get service data
		xhr.fail(function(response) {
			$('<span>'+I('Failed to query service')+'.<br/>' +
				response.status+': '+response.statusText+'<br/>' +
				response.responseText+'</span>'
			).modalForm({ title:I('Oops...') }).ok(function() { popup.abort(); });
		});
		xhr.done(function(data) { // we got service data, now put 'em in the fields
			div.find('a#lnkDeleteService').on('click', events.lnkDeleteService); // setup events
			div.find('a#lnkNewChildService').on('click', events.lnkCreateChildService);
			div.find('a#lnkChangeParent').on('click', events.lnkChangeParentService);
			div.find('a#lnkChooseTrigger').on('click', events.lnkChooseTrigger);
			div.find('input[name=showSla]').on('change', events.chkToggleSla);
			div.find('input[name=setdef]').on('click', events.btnSetDefaults);
			data.children = nodeObj.children;
			FillFields(data); // object with tons of info of the service
		});
	});

	popup.validateSubmit(function() {
		function ValidateFields() {
			if(div.find('input[name=name]').val() == '') {
				$('<span>'+I('The service must have a name')+'.</span>').modalForm({ title:I('Oops...') });
				return false;
			}
			for(var i = 0; i < props.length; ++i) {
				var filled = div.find('input[name=w'+props[i]+']').val() != ''
					&& div.find('input[name=t'+props[i]+']').val() != '';
				if(!filled) {
					$('<span>'+I('All values must be filled in')+'.</span>').modalForm({ title:I('Oops...') });
					return false;
				}
			}
			return true;
		}
		if(ValidateFields()) {
			unbindEvents();
			var retNode = { // build return object
				id: nodeObj.data.serviceid,
				name: div.find('input[name=name]').val(),
				//parent:
				algorithm: div.find('select[name=algorithm]').val(),
				goodsla: div.find('input[name=goodSla]').val(),
				showsla: div.find('input[name=showSla]').prop('checked') ? 1 : 0,
				triggerid: div.find('input[name=triggerId]').val()
			};
			if(retNode.triggerid == '') retNode.triggerid = null;
			for(var i = 0; i < props.length; ++i) {
				retNode['weight_'+props[i]] = div.find('input[name=w'+props[i]+']').val(),
				retNode['threshold_'+props[i]] = div.find('input[name=t'+props[i]+']').val()
			}
			popup.continueSubmit(retNode); // ok() event will receive a node object
		}
	});

	return popup; // can call ok() event to handle it
}
</script>