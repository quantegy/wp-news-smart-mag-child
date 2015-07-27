/**
 * genesis-uci-menu-toggle.js
 *
 * Features:
 * -Enables menu navigation via keyboard, including esc to close
 * -Enables consistent dropdown function on touch devices
 * -Inserts close-menu controls in submenus on touch devices
 * -Manages focus on menu close
 *
 * Updated for News-Smart-Mag-Theme:
 * -removed close functionality (already built into theme)
 * -updated selectors to fit theme
 *
 * @author tmcgill
 * @version 20150721
 */
 

(function($) {
	"use strict";
	var skipOpen = false; // Used to suppress menu open during returnfocus
	
/** Test for touch device **/

	var touch = false;
	if (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch) {
		touch = true;
	}

/** Define DOM manipulation functions **/	

	function showMenu(navItem) {
		if (!skipOpen) {
			navItem.addClass('uci-menu-toggle-on').find('a').addClass('uci-menu-toggle-on');
		}
		skipOpen = false;
	}
	
	function hideMenu(navItem) {
		navItem.removeClass('uci-menu-toggle-on').find('a').removeClass('uci-menu-toggle-on');
	}
	
	function returnFocus() {
		if ($(':focus').parents('.sub-menu').length > 0) {
			skipOpen = true;
			$(':focus').parents('.sub-menu').prev('a').focus();
		}
	}

	
/** Bind to events **/		

	$('.menu > li').hover(
		function() {
			if (!touch) {
				showMenu($(this));
			}
		},
		function() {
			hideMenu($(this));
		}
	);
	
	$('.menu .menu-item-has-children a').click(
		function(event) {
			var open = $(this).hasClass('uci-menu-toggle-on');
			if (!open) {
				event.preventDefault();
				showMenu($(this).parents('.menu-item'));
			}
		}
	);
	

	$('.menu li a, .sub-menu-close button').on('focus',
		function() {
			showMenu($(this).parents('.menu-item'));
		}
	);

	$('.menu li a, .sub-menu-close button').on('blur',
		function() {
			hideMenu($(this).parents('.menu-item'));
		}
	);
	
	// Use ESC key to close menu
	$('.menu-item').keyup(
		function(event) {
			if (event.keyCode == 27) {
				returnFocus();
				hideMenu($(this));
			}
		}
	);
		
}

(jQuery)
);
