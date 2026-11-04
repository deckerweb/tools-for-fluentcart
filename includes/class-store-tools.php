<?php
/**
 * Optional shop modules and administration helpers for FluentCart.
 *
 * @package ToolsForFluentCart
 * @copyright 2026 David Decker – DECKERWEB
 * @license GPL-2.0-or-later
 */
namespace Deckerweb\ToolsForFluentCart;
defined( 'ABSPATH' ) || exit;

/** Keeps optional site settings, presentation and native purchase guards together. */
final class StoreTools {
	/** @var string[] Known module slugs; never accept arbitrary option names. */
	private const MODULES = [ 'cart-rules', 'product-notes', 'store-checks', 'catalog-mode' ];

	/**
	 * Register module hooks after all plugin classes are available.
	 *
	 * @return void
	 */
	public static function boot(): void {
		add_action( 'admin_init', [ self::class, 'register' ] );
		add_action( 'admin_post_tffc_tools', [ self::class, 'action' ] );
		add_filter( 'fluent_cart/cart/can_purchase', [ self::class, 'purchase' ], 999, 2 );
		add_filter( 'fluent_cart/checkout/validate_before_process', [ self::class, 'purchase' ], 999, 2 );
		add_filter( 'fluent_cart/product/show_section', [ self::class, 'section' ], 999, 2 );
		add_filter( 'fluent_cart/buttons/enable_floating_cart_button', [ self::class, 'floating' ] );
		add_action( 'fluent_cart/product/after_price', [ self::class, 'price_note' ] );
		add_action( 'fluent_cart/product/after_product_content', [ self::class, 'content_note' ] );
		add_action( 'fluent_cart/cart/line_item/line_meta', [ self::class, 'cart_note' ] );
		add_action( 'fluent_cart/before_checkout_form', [ self::class, 'catalog_notice' ] );
		add_action( 'wp_enqueue_scripts', [ self::class, 'assets' ] );
		add_shortcode( 'tffc_product_note', [ self::class, 'shortcode' ] );
	}

	/**
	 * Get a module option name from a fixed allowlist.
	 *
	 * @param string $module Module slug.
	 * @return string Site option name or empty string for an unknown module.
	 */
	public static function option( string $module ): string {
		return in_array( $module, self::MODULES, true ) ? 'tffc_' . str_replace( '-', '_', $module ) : '';
	}

	/**
	 * Return disabled defaults without creating persistent options.
	 *
	 * @param string $module Module slug.
	 * @return array<string,mixed> Complete default schema.
	 */
	public static function defaults( string $module ): array {
		if ( 'cart-rules' === $module ) { return Rules::defaults(); }
		$base = [ 'enabled' => false, 'delete_data' => false ];
		if ( 'product-notes' === $module ) { $base['notes'] = []; }
		if ( 'catalog-mode' === $module ) { $base += [ 'message' => '', 'contact_url' => '' ]; }
		return $base;
	}

	/**
	 * Read current-site data; do not cache across switch_to_blog().
	 *
	 * @param string $module Module slug.
	 * @return array<string,mixed> Saved configuration merged with defaults.
	 */
	public static function get( string $module ): array {
		$value = get_option( self::option( $module ), [] );
		return array_merge( self::defaults( $module ), is_array( $value ) ? $value : [] );
	}

	/**
	 * Register each module separately with native WordPress nonce/capability checks.
	 *
	 * @return void
	 */
	public static function register(): void {
		foreach ( array_slice( self::MODULES, 1 ) as $module ) {
			register_setting( 'tffc_' . $module, self::option( $module ), [
				'type' => 'array', 'default' => self::defaults( $module ), 'show_in_rest' => false,
				'sanitize_callback' => static fn( $input ) => self::sanitize( $module, $input ),
			] );
		}
	}

	/**
	 * Normalize up to 200 product IDs without trusting imported product contents.
	 *
	 * @param mixed $input Array or comma-separated product IDs.
	 * @return int[] Unique positive IDs, resolved in the current website only.
	 */
	public static function ids( $input ): array {
		if ( is_string( $input ) ) { $input = preg_split( '/[\s,;]+/', $input ); }
		if ( ! is_array( $input ) ) { return []; }
		$ids = [];
		foreach ( array_slice( $input, 0, 200 ) as $id ) {
			if ( is_scalar( $id ) && ctype_digit( (string) $id ) && (int) $id > 0 ) { $ids[] = (int) $id; }
		}
		return array_values( array_unique( $ids ) );
	}

	/**
	 * Reject malformed product scopes rather than accidentally applying them globally.
	 *
	 * @param mixed $input User-entered or imported product scope.
	 * @return bool True for an empty selection or at most 200 positive integer IDs.
	 */
	public static function valid_ids( $input ): bool {
		if ( is_string( $input ) ) { $input = trim( $input ); $input = '' === $input ? [] : preg_split( '/[\s,;]+/', $input ); }
		if ( ! is_array( $input ) || count( $input ) > 200 ) { return false; }
		foreach ( $input as $id ) { if ( ! is_scalar( $id ) || ! ctype_digit( (string) $id ) || (int) $id < 1 ) { return false; } }
		return true;
	}

