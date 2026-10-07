<?php
/** FluentCart adapter for cart constraints. @package ToolsForFluentCart */
namespace Deckerweb\ToolsForFluentCart;
defined( 'ABSPATH' ) || exit;

/** Enforces single-item changes and validates all rules before checkout. */
final class Integration {
	/** @var string Last successfully resolved variation requested in this request. */
	private static string $preferred = '';
	/** @var array<int,bool> Model identities requiring coupon recalculation after save. */
	private static array $recalculate = [];
	/** @var bool Coupon recalculation recursion guard. */
	private static bool $busy = false;
	/** @var bool Whether the verified adapter has been registered. */
	private static bool $active = false;

	/**
	 * Register only verified FluentCart adapters; keep unsupported versions inert.
	 *
	 * @return void
	 */
	public static function boot(): void {
		// Integration is verified against 1.7.x. Unknown minor versions require a new adapter audit.
		if ( ! defined( 'FLUENTCART_VERSION' ) || version_compare( FLUENTCART_VERSION, '1.7.0', '<' ) || version_compare( FLUENTCART_VERSION, '1.8.0', '>=' ) || ! class_exists( '\FluentCart\App\Models\Cart' ) ) {
			add_action( 'admin_notices', [ self::class, 'notice' ] );
			add_action( 'network_admin_notices', [ self::class, 'notice' ] ); return;
		}
		if ( class_exists( '\FCSIC_Single_Item_Cart', false ) || class_exists( '\Einbuchgratis_FluentCart', false ) ) {
			add_action( 'admin_notices', [ self::class, 'legacy_notice' ] ); return;
		}
		self::$active = true;
		\FluentCart\App\Models\Cart::saving( [ self::class, 'saving' ] );
		\FluentCart\App\Models\Cart::saved( [ self::class, 'saved' ] );
		\FluentCart\App\Models\Cart::retrieved( [ self::class, 'retrieved' ] );
		add_filter( 'fluent_cart/cart/item_modify', [ self::class, 'remember' ], 100, 2 );
		add_filter( 'fluent_cart/item_max_quantity', [ self::class, 'quantity' ], 100 );
		add_filter( 'fluent_cart/checkout/validate_before_process', [ self::class, 'checkout' ], 100, 2 );
		add_action( 'fluent_cart/before_checkout_form', [ self::class, 'hints' ] );
		add_action( 'fluent_cart/cart/line_item/line_meta', [ self::class, 'line_hint' ] );
		add_action( 'wp_enqueue_scripts', [ self::class, 'assets' ] );
		add_action( 'wp_ajax_fluent_cart_cart_update', [ self::class, 'prepare_ajax' ], -100 );
		add_action( 'wp_ajax_nopriv_fluent_cart_cart_update', [ self::class, 'prepare_ajax' ], -100 );
	}

	/**
	 * Return whether the adapter is available in this request.
	 *
	 * @return bool Active integration, independent of enabled settings.
	 */
	public static function available(): bool { return self::$active; }

	/**
	 * Explain missing or unverified dependencies to authorized administrators.
	 *
	 * @return void
	 */
	public static function notice(): void {
		if ( current_user_can( 'manage_options' ) ) { echo '<div class="notice notice-warning"><p>' . esc_html__( 'Cart Rules is paused. Install and activate FluentCart 1.7.x to use this module. Tools for FluentCart remains available in the admin.', 'tools-for-fluentcart' ) . '</p></div>'; }
	}

	/**
	 * Avoid competing cart handlers from either predecessor plugin.
	 *
	 * @return void
	 */
	public static function legacy_notice(): void {
		if ( current_user_can( 'manage_options' ) ) { echo '<div class="notice notice-warning"><p>' . esc_html__( 'Deactivate the earlier FluentCart Cart Rules and single-item add-ons before using Tools for FluentCart.', 'tools-for-fluentcart' ) . '</p></div>'; }
	}

	/**
	 * Determine whether the current site uses the automatic single-item preset.
	 *
	 * @return bool Single mode enabled.
	 */
	public static function single(): bool { $s = Settings::get(); return self::$active && $s['enabled'] && $s['single']; }

