<?php
/** Administrative rule editor. @package ToolsForFluentCart */
namespace Deckerweb\ToolsForFluentCart;
defined( 'ABSPATH' ) || exit;

/** Renders one functional settings page with native WordPress save handling. */
final class Admin {
	/** @var string Registered settings-screen hook; differs between parent menus. */
	private static string $screen = '';

	/**
	 * Select the actual registered shop menu, with a native settings fallback.
	 *
	 * @return string Existing FluentCart parent slug or the WordPress settings parent.
	 */
	private static function parent_slug(): string {
		global $menu;
		foreach ( (array) $menu as $item ) {
			if ( 'fluent-cart' === ( $item[2] ?? '' ) ) { return 'fluent-cart'; }
		}
		return 'options-general.php';
	}

	/**
	 * Resolve the accessible settings URL for the current website menu.
	 *
	 * @return string Absolute administration URL; no customer-facing redirect.
	 */
	public static function settings_url(): string {
		$path = 'fluent-cart' === self::parent_slug() ? 'admin.php' : 'options-general.php';
		return admin_url( $path . '?page=tools-for-fluentcart' );
	}

	/**
	 * Add a site-scoped page and append it after the native shop entries.
	 * Preserve FluentCart submenu keys and order; no network settings are introduced.
	 *
	 * @return void
	 */
	public static function menu(): void {
		global $submenu;
		$parent = self::parent_slug();
		$existing = $submenu[ $parent ] ?? null;
		self::$screen = (string) add_submenu_page( $parent, 'Tools for FluentCart', 'Tools for FluentCart', 'manage_options', 'tools-for-fluentcart', [ self::class, 'render' ] );
		if ( 'fluent-cart' === $parent && is_array( $existing ) && '' !== self::$screen ) {
			// Core sorts numeric keys before FluentCart's named keys. Restore the
			// existing order and keys, then append only this plugin's registered row.
			foreach ( $submenu[ $parent ] as $item ) {
				if ( 'tools-for-fluentcart' === ( $item[2] ?? '' ) ) {
					unset( $existing['tffc_tools'] );
					$existing['tffc_tools'] = $item;
					$submenu[ $parent ] = $existing;
					break;
				}
			}
		}
	}

	/**
	 * Prepend the representative settings link on site plugin lists.
	 *
	 * @param array<string,string> $links Existing plugin actions.
	 * @return array<string,string> Actions with settings first when accessible.
	 */
	public static function action_links( array $links ): array {
		if ( is_network_admin() || ! current_user_can( 'manage_options' ) ) { return $links; }
		return [ 'settings' => '<a href="' . esc_url( self::settings_url() ) . '">' . esc_html__( 'Settings', 'tools-for-fluentcart' ) . '</a>' ] + $links;
	}

	/**
	 * Load editor assets only on this plugin's settings page.
	 *
	 * @param string $hook Current WordPress screen hook suffix.
	 * @return void
	 */
	public static function assets( string $hook ): void {
		if ( '' === self::$screen || self::$screen !== $hook ) { return; }
		wp_enqueue_style( 'tffc-admin', plugins_url( 'assets/admin.css', TFFC_FILE ), [], TFFC_VERSION );
		wp_enqueue_script( 'tffc-admin', plugins_url( 'assets/admin.js', TFFC_FILE ), [], TFFC_VERSION, true );
	}

