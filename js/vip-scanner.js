jQuery(document).ready(function($){
	$('.nav-tab-wrapper').each(function(){		
		var $active,
			$content,
			$links = $(this).find('a');

		$active = $links.first();
		$active.addClass('nav-tab-active');
		$content = $($active.attr('href'));

		$links.not($active).each(function () {
			$($(this).attr('href')).hide();
		});

		$(this).on('click', 'a', function(e){
			$active.removeClass('nav-tab-active');
			$content.hide();
			$active = $(this);
			$content = $($(this).attr('href'));
			$active.addClass('nav-tab-active');
			$content.show();
			e.preventDefault();
		});
	});
	
	$( '#analysis-accordion, .renderer-group-children' ).accordion( {
		heightStyle: "content",
		collapsible: true,
		active: false,
		beforeActivate: function (event, ui) {
			// Disable this panel if its empty
			return ! $( event.currentTarget ).hasClass( 'renderer-group-empty' );
		}
	} ).accordion( "option", "animate", "linear" );
});