	/**
	 * Normalize positive AJAX additions without preventing zero/negative removal.
	 * FluentCart retains its own authorization and request validation.
	 *
	 * @return void Mutates only quantity and input mode for this FluentCart action.
	 */
	public static function prepare_ajax(): void {
		if ( ! self::single() ) { return; }
		if ( isset( $_POST['quantity'] ) && is_scalar( $_POST['quantity'] ) && (float) $_POST['quantity'] > 0 ) {
			$_POST['quantity'] = '1'; $_REQUEST['quantity'] = '1';
			$_POST['by_input'] = '1'; $_REQUEST['by_input'] = '1';
			// FluentCart may already have captured its request before this AJAX action.
			\FluentCart\App\App::request()->merge( [ 'quantity' => '1', 'by_input' => '1' ] );
		}
	}

	/**
	 * Capture the successfully resolved product requested by the customer.
	 *
	 * @param mixed $variation Resolved FluentCart variation or null.
	 * @param array $data Variation ID and requested quantity.
	 * @return mixed Unmodified variation; null failures remain failures.
	 */
	public static function remember( $variation, array $data ) {
		if ( $variation && self::single() ) { self::$preferred = (string) ( $data['item_id'] ?? '' ); }
		return $variation;
	}

	/**
	 * Cap instant-checkout quantity for the single-item preset.
	 *
	 * @param int $quantity Requested quantity.
	 * @return int One in single mode, otherwise unchanged.
	 */
	public static function quantity( $quantity ): int { return self::single() ? 1 : (int) $quantity; }

	/**
	 * Preserve carts tied to invoices, renewals or completed orders.
	 *
	 * @param object $cart FluentCart model.
	 * @return bool Whether cart content may be adjusted.
	 */
	private static function editable( $cart ): bool {
		return 'completed' !== $cart->stage && ! $cart->isLocked() && 'yes' !== ( $cart->checkout_data['is_locked'] ?? 'no' );
	}

	/**
	 * Normalize single-item content before persistence; never change product records.
	 *
	 * @param object $cart FluentCart model being saved.
	 * @return void
	 */
	public static function saving( $cart ): void {
		if ( ! self::single() || ! self::editable( $cart ) ) { return; }
		$items = $cart->cart_data;
		if ( ! $items ) { return; }
		$item = end( $items );
		foreach ( $items as $candidate ) {
			if ( '' !== self::$preferred && self::$preferred === (string) ( $candidate['object_id'] ?? $candidate['id'] ?? '' ) ) { $item = $candidate; break; }
		}
		if ( 1 !== (int) ( $item['quantity'] ?? 0 ) ) {
			$item['quantity'] = 1;
			$item['subtotal'] = (int) ( $item['unit_price'] ?? $item['price'] ?? 0 );
			$item['line_total'] = $item['subtotal'];
			$item['discount_total'] = 0; $item['coupon_discount'] = 0; $item['tax_total'] = 0;
			$item['line_total_formatted'] = \FluentCart\Api\CurrencySettings::getFormattedPrice( $item['line_total'] );
		}
		if ( [ $item ] !== $items ) {
			$cart->cart_data = [ $item ]; self::$recalculate[ spl_object_id( $cart ) ] = true;
		}
	}

	/**
	 * Recalculate coupons after replacement with a recursion guard.
	 *
	 * @param object $cart Persisted FluentCart model.
	 * @return void
	 */
	public static function saved( $cart ): void {
		$id = spl_object_id( $cart );
		if ( self::$busy || empty( self::$recalculate[ $id ] ) ) { return; }
		unset( self::$recalculate[ $id ] ); self::$busy = true;
		try { $cart->reValidateCoupons(); } finally { self::$busy = false; }
	}

	/**
	 * Repair old editable carts before FluentCart snapshots their checkout items.
	 *
	 * @param object $cart Loaded FluentCart model.
	 * @return void Persists changes only when single mode requires them.
	 */
	public static function retrieved( $cart ): void {
		if ( ! self::single() || ! self::editable( $cart ) ) { return; }
		$items = $cart->cart_data;
		if ( count( $items ) > 1 || ( $items && 1 !== (int) ( $items[0]['quantity'] ?? 0 ) ) ) {
			self::saving( $cart ); $cart->save();
		}
	}

