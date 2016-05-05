<?php
/*
Plugin Name: Oomph Clone Widgets
Plugin URI: http://www.thinkoomph.com/plugins-modules/oomph-clone-widgets/
Description: Add a "+" button on Widgets that will copy them along with all of their settings into a new widget.
Author: Ben Doherty @ Oomph, Inc.
Version: 2.1
Author URI: http://www.oomphinc.com/thinking/author/bdoherty/
License: GPLv2 or later

		Copyright © 2016 Oomph, Inc. <http://oomphinc.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @package Oomph Clone Widgets
 */
class Oomph_Clone_Widgets {
	function __construct() {
		add_filter( 'admin_head', array( $this, 'clone_script'  )  );
	}

	function clone_script() {
		global $pagenow;

		if( $pagenow != 'widgets.php' )
			return;
?>
<style>
	.oomph-cloneable .clone-widget-action { float: left; }
	.oomph-cloneable .widget-title h4 { padding-left: 0; }
	.oomph-cloneable a.clone-widget {
		right: 0;
		content: "\f140";
		border: none;
		background: none;
		color: #a0a5aa;
		font: normal 20px/1 dashicons;
		speak: none;
		display: block;
		padding: 0;
		text-indent: 0;
		text-align: center;
		position: relative;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
		text-decoration: none !important;
		box-shadow: none; outline: none; text-decoration: none; margin-top: 10px; cursor: pointer; position: relative; z-index: 10;
	}
	.oomph-cloneable a.clone-widget::after { content: "\f132" !important; margin-left: 10px; margin-right: 0; }
	.oomph-cloneable:hover a.clone-widget { cursor: pointer; }
</style>
<script>
(function($) {
	if(!window.Oomph) window.Oomph = {};

	Oomph.CloneWidgets = {
		init: function() {
			$(document.body).bind('click.widgets-clone', function(e) {
				var $target = $(e.target);

				if($target.closest('.clone-widget-action').length && !$target.parents('#available-widgets').length) {
					e.stopPropagation();
					e.preventDefault();
					e.stopImmediatePropagation();

					Oomph.CloneWidgets.Clone($target.parents('.widget'));
				}
			});

			Oomph.CloneWidgets.Insert();
		},

		Insert: function() {
			$('#widgets-right').off('DOMSubtreeModified', Oomph.CloneWidgets.Insert);
			$('#widgets-right .widget:not(.oomph-cloneable)').each(function() {
				var $widget = $(this)
					, $clone = $('<a class="clone-widget" title="Clone this widget">')
				;

				$widget.addClass('oomph-cloneable')
					.find('.widget-top')
					.prepend($('<div class="widget-title-action clone-widget-action">').append($clone))
				;

				$widget.addClass('oomph-cloneable');
			});
			$('#widgets-right').on('DOMSubtreeModified', Oomph.CloneWidgets.Insert);
		},

		Clone: function($original) {
			var $widget = $original.clone();

			// Find this widget's ID base. Find its number, duplicate.
			var idbase = $widget.find('input.id_base').val()
				, $source = $('#available-widgets').find('.id_base[value="' + idbase + '"]').parents('.widget')
				, widgetId = $source.find('.widget-id').val()
				, multi = parseInt($source.find('.multi_number').val())
				, number = parseInt($widget.find('.widget_number').val())
				, newNum = number + 1
			;

			$widget.find('.widget-content').find('input,select,textarea').each(function() {
				$(['name', 'id']).each(function(i, attr) {
					var val = $(this).attr(attr);

					if(val) {
						$(this).attr(attr, val.replace(new RegExp('([-\\[])' + number + '([-\\]]?|$)'), '$1' + newNum + '$2'));
					}
				});
			});

			// assign a unique id to this widget:
			var newid = 0;
			$('.widget').each(function() {
				var match = this.id.match(/^widget-(\d+)/);

				if(match && parseInt(match[1]) > newid)
					newid = parseInt(match[1]);
			});

			newid++;

			// Figure out the value of add_new from the source widget:
			var add = $source.find('.add_new').val();

			// Compute new widget ID and multi number
			if ('multi' === add) {
				multi++;
				$widget.attr( 'id', 'widget-' + newid + '_' + widgetId.replace('__i__', multi));
				$source.find('input.multi_number').val(multi);
				$widget.find('.multi_number').val(multi);
				$widget.find('input.widget-id').val(idbase + '-' + multi)
			} else if ( 'single' === add ) {
				$widget.attr('id', 'new-' + widgetId);
				$widget.find('input.widget-id').val(idbase);
			}

			$widget.find('input.add_new').val(add);
			$widget.find('input.widget_number').val(newNum);
			$widget.hide();
			$original.after($widget);
			$original.removeClass('open').find('.widget-inside').hide();
			$widget.addClass('open').find('.widget-inside').show();
			$widget.fadeIn(300).fadeOut(300).fadeIn(300);

			wpWidgets.save($widget, 0, 0, 1);
		}
	}

	$(Oomph.CloneWidgets.init);
})(jQuery);

</script>
<?php
	}
}

$GLOBALS['Oomph_Clone_Widgets'] = new Oomph_Clone_Widgets();
