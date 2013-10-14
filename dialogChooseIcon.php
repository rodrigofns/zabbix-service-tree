<div id="dialogChooseIcon">
	<style type="text/css" scoped="scoped">
		div#dialogChooseIcon { display:none; }
		div#chooseIconWnd { }
		div#allIcons { width:550px; height:320px; border:1px solid #DADADA; overflow-y:scroll; }
		img.choosable { margin:5px; border:1px solid #DDD; cursor:pointer; }
		img.chosen { border:2px solid red; box-shadow:0px 0px 12px red; }
	</style>
	<div id="chooseIconWnd">
		<label><input type="radio" name="choice" value="none"/><?=I('No image')?></label> &nbsp;
		<label><input type="radio" name="choice" value="icon"/><?=I('Choose image')?>:</label>
		<div id="allIcons"></div>

	</div>
</div>

<script type="text/javascript">
function ShowChooseIcon() {
	var $div = $('div#dialogChooseIcon');
	$div.find('div#allIcons').empty();
	var imgObjs = [];
	var popup = $div.modalForm({ hasCancel:true, title:I('Choose icon') });

	function BuildImages() {
		var imgs = [];
		for(var i = 0; i < imgObjs.length; ++i) {
			var newimg = $('<img title="'+imgObjs[i].name+'" class="choosable" src="'+imgObjs[i].imageurl+'"/>');
			newimg.data('id', imgObjs[i].imageid); // keep ID into IMG element
			imgs.push(newimg);
		}
		$div.find('div#allIcons').append(imgs);
	}
	function BindEvents() {
		$div.find('input[name=choice]').on('change', function(ev) {
			var chosen = $div.find('input[name=choice]:checked').val();
			(chosen === 'icon') ? BuildImages() : $div.find('div#allIcons').empty();
		});
		$div.on('click', 'img', function(ev) {
			$div.find('img.chosen').removeClass('chosen');
			$(this).addClass('chosen');
		});
	}
	function UnbindEvents() {
		$div.find('input[name=choice]').off('change');
	}

	popup.ready(function() { // right before form appears
		$div.find('input[name=choice][value=none]').prop('checked', true);
		BindEvents();

		var xhr = $.post('ajaxService.php', { images:1 });
		xhr.fail(function(response) {
			$('<span>'+I('Failed to query images')+'.<br/>' +
				response.status+': '+response.statusText+'<br/>' +
				response.responseText+'</span>'
			).modalForm({ title:I('Oops...') }).ok(function() {
				popup.abort();
			});
		});
		xhr.done(function(data) {
			imgObjs = data; // keep array
		});
	});

	popup.ok(UnbindEvents);     // after user clicks OK
	popup.cancel(UnbindEvents); // after user clicks Cancel

	popup.validateSubmit(function() { // right after user clicks OK
		if($div.find('input[name=choice][value=none]').prop('checked') === true) { // use chose no image
			UnbindEvents();
			popup.continueSubmit(null);
		} else { // user chose an image
			if($div.find('img.chosen').length !== 1) {
				$('<span>'+I('No image has been selected.')+'</span>').modalForm({ title:I('Oops...') });
			} else {
				UnbindEvents();
				var $chosenImg = $div.find('img.chosen');
				popup.continueSubmit({ // build return object with image ID and URL
					imageid: $chosenImg.data('id'),
					imageurl: $chosenImg.attr('src')
				});
			}
		}
	});

	return popup; // can call ok() event
}
</script>