	/**
	 * Validate the actual current cart without trusting client-supplied totals.
	 *
	 * @param mixed $validation Existing checkout validation result.
	 * @param array $data FluentCart checkout submission; not used as cart authority.
	 * @return mixed Existing result or WP_Error containing customer instructions.
	 */
	public static function checkout( $validation, array $data ) {
		if ( is_wp_error( $validation ) ) { return $validation; }
		$s = Settings::get(); if ( ! $s['enabled'] ) { return $validation; }
		$cart = \FluentCart\App\Helpers\CartHelper::getCart();
		if ( ! $cart ) { return $validation; }
		// Re-query for authoritative data after coupon recalculation; never use submitted cart_hash.
		$fresh = \FluentCart\App\Models\Cart::query()->where( 'cart_hash', $cart->cart_hash )->first();
		if ( ! $fresh ) { return new \WP_Error( 'tffc_missing_cart', __( 'Please refresh your cart and try again.', 'tools-for-fluentcart' ) ); }
		$errors = Rules::violations( $fresh->cart_data, $s );
		return $errors ? new \WP_Error( 'tffc_rules', implode( ' ', array_map( [ Rules::class, 'message' ], $errors ) ) ) : $validation;
	}

	/**
	 * Render localized rule guidance within the native checkout form flow.
	 *
	 * @param array $data Renderer context containing the current cart.
	 * @return void
	 */
	public static function hints( array $data ): void {
		$cart = $data['cart'] ?? null;
		if ( ! $cart ) { return; }
		$errors = Rules::violations( $cart->cart_data, Settings::get() );
		if ( ! $errors ) { return; }
		echo '<aside class="tffc-guidance" aria-live="polite"><strong>' . esc_html__( 'Before ordering', 'tools-for-fluentcart' ) . '</strong><ul>';
		foreach ( $errors as $error ) { echo '<li>' . esc_html( Rules::message( $error ) ) . '</li>'; }
		echo '</ul></aside>';
	}

	/**
	 * Display relevant limits beside dynamically rendered cart items.
	 *
	 * @param array $data Native cart-line context.
	 * @return void
	 */
	public static function line_hint( array $data ): void {
		$s = Settings::get(); if ( ! $s['enabled'] || $s['single'] ) { return; }
		$parts = [];
		if ( $s['min_total'] ) { $parts[] = sprintf( __( 'Minimum cart quantity: %s.', 'tools-for-fluentcart' ), number_format_i18n( $s['min_total'] ) ); }
		if ( $s['min_item'] ) { $parts[] = sprintf( __( 'Minimum per product: %s.', 'tools-for-fluentcart' ), number_format_i18n( $s['min_item'] ) ); }
		if ( $s['max_total'] ) { $parts[] = sprintf( __( 'Maximum cart quantity: %s.', 'tools-for-fluentcart' ), number_format_i18n( $s['max_total'] ) ); }
		if ( $s['max_item'] ) { $parts[] = sprintf( __( 'Maximum per product: %s.', 'tools-for-fluentcart' ), number_format_i18n( $s['max_item'] ) ); }
		if ( $s['step'] > 1 ) { $parts[] = sprintf( __( 'Quantity step per product: %s.', 'tools-for-fluentcart' ), number_format_i18n( $s['step'] ) ); }
		if ( $s['min_value'] ) { $parts[] = sprintf( __( 'Minimum merchandise value: %s.', 'tools-for-fluentcart' ), wp_strip_all_tags( \FluentCart\Api\CurrencySettings::getFormattedPrice( $s['min_value'] ) ) ); }
		if ( $parts ) { echo '<small class="tffc-line-rules">' . esc_html( implode( ' ', $parts ) ) . '</small>'; }
	}

	/**
	 * Load local presentation only on pages with FluentCart assets.
	 *
	 * @return void
	 */
	public static function assets(): void {
		$s = Settings::get(); if ( ! $s['enabled'] ) { return; }
		add_action( 'wp_footer', [ self::class, 'late_assets' ], 1 );
	}

	/**
	 * Check native enqueued asset handles after shortcode rendering.
	 *
	 * @return void
	 */
	public static function late_assets(): void {
		global $wp_scripts, $wp_styles;
		$handles = array_merge( $wp_scripts->queue ?? [], $wp_styles->queue ?? [] );
		foreach ( $handles as $handle ) {
			if ( str_contains( $handle, 'fluent-cart' ) || str_contains( $handle, 'fluentcart' ) ) {
				wp_enqueue_style( 'tffc-shop', plugins_url( 'assets/shop.css', TFFC_FILE ), [], TFFC_VERSION );
				if ( self::single() ) { wp_add_inline_style( 'tffc-shop', '[data-fluent-cart-product-quantity-container],.fct-product-quantity-container,[data-fluent-cart-cart-list-item-quantity-wrapper],.fct-cart-item-quantity{display:none!important}' ); }
				wp_print_styles( 'tffc-shop' ); break;
			}
		}
	}
}
