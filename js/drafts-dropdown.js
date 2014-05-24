jQuery( document ).ready( function($) {
	$( 'body' ).append( '<div id="cfdd_drafts_wrap"><div class="cfdd_content"></div></div>' );
} );
	
jQuery( document ).on( 'click', '#wp-admin-bar-cfdd_drafts_menu', function(e) {
	e.preventDefault();
	var $ = jQuery;
	
	// slide up
	$wrap = $( '#cfdd_drafts_wrap' );
	if ( $wrap.size() && $wrap.is( ':visible' ) ) {
		$wrap.slideUp( function() {
			$( this ).find( '.cfdd_content ul' ).remove();
		} );
		return;
	}
	

	$wrap = $( '#cfdd_drafts_wrap' );
	
	// show spinner
	$menuitem = $( '#wp-admin-bar-cfdd_drafts_menu' );
	$menuitem.addClass('loading');
	
	// load drafts
	$.post(
		WP_DraftsDropdown.ajax_url,
		{
			action: 'cfdd_drafts_list',
		},
		function ( response ) {
		
			$content = $wrap.find( '.cfdd_content' );
			var $length = Object.keys( response ).length;
			if ( 0 == $length ) {
				$content.append( '<ul class="nodrafts"><li>' + WP_DraftsDropdown.no_drafts + '</li></ul>' );
			} else {
				var $list = $content.append( '<ul></ul>' ).find( 'ul' );
				for ( var draft in response ) {
					$list.append( '<li><a href="' + WP_DraftsDropdown.edit_url + draft + '">' + response[draft].title + '</a></li>' );
				}
			}
			
			$list = $content.find( 'ul' );
			
			//we need this to get the actual height of the list
			var pos = $wrap.css( 'position' );
			$wrap.css( {
				position:   'absolute', 
				visibility: 'hidden',
				display:    'block'
			} );

			var actualHeight = ( $list.height() <= 400 ) ? $list.height() : 400;

			$wrap.css( {
				position:   pos, 
				visibility: 'visible',
				height: 0,
			} );
							
			$wrap.hide().height( actualHeight ).slideDown();
			$menuitem.removeClass('loading');
		},
		'json'
	);
} );