	/**
	 * Validate a module and preserve dependency-bound fields while paused.
	 *
	 * @param string $module Known module slug.
	 * @param mixed  $input Submitted option array.
	 * @return array<string,mixed> Validated configuration or previous values.
	 */
	public static function sanitize( string $module, $input ): array {
		$old = self::get( $module );
		if ( ! current_user_can( 'manage_options' ) || ! is_array( $input ) ) { return $old; }
		if ( 'product-notes' === $module && isset( $input['notes'] ) && ! is_array( $input['notes'] ) ) { return $old; }
		$delete = ! empty( $input['delete_data'] ) && is_scalar( $input['delete_data'] );
		if ( ! Integration::available() ) { $old['delete_data'] = $delete; return $old; }
		$new = self::defaults( $module );
		$new['enabled'] = ! empty( $input['enabled'] ) && is_scalar( $input['enabled'] );
		$new['delete_data'] = $delete;
		if ( 'catalog-mode' === $module ) {
			$new['message'] = sanitize_textarea_field( is_string( $input['message'] ?? null ) ? substr( $input['message'], 0, 4000 ) : '' );
			$new['contact_url'] = esc_url_raw( is_string( $input['contact_url'] ?? null ) ? $input['contact_url'] : '', [ 'https', 'http' ] );
		}
		if ( 'product-notes' === $module ) {
			foreach ( array_slice( is_array( $input['notes'] ?? null ) ? $input['notes'] : [], 0, 20 ) as $note ) {
				if ( ! is_array( $note ) || ! self::valid_ids( $note['product_ids'] ?? [] ) ) { add_settings_error( self::option( $module ), 'invalid_products', __( 'Enter valid product IDs. Saved settings have been preserved.', 'tools-for-fluentcart' ) ); return $old; }
				$text = sanitize_textarea_field( is_string( $note['text'] ?? null ) ? substr( $note['text'], 0, 2000 ) : '' );
				if ( '' === $text ) { continue; }
				$position = in_array( $note['position'] ?? '', [ 'price', 'content', 'cart' ], true ) ? $note['position'] : 'price';
				$new['notes'][] = [ 'text' => $text, 'product_ids' => self::ids( $note['product_ids'] ?? [] ), 'position' => $position ];
			}
		}
		return $new;
	}

	/**
	 * Resolve a safe administration tab from the query string.
	 *
	 * @return string Known tab or default Cart Rules.
	 */
	public static function tab(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation, no mutation.
		$tab = isset( $_GET['tab'] ) && is_string( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		return in_array( $tab, array_merge( self::MODULES, [ 'transfer' ] ), true ) ? $tab : 'cart-rules';
	}

	/**
	 * Translate fixed module names.
	 *
	 * @return array<string,string> Module labels in the active interface language.
	 */
	public static function labels(): array {
		return [ 'cart-rules' => __( 'Cart Rules', 'tools-for-fluentcart' ), 'product-notes' => __( 'Product Notes', 'tools-for-fluentcart' ), 'store-checks' => __( 'Store Checks', 'tools-for-fluentcart' ), 'catalog-mode' => __( 'Catalog Mode', 'tools-for-fluentcart' ), 'transfer' => __( 'Transfer settings', 'tools-for-fluentcart' ) ];
	}

	/**
	 * Print a navigation row linking real pages, usable without JavaScript.
	 *
	 * @return void
	 */
	public static function navigation(): void {
		echo '<nav class="tffc-nav" aria-label="' . esc_attr__( 'Tools modules', 'tools-for-fluentcart' ) . '">';
		foreach ( self::labels() as $key => $label ) {
			echo '<a class="button"' . ( self::tab() === $key ? ' aria-current="page"' : '' ) . ' href="' . esc_url( add_query_arg( 'tab', $key, Admin::settings_url() ) ) . '">' . esc_html( $label ) . '</a> ';
		}
		echo '</nav>';
	}

	/**
	 * Explain whether a module is running, disabled or paused.
	 *
	 * @param string $module Module slug.
	 * @param bool   $enabled Stored enabled setting.
	 * @return void
	 */
	public static function status( string $module, bool $enabled ): void {
		$label = ! Integration::available() ? __( 'Paused: FluentCart is missing or the required interfaces are unavailable.', 'tools-for-fluentcart' ) : ( $enabled ? __( 'Active on this website.', 'tools-for-fluentcart' ) : __( 'Switched off. Saved settings are retained.', 'tools-for-fluentcart' ) );
		echo '<p class="tffc-status"><strong>' . esc_html( self::labels()[ $module ] ) . '</strong> — ' . esc_html( $label ) . ' · <a href="' . esc_url( ! Integration::available() ? admin_url( 'plugins.php' ) : ( 'cart-rules' === $module ? '#tffc-rule-fields' : '#tffc-module-controls' ) ) . '">' . esc_html( ! Integration::available() ? __( 'Review installed plugins', 'tools-for-fluentcart' ) : __( 'Edit module settings', 'tools-for-fluentcart' ) ) . '</a></p>';
	}

	/**
	 * Summarize saved rules; does not misrepresent unsaved form changes.
	 *
	 * @param array<string,mixed> $settings Complete rule settings.
	 * @return void
	 */
	public static function summary( array $settings ): void {
		echo '<section class="tffc-card"><h2>' . esc_html__( 'Saved rule summary', 'tools-for-fluentcart' ) . '</h2><ul>';
		if ( $settings['single'] ) { echo '<li>' . esc_html__( 'One product, quantity one. Adding another replaces it.', 'tools-for-fluentcart' ) . '</li>'; }
		else {
			$labels = [ 'min_total' => __( 'Minimum total units', 'tools-for-fluentcart' ), 'max_total' => __( 'Maximum total units', 'tools-for-fluentcart' ), 'min_item' => __( 'Minimum units per product', 'tools-for-fluentcart' ), 'max_item' => __( 'Maximum units per product', 'tools-for-fluentcart' ), 'step' => __( 'Quantity step per product', 'tools-for-fluentcart' ) ];
			foreach ( $labels as $key => $label ) { if ( $settings[ $key ] > ( 'step' === $key ? 1 : 0 ) ) { echo '<li>' . esc_html( $label . ': ' . number_format_i18n( $settings[ $key ] ) ) . '</li>'; } }
			if ( $settings['min_value'] ) { echo '<li>' . esc_html__( 'Minimum merchandise value', 'tools-for-fluentcart' ) . ': ' . esc_html( number_format_i18n( $settings['min_value'] / 100, 2 ) ) . ' ' . esc_html__( '(shop currency)', 'tools-for-fluentcart' ) . '</li>'; }
			echo '<li>' . esc_html( empty( $settings['product_ids'] ) ? __( 'Applies to the whole cart.', 'tools-for-fluentcart' ) : __( 'Only selected products count toward the limits.', 'tools-for-fluentcart' ) ) . '</li>';
		}
		echo '</ul></section>';
	}

	/**
	 * Render product IDs with currently resolved titles for orientation.
	 *
	 * @param string $name Form field name.
	 * @param int[]  $ids Saved product IDs.
	 * @return void
	 */
	public static function product_field( string $name, array $ids ): void {
		$id = 'tffc-' . sanitize_key( $name );
		echo '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Product IDs, separated by commas; empty means all products', 'tools-for-fluentcart' ) . '</label><p><input class="large-text" type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( implode( ', ', $ids ) ) . '"></p>';
		echo '<p>' . esc_html__( 'Use the parent product ID from the FluentCart product editor URL, not a variation ID.', 'tools-for-fluentcart' ) . '</p>';
		if ( $ids ) { echo '<ul>'; foreach ( $ids as $pid ) { echo '<li>' . esc_html( '#' . $pid . ' · ' . ( get_post_type( $pid ) === 'fluent-products' ? get_the_title( $pid ) : __( 'Product not found on this website', 'tools-for-fluentcart' ) ) ) . '</li>'; } echo '</ul>'; }
		echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=fluent-cart#/products' ) ) . '">' . esc_html__( 'Open FluentCart products', 'tools-for-fluentcart' ) . '</a></p>';
	}

