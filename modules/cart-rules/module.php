<?php
/**
 * Cart Rules module entry point.
 *
 * @package ToolsForFluentCart
 * @copyright 2026 David Decker – DECKERWEB
 * @license GPL-2.0-or-later
 */
defined( 'ABSPATH' ) || exit;
require_once TFFC_DIR . 'includes/class-rules.php';
require_once TFFC_DIR . 'includes/class-settings.php';
require_once TFFC_DIR . 'includes/class-integration.php';
add_action( 'init', [ \Deckerweb\ToolsForFluentCart\Settings::class, 'translations' ], 0 );
add_action( 'plugins_loaded', [ \Deckerweb\ToolsForFluentCart\Integration::class, 'boot' ], 30 );
add_action( 'admin_init', [ \Deckerweb\ToolsForFluentCart\Settings::class, 'register' ] );
