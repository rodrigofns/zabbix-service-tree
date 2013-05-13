/*!
 * jQuery modal popup form plugin.
 * Date: Nov 7, 2012.
 * Dependency: jQuery v1.8.3 or higher.
 * Source: https://github.com/rodrigocfd/jquery-modalForm
 *
 * Copyright (c) 2012 Rodrigo Cesar de Freitas Dias
 * Released under the MIT license, see license.txt for details.
 */

(function( $ ) {
var globalOpts = {
	labelOk: 'OK',
	labelCancel: 'Cancel',
	background: '#FFF',
	bottomLine: '1px solid #E4E4E4', // horizontal line above the buttons
	borderRadius: 0,
	shadow: '3px 3px 28px #333',
	titleBackground: '#E2E2E2', // background CCS property for the titlebar, if a titlebar is shown
	titleColor: '', // color CSS property for the titlebar
	titleIcon: '', // URL of a small image to be placed at left side of titlebar
	coverColor: '#000',
	coverOpacity: 0.42,
	closeOnEsc: true,
	closeOnOutsideClick: true, // click on the cover div which hides the page below the popup
	disableF5: true // page may still be reloaded, but the F5 key will be disabled when a popup is active
};
$.extend({
	modalForm: function(options) {
		$.extend(globalOpts, options); // to set global options, use $.modalForm({ opts... });
	}
});
$.fn.modalForm = function modalForm(options) {
	var userOpts = $.extend({
		background: '',      // if set, overrides default background
		bottomLine: '',      // if set, overrides style for horizontal line above buttons
		hasCancel: false,    // if hasCancel, then has OK/Cancel buttons, otherwise only OK
		event: null,         // an Event object, of originated from a mouse click
		title: '',           // if set, popup will have a titlebar
		titleBackground: '', // if set, overrides default titlebar background
		titleColor: '',      // if set, overrides default titlebar color
		titleIcon: ''        // if set, overrides default titlebar icon
	}, options);

	if(modalForm.g_stack === undefined)
		modalForm.g_stack = []; // static variable to hold all stacked objects in order
	var stack = modalForm.g_stack; // just a pointer to reduce typing

	stack.push({ // new stacked object will be current object
		callbacks: { ready:null, validateSubmit:null, // user callbacks
			ok:[], cancel:[] }, // multiple ok() and cancel() are supported
		opts: userOpts,
		target: $(this[0]), // if more than 1 element was passed, consider just first
		onTheFly: this.parent().length == 0 // if created on the fly, object will be deleted after
	});

	var retObj = { },
		iStack = stack.length - 1, // index of current stacked object to work upon
		lastStacked = stack[iStack]; // current stacked object

	var htmls = {
	coverDiv:
		'<div id="jQueryModalForm_cover" style="' + // div to block the page, unique
			'position:absolute;' +
			'left:0;' +
			'top:0;' +
			'height:' + $(document).height() + 'px;' +
			'width:' + $(document).width() + 'px;' +
			'background:' + globalOpts.coverColor + ';' +
			'opacity:' + globalOpts.coverOpacity + ';' +
			'z-index:90000;' + // 1 div: 90000; 2 divs: 90002; and so on
		'"></div>',
	cageDiv:
		'<div id="jQueryModalForm_cage_' + iStack + '" style="' + // div to be popped with the user div inside
			'display:none;' +
			'position:fixed;' +
			'box-shadow:' + globalOpts.shadow + ';' +
			'-moz-box-shadow:' + globalOpts.shadow + ';' +
			'border-radius:' + globalOpts.borderRadius + ';' +
			'-moz-border-radius:' + globalOpts.borderRadius + ';' +
			//'padding:3px;' +
			'z-index:' + (90001 + iStack * 2) + ';' + // 1st div is 90001; 2nd div is 90003; and so on
		'">' +
		(userOpts.title != '' ? // titlebar, if any
		'<div style="' +
			'background:' + (userOpts.titleBackground != '' ? userOpts.titleBackground : globalOpts.titleBackground) + ';' +
			'color:' + (userOpts.titleColor != '' ? userOpts.titleColor : globalOpts.titleColor) + ';' +
			'padding:4px 6px;' +
			'font-weight:bold;' +
		'">' +
			(globalOpts.titleIcon != '' || userOpts.titleIcon != '' ?
			'<img src="' + (userOpts.titleIcon != '' ? userOpts.titleIcon : globalOpts.titleIcon) + '" style="vertical-align:bottom;"/> ' : '') +
			userOpts.title +
		'</div>' : '') +
		'<form style="' + // the form to hold the user element
				'margin:0;' +
				'padding:5px;' +
				'background:' + (userOpts.background != '' ? userOpts.background : globalOpts.background) + ';' +
			'"><table>' +
			'<tr><td style="padding-bottom:10px;"></td></tr>' +
			'<tr><td style="text-align:' + (userOpts.hasCancel ? 'right' : 'center') + '; padding-top:6px; border-top:' + (userOpts.bottomLine != '' ? userOpts.bottomLine : globalOpts.bottomLine) + ';">' +
				'<input type="submit" value="' + globalOpts.labelOk + '"/> ' +
				(userOpts.hasCancel ? '<input type="button" value="' + globalOpts.labelCancel + '"/></td></tr>' : '') +
		'</table></form>' +
		'</div>'
	};

	var calcPageCenter = function(elem) {
		return {
			x: $(window).width() / 2 - elem.width() / 2, // x,y coord to center on page
			y: Math.max($(window).height(), $(document).height()) / 2 - elem.height() / 2,
			cx: elem.width(), // also return current dimensions
			cy: elem.height()
		};
	};

	var reCenterOnPage = function() {
		var divCage = $('div#jQueryModalForm_cage_' + iStack),
			rect = calcPageCenter(divCage);
		divCage.css({ left:rect.x, top:rect.y });
	};

	var cleanup = function(choice, args) {
		var lastStacked = stack[stack.length - 1], // make sure we work with topmost popup
			divCage = $('div#jQueryModalForm_cage_' + (stack.length - 1)),
			divCover = $('div#jQueryModalForm_cover');

		lastStacked.target.appendTo('body').hide(); // remove user element from inside popup cage
		if(lastStacked.onTheFly) lastStacked.target.remove();
		divCage.remove();

		if(stack.length == 1) { // last stacked popup being closed
			$('body').css({ overflow:'auto' });
			$(document).off('keydown.jQueryModalForm');
			divCover.off('click.jQueryModalForm').remove();
		} else { // there are other popups behind this one
			divCover.css({ 'z-index':(90000 + (stack.length - 2) * 2) }); // move cover to behind
			$('div#jQueryModalForm_cage_' + (stack.length - 2) + ' form :input:visible:first').focus();
		}

		// Now let's call the final callbacks, set by ok() and cancel().
		// The "this" will point to retObj, the object returned by the $().modalForm() call.
		lastStacked = stack.pop();
		var cbks = lastStacked.callbacks;
		if(!lastStacked.opts.hasCancel || choice == 'ok') // OK-only popup, call both ok and cancel callbacks
			for(var i = 0; i < cbks.ok.length; ++i)
				cbks.ok[i].apply(retObj, args); // args come from validateSubmit() or abort(); pass to final callback

		if(!lastStacked.opts.hasCancel || choice == 'cancel')
			for(var i = 0; i < cbks.cancel.length; ++i)
				cbks.cancel[i].apply(retObj, args);
	};

	var destroyModal = function(choice, args) {
		var lastStacked = stack[stack.length - 1], // make sure we work with topmost popup
			divCage = $('div#jQueryModalForm_cage_' + (stack.length - 1));

		if(lastStacked.opts.event !== null) { // effect: implode to clicked point
			divCage.animate({
				left: lastStacked.opts.event.clientX,
				top: lastStacked.opts.event.clientY,
				width: 0,
				height: 0
			}, 200, function() {
				divCage.css({ width:'auto', height:'auto' });
				cleanup(choice, args);
			});
		} else { // effect: fade out
			divCage.fadeOut(180, function() { cleanup(choice, args); });
		}
	};

	var setupEvents = function() {
		var divCover = $('div#jQueryModalForm_cover'),
			form = $('div#jQueryModalForm_cage_' + iStack + ' form');

		if(iStack == 0) { // first stacked popup being opened
			$(document).on('keydown.jQueryModalForm', function(ev) {
				if(globalOpts.closeOnEsc && ev.keyCode == 27) // close on ESC
					destroyModal('cancel');
				if(globalOpts.disableF5 && ev.which == 116) // disable F5
					ev.preventDefault();
			});
			divCover.on('click.jQueryModalForm', function() {
				if(globalOpts.closeOnOutsideClick) // dismiss when clicking the cover div
					destroyModal('cancel');
				else
					form.find(':input:visible:first').focus();
			});
		}
		form.submit(function() { // user clicked OK
			if(lastStacked.callbacks.validateSubmit !== null) {
				// If this callback was set, user must call continueSubmit() to close popup.
				// The "this" will point to retObj, the object returned by the $().modalForm()
				// call, so this.continueSubmit() works.
				lastStacked.callbacks.validateSubmit.apply(retObj);
			} else {
				destroyModal('ok'); // if this callback was not set, OK directly closes the popup
			}
			return false;
		});
		form.find('input[value=' + globalOpts.labelCancel + ']').click(function() { // user clicked Cancel
			destroyModal('cancel');
		});
		form.find(':input:visible:first').focus();
		if(lastStacked.callbacks.ready !== null) {
			// Invoke modal's "document.ready" user callback.
			// The "this" will point to retObj, the object returned by the $().modalForm() call.
			lastStacked.callbacks.ready.apply(retObj);
		}
	};

	var showModal = function() {
		if(iStack == 0) { // first stacked popup being opened
			$('body').css({ overflow:'hidden' }).append(htmls.coverDiv);
		} else { // there are other popups already stacked
			$('div#jQueryModalForm_cover').css({ 'z-index':(90000 + iStack * 2) }); // move ahead
		}
		$('body').append(htmls.cageDiv);

		var divCage = $('div#jQueryModalForm_cage_' + iStack);
		lastStacked.target
			.appendTo(divCage.find('form td:first')) // put user element into cage
			.show();

		var rect = calcPageCenter(divCage);
		if(userOpts.event !== null) { // effect: "boom" from clicked point
			divCage.css({
				left: userOpts.event.clientX, // set origin
				top: userOpts.event.clientY,
				width: 0,
				height: 0
			}).show().animate({ // now to destination
				left: rect.x,
				top: rect.y,
				width: rect.cx,
				height: rect.cy
			}, 200, function() {
				divCage.css({ width:'auto', height:'auto' });
				setupEvents();
			});
		} else { // effect: fade in
			divCage.css({ left:rect.x, top:rect.y })
				.fadeIn(180, setupEvents);
		}
	};

	showModal();

	return $.extend(retObj, {
		ok: function(callback) { lastStacked.callbacks.ok.push(callback); return this; },
		cancel: function(callback) { lastStacked.callbacks.cancel.push(callback); return this; },
		ready: function(callback) { lastStacked.callbacks.ready = callback; return this; },
		abort: function() { destroyModal('cancel', arguments); },
		validateSubmit: function(callback) { lastStacked.callbacks.validateSubmit = callback; return this; },
		continueSubmit: function() { destroyModal('ok', arguments); },
		centerOnPage: reCenterOnPage
	});
};
})( jQuery );