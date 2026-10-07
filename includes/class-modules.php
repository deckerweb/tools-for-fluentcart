<?php
/** Module registration for Tools for FluentCart. @package ToolsForFluentCart */
namespace Deckerweb\ToolsForFluentCart;
defined( 'ABSPATH' ) || exit;

/** Loads available modules once; future modules remain independent of Cart Rules. */
final class Modules {
	/**
	 * Load and register the shipped Cart Rules module.
	 * Settings control its behavior per website; no future module is enabled here.
	 *
	 * @return void
	 */
	public static function boot(): void {
		require_once TFFC_DIR . 'modules/cart-rules/module.php';
	}
}
