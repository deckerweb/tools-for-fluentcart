<?php
/**
 * Scoped opt-in cleanup; never delete FluentCart data.
 *
 * @package ToolsForFluentCart
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
delete_site_transient( 'ddw_ghru_' . substr( md5( 'https://github.com/deckerweb/tools-for-fluentcart' ), 0, 24 ) );
require_once __DIR__ . '/includes/deckerweb-plugin-library/lifecycle.php';
deckerweb_library_uninstall_v2( __DIR__ . '/tools-for-fluentcart.php' );

/**
 * Delete only this site's own option if its administrator opted in.
 *
 * @return void
 */
function tffc_uninstall_site(): void {
	$settings = get_option( 'tffc_cart_rules', [] );
	if ( is_array( $settings ) && ! empty( $settings['delete_data'] ) ) { delete_option( 'tffc_cart_rules' ); }
}
if ( is_multisite() ) {
	$offset = 0;
	do {
		$sites = get_sites( [ 'fields' => 'ids', 'number' => 100, 'offset' => $offset ] );
		foreach ( $sites as $site ) { switch_to_blog( (int) $site ); tffc_uninstall_site(); restore_current_blog(); }
		$offset += 100;
	} while ( count( $sites ) === 100 );
} else { tffc_uninstall_site(); }
