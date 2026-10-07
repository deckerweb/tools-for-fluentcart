<?php
/** Site-scoped configuration and localization. @package ToolsForFluentCart */
namespace Deckerweb\ToolsForFluentCart;
defined( 'ABSPATH' ) || exit;

/** Owns validated options; preserves the last valid configuration on errors. */
final class Settings {
	public const OPTION = 'tffc_cart_rules';
	/** @var array|null Last validated output; WordPress may sanitize twice on first insert. */
	private static ?array $last_output = null;

	/**
	 * Read settings for the current site without caching across switch_to_blog().
	 *
	 * @return array<string,mixed> Complete configuration.
	 */
	public static function get(): array {
		$stored = get_option( self::OPTION, [] );
		return array_merge( Rules::defaults(), is_array( $stored ) ? $stored : [] );
	}

	/**
	 * Load shipped translations after the WordPress locale has been initialized.
	 *
	 * @return void
	 */
	public static function translations(): void {
		load_plugin_textdomain( 'tools-for-fluentcart', false, dirname( plugin_basename( TFFC_FILE ) ) . '/languages' );
	}

	/**
	 * Register an option handled by WordPress permissions and nonce checks.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_setting( 'tffc', self::OPTION, [ 'type' => 'array', 'sanitize_callback' => [ self::class, 'sanitize' ], 'default' => Rules::defaults(), 'show_in_rest' => false ] );
	}

	/**
	 * Validate all values, amounts and rule combinations before storing options.
	 *
	 * @param mixed $input Submitted settings; non-array input is rejected.
	 * @return array<string,mixed> New valid settings or unchanged previous settings.
	 */
	public static function sanitize( $input ): array {
		$old = self::get();
		if ( ! current_user_can( 'manage_options' ) || ! is_array( $input ) ) { return $old; }
		if ( self::$last_output !== null && $input === self::$last_output ) { return $input; }
		$new = Rules::defaults(); $errors = [];
		foreach ( [ 'enabled', 'single', 'delete_data' ] as $key ) { $new[ $key ] = isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) && '1' === (string) $input[ $key ]; }
		foreach ( [ 'min_total', 'min_item', 'max_total', 'max_item', 'step' ] as $key ) {
			$value = $input[ $key ] ?? $new[ $key ];
			if ( ! is_scalar( $value ) || ! preg_match( '/^\d{1,6}$/D', (string) $value ) || (int) $value > 100000 || ( 'step' === $key && (int) $value < 1 ) ) {
				$errors[] = __( 'Enter whole quantities between 0 and 100000. The quantity step must be at least 1.', 'tools-for-fluentcart' );
			} else { $new[ $key ] = (int) $value; }
		}
		$value = $input['min_value'] ?? '0';
		if ( ! is_scalar( $value ) || ! preg_match( '/^\d{1,8}(?:[.,]\d{1,2})?$/D', (string) $value ) ) {
			$errors[] = __( 'Enter a nonnegative order value with no more than two decimal places.', 'tools-for-fluentcart' );
		} else {
			$parts = explode( '.', str_replace( ',', '.', (string) $value ) );
			$new['min_value'] = (int) $parts[0] * 100 + (int) str_pad( $parts[1] ?? '', 2, '0' );
		}
		if ( $new['single'] ) {
			foreach ( [ 'min_total', 'min_item', 'max_total', 'max_item', 'min_value' ] as $key ) { $new[ $key ] = 0; }
			$new['step'] = 1;
		}
		$errors = array_merge( $errors, Rules::conflicts( $new ) );
		if ( $errors ) {
			add_settings_error( self::OPTION, 'invalid_rules', implode( ' ', array_unique( $errors ) ), 'error' );
			return $old;
		}
		self::$last_output = $new;
		return $new;
	}
}
