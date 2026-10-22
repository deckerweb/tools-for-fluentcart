<?php
/**
 * Scoped opt-in cleanup; never delete FluentCart data.
 *
 * @package ToolsForFluentCart
 * @copyright 2026 David Decker – DECKERWEB
 * @license GPL-2.0-or-later
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
$tffc_updater = require __DIR__ . '/includes/deckerweb-github-release-updater-v2.php';
$tffc_updater::clear_repository_cache( 'https://github.com/deckerweb/tools-for-fluentcart' );
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
	$tffc_offset = 0;
	do {
		$tffc_sites = get_sites( [ 'fields' => 'ids', 'number' => 100, 'offset' => $tffc_offset ] );
		foreach ( $tffc_sites as $tffc_site ) { switch_to_blog( (int) $tffc_site ); tffc_uninstall_site(); restore_current_blog(); }
		$tffc_offset += 100;
	} while ( count( $tffc_sites ) === 100 );
} else { tffc_uninstall_site(); }