	/**
	 * Render the accessible settings form and local release history.
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$s = Settings::get();
		$presets = [ 'custom' => __( 'Custom rules', 'tools-for-fluentcart' ), 'single' => __( 'One item only', 'tools-for-fluentcart' ), 'minimum' => __( 'Minimum quantity', 'tools-for-fluentcart' ), 'value' => __( 'Minimum order value', 'tools-for-fluentcart' ), 'maximum' => __( 'Quantity limits', 'tools-for-fluentcart' ), 'steps' => __( 'Quantity steps', 'tools-for-fluentcart' ) ];
		echo '<div class="wrap tffc-admin"><header class="tffc-header"><img class="tffc-mark" src="' . esc_url( plugins_url( 'assets/brand/icon.svg', TFFC_FILE ) ) . '" width="64" height="64" alt=""><div><h1>Tools for FluentCart</h1><p>' . esc_html__( 'Fine-tune your store.', 'tools-for-fluentcart' ) . '</p></div></header>';
		// WordPress moves standard notices after this marker, outside the complete header.
		echo '<div id="tffc-notices" class="tffc-notices"><hr class="wp-header-end"></div>';
		echo '<form action="options.php" method="post" id="tffc-form">'; settings_fields( 'tffc' );
		echo '<section class="tffc-card"><h2>' . esc_html__( 'Cart Rules', 'tools-for-fluentcart' ) . '</h2>';
		self::checkbox( 'enabled', __( 'Enable cart rules', 'tools-for-fluentcart' ), $s['enabled'] );
		echo '<p>' . esc_html__( 'Rules start disabled. Configure them here, then enable them for this website. Other websites keep their own settings.', 'tools-for-fluentcart' ) . '</p>';
		echo '<label for="tffc-preset"><strong>' . esc_html__( 'Start with a preset', 'tools-for-fluentcart' ) . '</strong></label><p><select id="tffc-preset">';
		foreach ( $presets as $key => $label ) { echo '<option value="' . esc_attr( $key ) . '" ' . selected( $s['single'] ? 'single' : 'custom', $key, false ) . '>' . esc_html( $label ) . '</option>'; }
		echo '</select></p><p>' . esc_html__( 'Selecting a preset fills the rule fields. Review the values before saving. You can combine rules in custom mode.', 'tools-for-fluentcart' ) . '</p>';
		self::checkbox( 'single', __( 'Allow one product with quantity one', 'tools-for-fluentcart' ), $s['single'] );
		echo '<p>' . esc_html__( 'In this mode, a new product replaces the previous one and quantity controls are hidden. Existing open carts are reduced to the last item on their next visit. The other quantity and value rules are cleared when saving.', 'tools-for-fluentcart' ) . '</p></section>';
		echo '<section class="tffc-card" id="tffc-custom"><h2>' . esc_html__( 'Quantity and value', 'tools-for-fluentcart' ) . '</h2><div class="tffc-grid">';
		$labels = [
			'min_total' => __( 'Minimum total units', 'tools-for-fluentcart' ),
			'min_item' => __( 'Minimum units per product', 'tools-for-fluentcart' ),
			'min_value' => __( 'Minimum merchandise value', 'tools-for-fluentcart' ),
			'max_total' => __( 'Maximum total units', 'tools-for-fluentcart' ),
			'max_item' => __( 'Maximum units per product', 'tools-for-fluentcart' ),
			'step' => __( 'Quantity step per product', 'tools-for-fluentcart' ),
		];
		foreach ( $labels as $key => $label ) {
			$value = 'min_value' === $key ? number_format( $s[ $key ] / 100, 2, '.', '' ) : (string) $s[ $key ];
			echo '<div><label for="tffc-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label><input id="tffc-' . esc_attr( $key ) . '" name="tffc_cart_rules[' . esc_attr( $key ) . ']" type="number" min="' . ( 'step' === $key ? '1' : '0' ) . '" step="' . ( 'min_value' === $key ? '0.01' : '1' ) . '" value="' . esc_attr( $value ) . '" ' . ( 'min_value' !== $key ? 'max="100000"' : 'max="99999999.99"' ) . '></div>';
		}
		echo '</div><p>' . esc_html__( 'Zero disables a limit. A quantity step of 1 allows any whole quantity; a step of 6 allows 6, 12, 18 and so on for each product variation.', 'tools-for-fluentcart' ) . '</p><p>' . esc_html__( 'Order value uses the shop currency and merchandise amounts after discounts, excluding shipping and fees. Tax treatment follows FluentCart product pricing. Customers may build their cart freely; checkout explains and blocks unmet rules.', 'tools-for-fluentcart' ) . '</p></section>';
		echo '<section class="tffc-card"><h2>' . esc_html__( 'Data and compatibility', 'tools-for-fluentcart' ) . '</h2><p>' . esc_html__( 'Product variations count separately. A bundle counts as one cart row. Locked payment carts are preserved and checked at checkout. Test subscriptions, bundles and order bumps with your chosen rules before live use.', 'tools-for-fluentcart' ) . '</p>';
		self::checkbox( 'delete_data', __( 'Delete this website’s Cart Rules settings when uninstalling', 'tools-for-fluentcart' ), $s['delete_data'] );
		echo '<p>' . esc_html__( 'Off by default. Uninstalling in a network checks this choice separately for each website. FluentCart orders, products and carts are never deleted.', 'tools-for-fluentcart' ) . '</p></section>';
		submit_button(); echo '</form>'; self::footer(); echo '</div>';
	}

	/**
	 * Render a labelled native checkbox.
	 *
	 * @param string $key Settings key.
	 * @param string $label Localized accessible label.
	 * @param bool   $value Current selection.
	 * @return void
	 */
	private static function checkbox( string $key, string $label, bool $value ): void {
		echo '<p><label for="tffc-' . esc_attr( $key ) . '"><input id="tffc-' . esc_attr( $key ) . '" type="checkbox" name="tffc_cart_rules[' . esc_attr( $key ) . ']" value="1" ' . checked( $value, true, false ) . '> ' . esc_html( $label ) . '</label></p>';
	}

