var vip_scanner_instance = vip_scanner();

function vip_scanner() {
	var $ = jQuery;
	var issue_count_interval = 0;

	update_issue_count();
	setup_periodic_update();

	function do_vip_scan( e ) {
		$.ajax( ajaxurl, {
			data: {
				action: 'vip-scanner-do_async_scan',
			},
		} ).complete( process_scanner_results );

		e.preventDefault();
		return false;
	}

	function update_issue_count() {
		$.ajax( ajaxurl, {
			data: {
				action: 'vip-scanner-get_errors',
			},
		} ).complete( process_scanner_results );
	}

	function process_scanner_results( results ) {
		$(function(){
			if ( typeof results.responseJSON === 'undefined' ) {
				return;
			}

			var response = results.responseJSON;
			var issue_count = 0;

			for ( var issue_type in response.data.issues ) {
				var issue_type_count = Object.keys( response.data.issues[issue_type] ).length;
				var issue_type_text = vip_scanner_i18n.levels[issue_type].none;

				issue_count += issue_type_count;

				if ( 1 === issue_type_count ) {
					issue_type_text = vip_scanner_i18n.levels[issue_type].single;
				} else if ( 1 < issue_type_count ) {
					issue_type_text = vip_scanner_i18n.levels[issue_type].multiple.replace( '{issue_count}', issue_type_count );
				}

				$( '#wp-admin-bar-vip-scanner-' + issue_type ).children().html( issue_type_text );
			}

			var issue_text = '';
			if ( 0 === issue_count ) {
				issue_text = vip_scanner_i18n.no_issues;
			} else if ( 1 === issue_count ) {
				issue_text = vip_scanner_i18n.single_issue;
			} else {
				issue_text = vip_scanner_i18n.multiple_issues.replace( '{issue_count}', issue_count );
			}

			$( '#wp-admin-bar-vip-scanner a' ).html( issue_text );

			$( '#wp-admin-bar-vip-scanner-theme' ).children().html( vip_scanner_i18n.theme_header.replace( '{theme_name}',  response.data.theme ) );
			$( '#wp-admin-bar-vip-scanner-review' ).children().html( vip_scanner_i18n.review_header.replace( '{review_name}',  response.data.review ) );
		});
	}

	function setup_periodic_update() {
		if ( ! issue_count_interval ) {
			issue_count_interval = setInterval( update_issue_count, 60000 );
		}
	}

	function cancel_periodic_update() {
		if ( issue_count_interval ) {
			clearInterval( issue_count_interval );
			issue_count_interval = 0;
		}
	}

	return {
		'do_vip_scan': do_vip_scan,
		'update_issue_count': update_issue_count,
		'process_scanner_results': process_scanner_results,
		'cancel_periodic_update': cancel_periodic_update,
	};
}