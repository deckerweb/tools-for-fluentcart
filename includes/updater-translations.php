<?php
/**
 * Translate shared updater messages through the host textdomain.
 *
 * @package ToolsForFluentCart
 */
defined( 'ABSPATH' ) || exit;
/**
 * Return a translated message without changing the shared engine.
 *
 * @param string $message Original engine message.
 * @return string Localized message, or unchanged unknown input.
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
        default:
            return $message;
    }
};
