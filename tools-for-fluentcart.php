<?php
/**
 * Plugin Name: Tools for FluentCart
 * Plugin URI: https://deckerwebplugins.com/en/plugins/tools-for-fluentcart/
 * Description: An add-on for FluentCart: cart rules, product notes, store checks and catalog mode.
 * Version: 1.0.0
 * Requires at least: 7.1.2
 * Requires PHP: 8.2
 * Author: David Decker – DECKERWEB
 * Author URI: https://deckerwebplugins.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tools-for-fluentcart
 * Domain Path: /languages/
 * Update URI: https://github.com/deckerweb/tools-for-fluentcart
 * GitHub Plugin URI: https://github.com/deckerweb/tools-for-fluentcart
 *
 * Copyright © 2026 David Decker – DECKERWEB.
 * SPDX-License-Identifier: GPL-2.0-or-later
 */
defined( 'ABSPATH' ) || exit;
define( 'TFFC_VERSION', '1.0.0' );
define( 'TFFC_FILE', __FILE__ );
define( 'TFFC_DIR', __DIR__ . '/' );
require_once TFFC_DIR . 'includes/deckerweb-plugin-library/bootstrap.php';
deckerweb_library_register_v2( __FILE__, [], TFFC_DIR . 'includes/deckerweb-plugin-library' );
require_once TFFC_DIR . 'includes/class-modules.php';
\Deckerweb\ToolsForFluentCart\Modules::boot();
require_once TFFC_DIR . 'includes/class-admin.php';
require_once TFFC_DIR . 'includes/class-update.php';
add_action( 'init', [ \Deckerweb\ToolsForFluentCart\Update::class, 'register' ], 5 );
add_action( 'admin_menu', [ \Deckerweb\ToolsForFluentCart\Admin::class, 'menu' ], 999 );
add_action( 'admin_enqueue_scripts', [ \Deckerweb\ToolsForFluentCart\Admin::class, 'assets' ] );

add_filter( 'plugin_action_links_' . plugin_basename( TFFC_FILE ), [ \Deckerweb\ToolsForFluentCart\Admin::class, 'action_links' ] );
