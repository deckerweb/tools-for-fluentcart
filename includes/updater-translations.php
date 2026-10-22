<?php
/**
 * Resolve shared updater messages in the host textdomain.
 *
 * @package ToolsForFluentCart
 * @copyright 2026 David Decker – DECKERWEB
 * @license GPL-2.0-or-later
 */
defined( 'ABSPATH' ) || exit;
/**
 * Resolve an updater source string in the host textdomain.
 * @param string $message English updater message.
 * @return string Translated host message, or original source text.
 */
return static function ( string $message ): string {
    // Literal calls let the host's normal translation extractor collect every source string.
    switch ( $message ) {
        case 'Private mode must be boolean.':
            return __( 'Private mode must be boolean.', 'tools-for-fluentcart' );
        case 'Invalid authentication provider.':
            return __( 'Invalid authentication provider.', 'tools-for-fluentcart' );
        case 'The plugin must be installed in a stable slug directory.':
            return __( 'The plugin must be installed in a stable slug directory.', 'tools-for-fluentcart' );
        case 'Invalid GitHub repository URL.':
            return __( 'Invalid GitHub repository URL.', 'tools-for-fluentcart' );
        case 'The private update could not be authorized. Check the repository credentials and refresh updates.':
            return __( 'The private update could not be authorized. Check the repository credentials and refresh updates.', 'tools-for-fluentcart' );
        case 'Could not create the update download file.':
            return __( 'Could not create the update download file.', 'tools-for-fluentcart' );
        case 'The private update download failed. Check credentials and try again.':
            return __( 'The private update download failed. Check credentials and try again.', 'tools-for-fluentcart' );
        case 'Could not access the update filesystem.':
            return __( 'Could not access the update filesystem.', 'tools-for-fluentcart' );
        case 'GitHub release does not contain the plugin main file.':
            return __( 'GitHub release does not contain the plugin main file.', 'tools-for-fluentcart' );
        case 'Could not prepare the GitHub release package.':
            return __( 'Could not prepare the GitHub release package.', 'tools-for-fluentcart' );
        case 'See the release on GitHub.':
            return __( 'See the release on GitHub.', 'tools-for-fluentcart' );
        case 'Git Updater is active. If a deckerweb installation or update fails, temporarily deactivate Git Updater and retry. Reactivate it afterwards if other plugins or themes need it. Only leave it disabled when those extensions have another reliable update provider.':
            return __( 'Git Updater is active. If a deckerweb installation or update fails, temporarily deactivate Git Updater and retry. Reactivate it afterwards if other plugins or themes need it. Only leave it disabled when those extensions have another reliable update provider.', 'tools-for-fluentcart' );
        case 'Manage installed plugins':
            return __( 'Manage installed plugins', 'tools-for-fluentcart' );
        case 'Dismiss this notice':
            return __( 'Dismiss this notice', 'tools-for-fluentcart' );
        case 'Permission denied.':
            return __( 'Permission denied.', 'tools-for-fluentcart' );
        case 'Public catalog callbacks require both providers and cannot be used in private mode.':
            return __( 'Public catalog callbacks require both providers and cannot be used in private mode.', 'tools-for-fluentcart' );
        case 'The approved release could not be verified.':
            return __( 'The approved release could not be verified.', 'tools-for-fluentcart' );
        case 'The approved release changed. Refresh updates first.':
            return __( 'The approved release changed. Refresh updates first.', 'tools-for-fluentcart' );
        default:
            return $message;
    }
};