	/**
	 * Render standard footer and escaped categorized HTML release history.
	 *
	 * @return void
	 */
	private static function footer(): void {
		$de = str_starts_with( determine_locale(), 'de' );
		$changelog = $de ? 'docs/changelog-de.txt' : 'docs/changelog.txt';
		$docs = 'https://github.com/deckerweb/tools-for-fluentcart/wiki/' . ( $de ? 'Dokumentation' : 'Documentation' );
		echo '<footer class="tffc-footer"><div><img class="tffc-footer-icon" src="' . esc_url( plugins_url( 'assets/brand/icon.svg', TFFC_FILE ) ) . '" width="52" height="52" alt=""><strong>Tools for FluentCart</strong> <span>' . esc_html( TFFC_VERSION ) . '</span> · <a id="tffc-history-link" href="' . esc_url( plugins_url( $changelog, TFFC_FILE ) ) . '">' . esc_html__( 'Changelog', 'tools-for-fluentcart' ) . '</a> · <a href="' . esc_url( $docs ) . '">' . esc_html__( 'Documentation', 'tools-for-fluentcart' ) . '</a><p>' . esc_html__( 'Fine-tune your store.', 'tools-for-fluentcart' ) . '</p></div><div>© 2026 <a href="https://github.com/deckerweb">David Decker – DECKERWEB</a> · <a href="https://github.com/deckerweb/tools-for-fluentcart">' . esc_html__( 'Plugin website', 'tools-for-fluentcart' ) . '</a></div></footer>';
		echo '<dialog id="tffc-history" aria-labelledby="tffc-history-title"><header><h2 id="tffc-history-title">' . esc_html__( 'Changelog', 'tools-for-fluentcart' ) . '</h2><button type="button" id="tffc-history-close">' . esc_html__( 'Close', 'tools-for-fluentcart' ) . '</button></header>';
		$history = require TFFC_DIR . 'includes/history.php';
		$categories = [ 'New' => __( 'New:', 'tools-for-fluentcart' ), 'Improved' => __( 'Improved:', 'tools-for-fluentcart' ), 'Fixed' => __( 'Fixed:', 'tools-for-fluentcart' ), 'Misc' => __( 'Misc:', 'tools-for-fluentcart' ) ];
		foreach ( array_slice( $history, 0, 7 ) as $release ) {
			echo '<section class="tffc-release"><header><h3>' . esc_html( $release['version'] ) . '</h3>';
			if ( ! empty( $release['date'] ) ) {
				echo '<time datetime="' . esc_attr( $release['date'] ) . '">' . esc_html( wp_date( get_option( 'date_format' ), strtotime( $release['date'] . ' 12:00:00 UTC' ) ) ) . '</time>';
			}
			echo '</header>';
			foreach ( $categories as $category => $label ) {
				$entries = $release['changes'][ $category ] ?? [];
				if ( ! $entries ) { continue; }
				echo '<ul>';
				foreach ( $entries as $entry ) { echo '<li><span class="tffc-badge tffc-' . esc_attr( strtolower( $category ) ) . '">' . esc_html( $label ) . '</span> ' . esc_html( $entry ) . '</li>'; }
				echo '</ul>';
			}
			echo '</section>';
		}
		echo '</dialog>';
	}
}