	/**
	 * Handle nonce-protected reset, export, import preview/apply and sample tests.
	 *
	 * @return void Redirects after successful mutations; preview responses contain no secrets.
	 */
	public static function action(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You are not allowed to manage these settings.', 'tools-for-fluentcart' ), '', [ 'response' => 403 ] ); }
		check_admin_referer( 'tffc_tools' );
		$op = isset( $_POST['operation'] ) && is_string( $_POST['operation'] ) ? sanitize_key( wp_unslash( $_POST['operation'] ) ) : '';
		$module = isset( $_POST['module'] ) && is_string( $_POST['module'] ) ? sanitize_key( wp_unslash( $_POST['module'] ) ) : '';
		if ( 'export' === $op ) {
			$payload = [ 'plugin' => 'tools-for-fluentcart', 'schema' => 1, 'modules' => [] ];
			foreach ( self::MODULES as $key ) { $payload['modules'][ $key ] = self::get( $key ); }
			nocache_headers(); header( 'Content-Type: application/json; charset=utf-8' ); header( 'Content-Disposition: attachment; filename="tools-for-fluentcart-settings.json"' );
			wp_send_json( $payload, 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		}
		if ( ! Integration::available() ) { wp_die( esc_html__( 'The module is paused. Saved settings have not been changed.', 'tools-for-fluentcart' ) ); }
		if ( 'reset' === $op && in_array( $module, self::MODULES, true ) && isset( $_POST['confirm'] ) && is_scalar( $_POST['confirm'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['confirm'] ) ) ) {
			$defaults = self::defaults( $module ); $defaults['delete_data'] = self::get( $module )['delete_data'];
			self::save_normalized( $module, $defaults );
			wp_safe_redirect( self::return_url( $module ) ); exit;
		}
		if ( 'import' === $op ) {
			$json = isset( $_POST['settings_json'] ) && is_string( $_POST['settings_json'] ) ? filter_var( wp_unslash( $_POST['settings_json'] ), FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW ) : '';
			$parsed = self::parse_import( $json );
			if ( is_wp_error( $parsed ) ) { wp_die( esc_html( $parsed->get_error_message() ) ); }
			if ( 'import' === $op && isset( $_POST['confirm'] ) && is_scalar( $_POST['confirm'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['confirm'] ) ) ) {
				foreach ( $parsed as $key => $config ) { self::save_normalized( $key, $config ); }
				wp_safe_redirect( self::return_url( 'transfer' ) ); exit;
			}

		}
		wp_die( esc_html__( 'Choose an action and confirm it.', 'tools-for-fluentcart' ) );
	}

	/**
	 * Preserve the real parent menu when admin-post runs before menu registration.
	 *
	 * @param string $tab Target module tab.
	 * @return string Safe local plugin settings URL.
	 */
	private static function return_url( string $tab ): string {
		$url = wp_get_referer();
		$query = $url ? wp_parse_url( $url, PHP_URL_QUERY ) : '';
		parse_str( (string) $query, $args );
		if ( ( $args['page'] ?? '' ) !== 'tools-for-fluentcart' ) { $url = Admin::settings_url(); }
		return add_query_arg( 'tab', $tab, remove_query_arg( 'settings-updated', $url ) );
	}

	/**
	 * Validate a bounded settings document before any database mutation.
	 *
	 * @param string $json JSON exported by this plugin.
	 * @return array<string,array>|\WP_Error Disabled validated modules or an explanatory error.
	 */
	public static function parse_import( string $json ) {
		$data = strlen( $json ) <= 262144 ? json_decode( $json, true, 16 ) : null;
		if ( ! is_array( $data ) || ( $data['plugin'] ?? '' ) !== 'tools-for-fluentcart' || ( $data['schema'] ?? 0 ) !== 1 || ! is_array( $data['modules'] ?? null ) || ! $data['modules'] || array_diff( array_keys( $data['modules'] ), self::MODULES ) ) {
			return new \WP_Error( 'tffc_import', __( 'This is not a supported Tools for FluentCart settings document.', 'tools-for-fluentcart' ) );
		}
		$out = [];
		foreach ( $data['modules'] as $module => $config ) {
			if ( ! is_array( $config ) ) { return new \WP_Error( 'tffc_import', __( 'Invalid module settings. Nothing was imported.', 'tools-for-fluentcart' ) ); }
			$config['enabled'] = false; $config['delete_data'] = self::get( $module )['delete_data'];
			if ( 'cart-rules' === $module ) {
				foreach ( [ 'min_total', 'min_item', 'max_total', 'max_item', 'step', 'min_value' ] as $key ) {
					if ( ! isset( $config[ $key ] ) || ! is_int( $config[ $key ] ) || $config[ $key ] < ( 'step' === $key ? 1 : 0 ) || $config[ $key ] > ( 'min_value' === $key ? 9999999999 : 100000 ) ) { return new \WP_Error( 'tffc_import', __( 'Invalid module settings. Nothing was imported.', 'tools-for-fluentcart' ) ); }
				}
				$config = array_intersect_key( array_merge( Rules::defaults(), $config ), Rules::defaults() );
				if ( ! self::valid_ids( $config['product_ids'] ?? [] ) ) { return new \WP_Error( 'tffc_import', __( 'Invalid module settings. Nothing was imported.', 'tools-for-fluentcart' ) ); }
				$config['single'] = ! empty( $config['single'] ); $config['product_ids'] = self::ids( $config['product_ids'] ?? [] );
				if ( $config['single'] ) { foreach ( [ 'min_total', 'min_item', 'max_total', 'max_item', 'min_value' ] as $key ) { $config[ $key ] = 0; } $config['step'] = 1; $config['product_ids'] = []; }
				if ( Rules::conflicts( $config ) ) { return new \WP_Error( 'tffc_import', __( 'Invalid module settings. Nothing was imported.', 'tools-for-fluentcart' ) ); }
				$out[ $module ] = $config;
			} else {
				if ( 'product-notes' === $module ) {
					if ( ! is_array( $config['notes'] ?? null ) || count( $config['notes'] ) > 20 ) { return new \WP_Error( 'tffc_import', __( 'Invalid module settings. Nothing was imported.', 'tools-for-fluentcart' ) ); }
					foreach ( $config['notes'] as $note ) { if ( ! is_array( $note ) || ! self::valid_ids( $note['product_ids'] ?? [] ) ) { return new \WP_Error( 'tffc_import', __( 'Invalid module settings. Nothing was imported.', 'tools-for-fluentcart' ) ); } }
				}
				$out[ $module ] = self::sanitize( $module, $config );
			}
		}
		return $out;
	}

	/**
	 * Persist already validated values through normal sanitization exactly once.
	 * Monetary input is converted from stored minor units for the existing Settings API.
	 *
	 * @param string $module Allowed module slug.
	 * @param array  $config Normalized stored schema.
	 * @return void
	 */
	private static function save_normalized( string $module, array $config ): void {
		if ( 'cart-rules' === $module ) { $config['min_value'] = number_format( $config['min_value'] / 100, 2, '.', '' ); }
		update_option( self::option( $module ), $config, false );
	}

	/**
	 * Open a site-scoped POST form with a nonce.
	 *
	 * @param string $operation Fixed action identifier.
	 * @return void
	 */
	private static function form_start( string $operation ): void {
		echo '<form action="' . esc_url( in_array( $operation, [ 'preview', 'test' ], true ) ? add_query_arg( 'tab', 'preview' === $operation ? 'transfer' : 'cart-rules', Admin::settings_url() ) : admin_url( 'admin-post.php' ) ) . '" method="post"><input type="hidden" name="action" value="tffc_tools"><input type="hidden" name="operation" value="' . esc_attr( $operation ) . '">'; wp_nonce_field( 'tffc_tools' );
	}

	/**
	 * Render a reset action requiring an explicit checkbox.
	 *
	 * @param string $module Module slug.
	 * @return void
	 */
	public static function reset_form( string $module ): void {
		echo '<details class="tffc-card"><summary>' . esc_html__( 'Reset this module', 'tools-for-fluentcart' ) . '</summary>';
		self::form_start( 'reset' ); echo '<input type="hidden" name="module" value="' . esc_attr( $module ) . '"><p><label><input type="checkbox" name="confirm" value="1" required> ' . esc_html__( 'Restore defaults for this module only. Other modules and deletion preferences stay unchanged.', 'tools-for-fluentcart' ) . '</label></p>'; submit_button( __( 'Reset this module', 'tools-for-fluentcart' ), 'secondary' ); echo '</form></details>';
	}

	/**
	 * Render a single-row, shop-currency sample calculator without modifying carts.
	 *
	 * @return void
	 */
	public static function tester(): void {
		echo '<section class="tffc-card"><h2>' . esc_html__( 'Try the saved rules', 'tools-for-fluentcart' ) . '</h2><p>' . esc_html__( 'One sample product variation, in shop currency after discounts. Uses saved rules and creates no cart or order. It does not check stock, tax, shipping or payment.', 'tools-for-fluentcart' ) . '</p>';
		if ( isset( $_POST['operation'] ) && is_string( $_POST['operation'] ) && 'test' === $_POST['operation'] ) {
			check_admin_referer( 'tffc_tools' );
			if ( Integration::available() ) {
			$quantity = isset( $_POST['quantity'] ) && is_scalar( $_POST['quantity'] ) ? min( 100000, max( 1, (int) $_POST['quantity'] ) ) : 1;
			$value = isset( $_POST['value'] ) && is_scalar( $_POST['value'] ) ? max( 0, (float) $_POST['value'] ) : 0;
			$pid = isset( $_POST['product_id'] ) && is_scalar( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
			$errors = Rules::violations( [ [ 'post_id' => $pid, 'quantity' => $quantity, 'subtotal' => (int) round( min( $value, 99999999 ) * 100 ), 'post_title' => __( 'Sample product', 'tools-for-fluentcart' ) ] ], Settings::get() );
			$messages = array_map( [ Rules::class, 'message' ], $errors );
				echo '<p class="tffc-status" role="status">' . esc_html( $messages ? implode( ' ', $messages ) : __( 'The sample meets the saved rules, or no rule applies.', 'tools-for-fluentcart' ) ) . '</p>';
			}
		}
		self::form_start( 'test' );
		foreach ( [ 'product_id' => __( 'Product ID', 'tools-for-fluentcart' ), 'quantity' => __( 'Quantity', 'tools-for-fluentcart' ), 'value' => __( 'Total merchandise value', 'tools-for-fluentcart' ) ] as $key => $label ) {
			echo '<p><label>' . esc_html( $label ) . ' <input type="number" name="' . esc_attr( $key ) . '" min="0" step="' . ( 'value' === $key ? '0.01' : '1' ) . '" value="' . ( 'quantity' === $key ? '1' : '0' ) . '"></label></p>';
		}
		submit_button( __( 'Check sample', 'tools-for-fluentcart' ), 'secondary' ); echo '</form></section>';
	}

	/**
	 * Render the selected module and its isolated form.
	 *
	 * @return void
	 */
	public static function render(): void {
		$module = self::tab(); $s = self::get( $module ); $paused = ! Integration::available();
		echo '<div class="wrap tffc-admin"><header class="tffc-header"><img src="' . esc_url( plugins_url( 'assets/brand/icon.svg', TFFC_FILE ) ) . '" class="tffc-mark" width="64" height="64" alt=""><div><h1>Tools for FluentCart</h1><p>' . esc_html__( 'Fine-tune your store.', 'tools-for-fluentcart' ) . '</p></div></header><div class="tffc-notices"><hr class="wp-header-end">'; if ( ! str_contains( Admin::settings_url(), 'options-general.php' ) ) { settings_errors(); } echo '</div>';
		self::navigation();
		if ( 'transfer' === $module ) {
			echo '<section class="tffc-card"><h2>' . esc_html__( 'Transfer settings', 'tools-for-fluentcart' ) . '</h2><p>' . esc_html__( 'Only this add-on’s settings are exported. No products, orders, customers, payment credentials or Library preferences are included.', 'tools-for-fluentcart' ) . '</p>';
			self::form_start( 'export' ); submit_button( __( 'Export settings', 'tools-for-fluentcart' ), 'secondary' ); echo '</form>';
			if ( isset( $_POST['operation'] ) && is_string( $_POST['operation'] ) && 'preview' === $_POST['operation'] ) {
				check_admin_referer( 'tffc_tools' );
				$json = isset( $_POST['settings_json'] ) && is_string( $_POST['settings_json'] ) ? filter_var( wp_unslash( $_POST['settings_json'] ), FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW ) : '';
				$parsed = self::parse_import( $json );
				if ( is_wp_error( $parsed ) ) { echo '<p role="alert">' . esc_html( $parsed->get_error_message() ) . '</p>'; }
				else {
					echo '<h3>' . esc_html__( 'Import preview', 'tools-for-fluentcart' ) . '</h3><p>' . esc_html__( 'Only Tools settings are replaced. Modules will be switched off; enable them after review. Product IDs belong to the source website: check them before enabling. Deletion preferences stay unchanged.', 'tools-for-fluentcart' ) . '</p><pre class="tffc-import-preview">' . esc_html( wp_json_encode( $parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ) . '</pre>';
					self::form_start( 'import' ); echo '<textarea name="settings_json" hidden>' . esc_textarea( $json ) . '</textarea><p><label><input type="checkbox" name="confirm" value="1" required> ' . esc_html__( 'Replace the listed module settings', 'tools-for-fluentcart' ) . '</label></p>'; submit_button( __( 'Apply import', 'tools-for-fluentcart' ) ); echo '</form>';
				}
			}
			self::form_start( 'preview' ); echo '<p><label for="tffc-json">' . esc_html__( 'Paste exported JSON (maximum 256 KB)', 'tools-for-fluentcart' ) . '</label></p><textarea id="tffc-json" name="settings_json" rows="10" class="large-text" maxlength="262144" required></textarea>'; submit_button( __( 'Preview import', 'tools-for-fluentcart' ) ); echo '</form></section>';
		} else {
			self::status( $module, $s['enabled'] );
			echo '<form action="options.php" method="post">'; settings_fields( 'tffc_' . $module );
			echo '<section class="tffc-card"><h2>' . esc_html( self::labels()[ $module ] ) . '</h2><fieldset id="tffc-module-controls"' . disabled( $paused, true, false ) . '>';
			$name = self::option( $module );
			echo '<p><label><input type="checkbox" name="' . esc_attr( $name ) . '[enabled]" value="1" ' . checked( $s['enabled'], true, false ) . '> ' . esc_html__( 'Enable this module on this website', 'tools-for-fluentcart' ) . '</label></p>';
			if ( 'product-notes' === $module ) {
				echo '<p>' . esc_html__( 'Plain product information, not legal notices. Empty product selection applies to all products. Empty notes are not saved. Up to 20 notes; one additional empty row appears after saving.', 'tools-for-fluentcart' ) . '</p>';
				$notes = array_slice( array_merge( $s['notes'], [ [ 'text' => '', 'product_ids' => [], 'position' => 'price' ] ] ), 0, 20 );
				foreach ( $notes as $i => $note ) {
					echo '<fieldset class="tffc-note-editor"><legend>' . esc_html__( 'Product note', 'tools-for-fluentcart' ) . ' ' . esc_html( (string) ( $i + 1 ) ) . '</legend>'; self::product_field( $name . '[notes][' . $i . '][product_ids]', $note['product_ids'] );
					echo '<p><label>' . esc_html__( 'Note text', 'tools-for-fluentcart' ) . '<textarea rows="3" class="large-text" maxlength="2000" name="' . esc_attr( $name . '[notes][' . $i . '][text]' ) . '">' . esc_textarea( $note['text'] ) . '</textarea></label></p><p><label>' . esc_html__( 'Display position', 'tools-for-fluentcart' ) . ' <select name="' . esc_attr( $name . '[notes][' . $i . '][position]' ) . '">';
					foreach ( [ 'price' => __( 'After the product price', 'tools-for-fluentcart' ), 'content' => __( 'After product content', 'tools-for-fluentcart' ), 'cart' => __( 'Beside the cart item', 'tools-for-fluentcart' ) ] as $key => $label ) { echo '<option value="' . esc_attr( $key ) . '" ' . selected( $note['position'], $key, false ) . '>' . esc_html( $label ) . '</option>'; }
					echo '</select></label></p></fieldset>';
				}
				echo '<p>' . esc_html__( 'Custom layouts can use [tffc_product_note id="123"]. The shortcode outputs matching notes for that product, irrespective of their automatic display position.', 'tools-for-fluentcart' ) . '</p>';
			}
			if ( 'catalog-mode' === $module ) {
				echo '<p>' . esc_html__( 'Products and prices stay visible. New storefront purchases and checkout are blocked for everyone, including administrators. Existing orders, renewals and account access remain with FluentCart. Clear page caches when switching this mode.', 'tools-for-fluentcart' ) . '</p><p><label>' . esc_html__( 'Public catalog message (optional)', 'tools-for-fluentcart' ) . '<textarea class="large-text" rows="3" maxlength="4000" name="tffc_catalog_mode[message]">' . esc_textarea( $s['message'] ) . '</textarea></label></p><p><label>' . esc_html__( 'Contact URL (optional)', 'tools-for-fluentcart' ) . '<input class="large-text" type="url" name="tffc_catalog_mode[contact_url]" value="' . esc_attr( $s['contact_url'] ) . '"></label></p>';
			}
			if ( 'store-checks' === $module ) { echo '<p>' . esc_html__( 'Run a local check on this page. It reads page assignments and Tools settings without changing your shop or contacting external services. This is not a legal, security or payment certification.', 'tools-for-fluentcart' ) . '</p>'; }
			echo '</fieldset><p><label><input type="checkbox" name="' . esc_attr( $name ) . '[delete_data]" value="1" ' . checked( $s['delete_data'], true, false ) . '> ' . esc_html__( 'Delete this module’s settings on this website when uninstalling', 'tools-for-fluentcart' ) . '</label></p>'; submit_button(); echo '</section></form>';
			if ( 'store-checks' === $module && $s['enabled'] && ! $paused ) { self::checks_ui(); }
			self::reset_form( $module );
		}
		Admin::footer(); echo '</div>';
	}

	/**
	 * Read locally configured core pages and report publication/content issues.
	 *
	 * @return array<int,array<string,mixed>> Check results with status, explanation and link.
	 */
	public static function checks(): array {
		if ( ! Integration::available() || ! class_exists( '\\FluentCart\\Api\\StoreSettings' ) ) { return []; }
		$results = []; $api = new \FluentCart\Api\StoreSettings();
		$blocks = [ 'shop' => 'fluent-cart/products', 'cart' => 'fluent-cart/cart', 'checkout' => 'fluent-cart/checkout', 'customer_profile' => 'fluent-cart/customer-profile' ];
		foreach ( [ 'shop' => 'fluent_cart_products', 'cart' => 'fluent_cart_cart', 'checkout' => 'fluent_cart_checkout', 'customer_profile' => 'fluent_cart_customer_profile' ] as $key => $shortcode ) {
			$id = (int) $api->get( $key . '_page_id', 0 ); $post = get_post( $id );
			$status = $post && 'page' === $post->post_type && 'publish' === $post->post_status;
			$content = $post && ( has_shortcode( $post->post_content, $shortcode ) || has_block( $blocks[ $key ], $post ) );
			$results[] = [ 'name' => $key, 'ok' => $status && $content, 'message' => ! $status ? __( 'Page missing or not published.', 'tools-for-fluentcart' ) : ( $content ? __( 'Published page with FluentCart content found.', 'tools-for-fluentcart' ) : __( 'No native shortcode or FluentCart block found. Check custom builder content manually.', 'tools-for-fluentcart' ) ), 'url' => $post ? get_edit_post_link( $id, 'raw' ) : admin_url( 'admin.php?page=fluent-cart#/settings/pages' ) ];
		}
		$results[] = [ 'name' => __( 'Cart Rules', 'tools-for-fluentcart' ), 'ok' => ! Rules::conflicts( Settings::get() ), 'message' => __( 'Saved limits checked for contradictions.', 'tools-for-fluentcart' ), 'url' => Admin::settings_url() ];
		if ( self::catalog() ) { $results[] = [ 'name' => __( 'Catalog Mode', 'tools-for-fluentcart' ), 'ok' => false, 'message' => __( 'Purchases are intentionally paused.', 'tools-for-fluentcart' ), 'url' => add_query_arg( 'tab', 'catalog-mode', Admin::settings_url() ) ]; }
		return $results;
	}

	/**
	 * Present current checks on demand when the module page is opened.
	 *
	 * @return void
	 */
	private static function checks_ui(): void {
		echo '<section class="tffc-card"><h2>' . esc_html__( 'Current store checks', 'tools-for-fluentcart' ) . '</h2><p><a class="button" href="' . esc_url( add_query_arg( 'tab', 'store-checks', Admin::settings_url() ) ) . '">' . esc_html__( 'Run checks again', 'tools-for-fluentcart' ) . '</a></p><ul>';
		$names = [ 'shop' => __( 'Shop page', 'tools-for-fluentcart' ), 'cart' => __( 'Cart page', 'tools-for-fluentcart' ), 'checkout' => __( 'Checkout page', 'tools-for-fluentcart' ), 'customer_profile' => __( 'Customer account page', 'tools-for-fluentcart' ) ];
		foreach ( self::checks() as $check ) { echo '<li><strong>' . esc_html( $names[ $check['name'] ] ?? $check['name'] ) . '</strong> — ' . esc_html( $check['ok'] ? __( 'OK', 'tools-for-fluentcart' ) : __( 'Review', 'tools-for-fluentcart' ) ) . ': ' . esc_html( $check['message'] ) . ' <a href="' . esc_url( $check['url'] ) . '">' . esc_html__( 'Open settings or page', 'tools-for-fluentcart' ) . '</a></li>'; }
		echo '</ul><p>' . esc_html__( 'FluentCart 1.7.0, 2.2.0 and 2.3.0 can report scheduler errors when a new Multisite website is created. The cause is in FluentCart; this add-on does not modify the parent plugin.', 'tools-for-fluentcart' ) . '</p></section>';
	}

	/**
	 * Determine whether native purchase guards must apply.
	 *
	 * @return bool Enabled and supported catalog mode on this site.
	 */
	public static function catalog(): bool { return Integration::available() && self::get( 'catalog-mode' )['enabled']; }

	/**
	 * Stop new native cart purchases and checkout while retaining earlier errors.
	 *
	 * @param mixed $result Native purchase/checkout validation result.
	 * @param array $context Native cart/checkout context, not trusted as authorization.
	 * @return mixed Original result or a customer-facing WP_Error.
	 */
	public static function purchase( $result, array $context = [] ) {
		return self::catalog() && ! is_wp_error( $result ) ? new \WP_Error( 'tffc_catalog', __( 'This shop is currently a catalog. Online purchases are paused.', 'tools-for-fluentcart' ) ) : $result;
	}

	/**
	 * Hide native buying regions through FluentCart's rendering API.
	 *
	 * @param bool  $show Current visibility.
	 * @param array $context Region payload supplied by FluentCart.
	 * @return bool False for purchase regions in catalog mode.
	 */
	public static function section( bool $show, array $context ): bool { return self::catalog() && in_array( $context['section'] ?? '', [ 'quantity', 'actions', 'add_to_cart_button', 'buy_now_button', 'buy_section' ], true ) ? false : $show; }

	/**
	 * Suppress the native floating cart entry point in catalog mode.
	 *
	 * @param bool $show Current visibility.
	 * @return bool Effective floating-cart visibility.
	 */
	public static function floating( bool $show ): bool { return self::catalog() ? false : $show; }

	/**
	 * Load scoped storefront styling only when these modules need output.
	 *
	 * @return void
	 */
	public static function assets(): void {
		if ( Integration::available() && ( self::catalog() || self::get( 'product-notes' )['enabled'] ) ) {
			wp_enqueue_style( 'tffc-modules', plugins_url( 'assets/modules.css', TFFC_FILE ), [], TFFC_VERSION );
		}
		if ( self::catalog() ) { add_filter( 'body_class', [ self::class, 'body_class' ] ); }
	}

	/**
	 * Expose a scoped fallback for older native templates without rendering gates.
	 *
	 * @param string[] $classes Existing body classes.
	 * @return string[] Classes with the catalog marker.
	 */
	public static function body_class( array $classes ): array { $classes[] = 'tffc-catalog'; return $classes; }

	/**
	 * Output the public catalog explanation and optional contact link.
	 *
	 * @param array $context Optional native rendering context.
	 * @return void
	 */
	public static function catalog_notice( array $context = [] ): void {
		if ( ! self::catalog() ) { return; }
		$s = self::get( 'catalog-mode' );
		echo '<span class="tffc-product-note tffc-catalog-note">' . esc_html( $s['message'] ?: __( 'This shop is currently a catalog. Online purchases are paused.', 'tools-for-fluentcart' ) ) . ' ';
		if ( $s['contact_url'] ) { echo '<a href="' . esc_url( $s['contact_url'] ) . '">' . esc_html__( 'Contact the shop', 'tools-for-fluentcart' ) . '</a>'; }
		echo '</span>';
	}

	/**
	 * Render product-price notes using the native product model context.
	 *
	 * @param array $context Native product/variation rendering context.
	 * @return void
	 */
	public static function price_note( array $context ): void {
		$product = $context['product'] ?? null;
		$id = is_object( $product ) ? (int) ( $product->ID ?? $product->post_id ?? 0 ) : (int) ( $product['ID'] ?? $product['post_id'] ?? 0 );
		echo wp_kses_post( self::notes_html( $id, 'price' ) );
		self::catalog_notice();
	}

	/**
	 * Render notes after a native product description.
	 *
	 * @param int $product_id WordPress FluentCart product ID.
	 * @return void
	 */
	public static function content_note( int $product_id ): void { echo wp_kses_post( self::notes_html( $product_id, 'content' ) ); }

	/**
	 * Render notes alongside native cart rows.
	 *
	 * @param array $context Native line-item renderer payload.
	 * @return void
	 */
	public static function cart_note( array $context ): void {
		$item = $context['item'] ?? $context['cart_item'] ?? [];
		echo wp_kses_post( self::notes_html( (int) ( $item['post_id'] ?? $context['post_id'] ?? 0 ), 'cart' ) );
	}

	/**
	 * Build safe plain-text notes for a published product.
	 *
	 * @param int    $product_id WordPress product ID.
	 * @param string $position Automatic position, or empty for shortcode output.
	 * @return string Escaped note markup, never executes shortcodes or arbitrary HTML.
	 */
	public static function notes_html( int $product_id, string $position = '' ): string {
		$s = self::get( 'product-notes' );
		if ( ! Integration::available() || ! $s['enabled'] || get_post_type( $product_id ) !== 'fluent-products' || get_post_status( $product_id ) !== 'publish' ) { return ''; }
		$html = '';
		foreach ( $s['notes'] as $note ) {
			if ( $position && $position !== $note['position'] ) { continue; }
			if ( $note['product_ids'] && ! in_array( $product_id, $note['product_ids'], true ) ) { continue; }
			$html .= '<span class="tffc-product-note">' . nl2br( esc_html( $note['text'] ) ) . '</span>';
		}
		return $html;
	}

	/**
	 * Return notes for custom shop layouts without changing native products.
	 *
	 * @param array|string $attributes Optional numeric product id.
	 * @return string Safe notes markup.
	 */
	public static function shortcode( $attributes ): string {
		$attributes = shortcode_atts( [ 'id' => get_the_ID() ], $attributes, 'tffc_product_note' );
		return self::notes_html( absint( $attributes['id'] ) );
	}
}
