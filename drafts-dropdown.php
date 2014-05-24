<?php

/*
Plugin Name: Drafts Dropdown 
_Plugin URI: http://crowdfavorite.com/wordpress/plugins/drafts-dropdown/ 
Description: Easy access to your WordPress drafts from the admin bar. Drafts are listed in a slide-down menu.
Version: 3.0.0
Author: Paul Ellmaier
Author URI: http://blog.anvor.at
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// Copyright (c) 2009-2011 
//   Crowd Favorite, Ltd. - http://crowdfavorite.com
//   Alex King - http://alexking.org
// All rights reserved.
// Copyright (c) 2014
//	Paul Ellmaier - http://blog.anvor.at
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


//Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) 
	exit;
	

if ( !class_exists( 'WP_DraftsDropdown' ) ):

	class WP_DraftsDropdown
	{
	
		public static function enable() {
		
			add_action('wp_ajax_cfdd_drafts_list', array( __CLASS__, 'ajax_drafts_list' ) );

			add_action('admin_bar_menu', array( __CLASS__, 'admin_bar_menu_drafts' ), 45);

			add_action('init', array( __CLASS__, 'init' ) );
		}
		
		public static function admin_bar_menu_drafts($wp_admin_bar) {
			if ( current_user_can( 'edit_posts' ) ) {
				$wp_admin_bar->add_menu( array(
					'id' => 'cfdd_drafts_menu',
					'title' => __( 'Drafts' ),
					'href' => admin_url( 'edit.php?post_status=draft&post_type=post' ),
				) );
			}
		}
		
		function init() {
			load_plugin_textdomain( 'drafts-dropdown' );
			
			wp_enqueue_script( 'drafts-dropdown', self::get_url() . 'js/drafts-dropdown.js', array( 'jquery' ) );
			$i18n = array( 
				'ajax_url'	=> admin_url( 'admin-ajax.php' ),
				'no_drafts'	=> __('No drafts found', 'drafts-dropdown'),
				'edit_url'	=> esc_url( admin_url( 'post.php?action=edit&post=' ) ),
			);
			wp_localize_script('drafts-dropdown', 'WP_DraftsDropdown', $i18n );
			
			wp_enqueue_style( 'drafts-dropdown', self::get_url() . 'css/drafts-dropdown.css' );
		}
		
		public static function get_drafts() {	
			$args = array(
			  'public' => true,
			);
			$post_types = get_post_types( $args, 'names' );
			$query = array( 
				'post_type'			=> $post_types, 
				'post_status'		=> 'draft',
				'posts_per_page'	=> 100,
				'order'				=> 'DESC',
				'orderby'			=> 'modified',
			);
			
			$drafts = new WP_Query( $query );
			return $drafts->posts;
		}
		
		public static function drafts_content() {
			$output = array();
			$drafts = self::get_drafts();

			foreach ( $drafts as $draft ) {
				$post_title = !empty( $draft->post_title ) ? esc_html( $draft->post_title ) : __( '(untitled)', 'drafts-dropdown' );
				//$modified = DateTime::createFromFormat('Y-m-d H:i:s', $draft->post_modified)->getTimestamp();
				$data = array( 
								'title'		=> $post_title, 
								/*'modified'	=> $modified,*/ //might consider sending more metadata in the future
							);
				$output[$draft->ID] = $data;
			}
			return json_encode($output);
		}
		
		public static function ajax_drafts_list() {
			if ( !current_user_can( 'edit_posts' ) ) {
				return false;
			}
			$html = self::drafts_content();
			header( 'Content-type: application/json' );
			echo $html;
			die();
		}
		
		/**
		 * Class should be able to be instantiated from a theme or plugin,
		 * so we need to find out the url to the current folder in a
		 * roundabout way.
		 */
		public static function get_url() {
			// get and normalize framework dirname
			$dirname = str_replace( '\\' ,'/', dirname( __FILE__ ) ); // standardize slash
			$dirname = preg_replace( '|/+|', '/', $dirname );       // normalize duplicate slash

			// get and normalize WP content directory
			$wp_content_dir = str_replace( '\\', '/', WP_CONTENT_DIR );  // standardize slash

			// build relative url
			$relative_url = str_replace( $wp_content_dir, "", $dirname );

			// finally base url
			return trailingslashit( content_url() . $relative_url );
		}
	}

endif;

WP_DraftsDropdown::enable();