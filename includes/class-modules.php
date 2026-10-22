<?php
/**
 * Module registration for Tools for FluentCart.
 *
 * @package ToolsForFluentCart
 * @copyright 2026 David Decker – DECKERWEB
 * @license GPL-2.0-or-later
 */
namespace Deckerweb\ToolsForFluentCart;
defined( 'ABSPATH' ) || exit;

/** Loads the shipped modules once. */
final class Modules {
	/**
	 * Load and register the shipped Cart Rules module.
	 * Settings control its behavior separately for each website.
	 *
	 * @return void
	 */
	public static function boot(): void {
		require_once TFFC_DIR . 'modules/cart-rules/module.php';
	}
}
