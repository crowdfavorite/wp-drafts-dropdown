<?php

/*
Plugin Name: Drafts Dropdown 
Plugin URI: http://crowdfavorite.com/wordpress/plugins/drafts-dropdown/ 
Description: Easy access to your WordPress drafts from the admin bar. Drafts are listed in a slide-down menu.
Version: 2.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// Copyright (c) 2009-2011 
//   Crowd Favorite, Ltd. - http://crowdfavorite.com
//   Alex King - http://alexking.org
// All rights reserved.
//
// Released under the GPL license
// http://www.opensource.org/licenses/gpl-license.php
//
// This is an add-on for WordPress - http://wordpress.org
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// **********************************************************************

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

load_plugin_textdomain('drafts-dropdown');

function cfdd_get_drafts() {	
	$args = array(
	  'public' => true,
	);
	$post_types = get_post_types($args, 'names');
	$query = array( 
		'post_type' => $post_types, 
		'post_status' => 'draft',
		'posts_per_page' => 100,
		'order' => 'DESC',
		'orderby' => 'modified',
	);
	
	$drafts = new WP_Query($query);
	return $drafts->posts;
}

function cfdd_drafts_content() {
	$output = '';
	$drafts = cfdd_get_drafts();
	if (count($drafts)) {
		$output .= '<ul id="cfdd_drafts">';
		foreach ($drafts as $draft) {
			$post_title = !empty($draft->post_title) ? esc_html($draft->post_title) : __('(untitled)', 'drafts-dropdown');
			$output .= '<li><a href="'.esc_url(admin_url('post.php?action=edit&post='.$draft->ID)).'">'.$post_title.'</a></li>';
		}
		$output .= '</ul>';
	}
	else {
		$output .= '<p>'.__('(none)', 'drafts-dropdown').'</p>';
	}
	return $output;
}

function cfdd_ajax_drafts_list() {
	if (!current_user_can('edit_posts')) {
		return false;
	}
	$html = cfdd_drafts_content();
	header('Content-type: application/json');
	echo json_encode(compact('html'));
	die();
}
add_action('wp_ajax_cfdd_drafts_list', 'cfdd_ajax_drafts_list');

function cfdd_footer() {
?>
<style type="text/css">
#cfdd_drafts_wrap {
	background: #444;
	border-top: 1px solid #999;
	border-bottom: 5px solid #666;
	-webkit-box-shadow: 0px 2px 2px rgba(0,0,0,0.8), inset 0px 0px 4px rgba(0,0,0,0.5);
	box-shadow: 0px 2px 4px rgba(0,0,0,0.8), inset 0px 0px 4px rgba(0,0,0,0.5);
	color: #fff;
	display: none;
	height: 400px;
	left: 0;
	max-height: 400px;
	overflow: auto;
	padding: 15px 0;
	position: absolute;
	top: 28px;
	width: 100%;
	z-index: 100;
}
#cfdd_drafts_wrap.loading {
	background: #444 url(<?php echo admin_url('images/wpspin_dark.gif'); ?>) no-repeat center center;
}
#cfdd_drafts_wrap .cfdd_content {
	visibility: hidden;
}
#cfdd_drafts_wrap a,
#cfdd_drafts_wrap a:visited {
	color: #fff;
}
#cfdd_drafts_wrap .cfdd_col {
	border-right: 1px solid #777;
	float: left;
	margin: 0 0 0 15px;
	padding-right: 15px;
}
#cfdd_drafts_wrap .cfdd_col ul {
	font: 12px sans-serif;
	line-height: 140%;
	list-style: none;
	margin: 0;
	padding: 0;
}
#cfdd_drafts_wrap .cfdd_col ul li {
	margin: 0 0 6px;
}
</style>
<script type="text/javascript">
jQuery(function($) {
	$('#wp-admin-bar-cfdd_drafts_menu').click(function(e) {
		e.preventDefault();
// slide up
		$wrap = $('#cfdd_drafts_wrap');
		if ($wrap.size() && $wrap.is(':visible')) {
			$wrap.slideUp(function() {
				$(this).remove();
			});
			return;
		}
// slide down
		$('body').append('<div id="cfdd_drafts_wrap"><div class="cfdd_content"></div></div>');
// show spinner
		$wrap = $('#cfdd_drafts_wrap');
		$wrap.css({'height': '400px'}).slideDown().addClass('loading');
// load drafts
		$.post(
			'<?php echo admin_url('admin-ajax.php'); ?>',
			{
				action: 'cfdd_drafts_list'
			},
			function(response) {
				$content = $wrap.find('.cfdd_content');
				$content.html(response.html);
// format cols
				var drafts = $('#cfdd_drafts li');
				var drafts_count = drafts.size();
				var i = 0;
				if (drafts_count <= 10) {
// set to 2 columns
					$content.append('<div class="cfdd_col" id="cfdd_col_1"><ul></ul></div><div class="cfdd_col" id="cfdd_col_2"><ul></ul></div><div class="cfdd_clear"></div>');
					var col_count = Math.ceil(drafts_count / 2);
					drafts.each(function() {
						i < col_count ? target = '#cfdd_col_1 ul' : target = '#cfdd_col_2 ul';
						$(this).appendTo(target);
						i++;
					});
				}
				else {
// 3 columns
					$content.append('<div class="cfdd_col" id="cfdd_col_1"><ul></ul></div><div class="cfdd_col" id="cfdd_col_2"><ul></ul></div><div class="cfdd_col" id="cfdd_col_3"><ul></ul></div><div class="cfdd_clear"></div>');
					var col_count = Math.ceil(drafts_count / 3);
					drafts.each(function() {
						if (i < col_count) {
							target = '#cfdd_col_1 ul';
						}
						else if (i >= col_count * 2) {
							target = '#cfdd_col_3 ul';
						}
						else {
							target = '#cfdd_col_2 ul';
						}
						$(this).appendTo(target);
						i++;
					});
				}
				$('#cfdd_drafts').remove();
			// set size of cfdd_col
				$('.cfdd_col').width(Math.floor($('body').width() - 120) / 3);
				$('.cfdd_col:last').css('border-right', 0);

				var height = 0;
				$wrap.find('.cfdd_col').each(function() {
					if ($(this).height() > height) {
						height = $(this).height();
					}
				});
				if (height < 400) {
					$wrap.animate({ 'height': height + 'px' }, 'fast');
				}

// remove spinner, make visible
				$wrap.removeClass('loading');
				$content.hide().css({ 'visibility': 'visible' }).fadeIn();
			},
			'json'
		);
	});
});
</script>
<?php
}
// attached in admin bar call below for back end
// attached in init below for front end

function cfdd_admin_bar_menu_drafts($wp_admin_bar) {
	if (current_user_can('edit_posts')) {
		$wp_admin_bar->add_menu(array(
			'id' => 'cfdd_drafts_menu',
			'title' => __('Drafts', 'drafts-dropdown'),
			'href' => admin_url('edit.php?post_status=draft&post_type=post'),
		));
		add_action('admin_footer', 'cfdd_footer');
	}
}
add_action('admin_bar_menu', 'cfdd_admin_bar_menu_drafts', 45);

function cfdd_init() {
	if (!is_admin() && current_user_can('edit_posts')) {
		add_action('wp_footer', 'cfdd_footer');
	}
}
add_action('init', 'cfdd_init');

//a:22:{s:11:"plugin_name";s:15:"Drafts Dropdown";s:10:"plugin_uri";s:38:"http://alexking.org/projects/wordpress";s:18:"plugin_description";s:112:"Easy access to your WordPress drafts from within the web admin interface. Drafts are listed in a drop-down menu.";s:14:"plugin_version";s:3:"1.0";s:6:"prefix";s:4:"cfdd";s:12:"localization";s:14:"draft-dropdown";s:14:"settings_title";N;s:13:"settings_link";N;s:4:"init";b:0;s:7:"install";b:0;s:9:"post_edit";b:0;s:12:"comment_edit";b:0;s:6:"jquery";b:0;s:6:"wp_css";b:0;s:5:"wp_js";b:0;s:9:"admin_css";b:0;s:8:"admin_js";s:1:"1";s:15:"request_handler";b:0;s:6:"snoopy";b:0;s:11:"setting_cat";b:0;s:14:"setting_author";b:0;s:11:"custom_urls";b:0;}
