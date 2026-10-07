<?php
/**
 * Cart rule calculation without database or storefront side effects.
 *
 * @package ToolsForFluentCart
 * @copyright 2026 David Decker – DECKERWEB
 * @license GPL-2.0-or-later
 */
namespace Deckerweb\ToolsForFluentCart;
defined( 'ABSPATH' ) || exit;

/** Validates quantities and merchandise values in FluentCart minor units. */
final class Rules {
	/**
	 * Provide conservative per-site defaults; activation does not alter carts.
	 *
	 * @return array<string,mixed> Complete stored settings schema.
	 */
	public static function defaults(): array {
		return [ 'enabled' => false, 'single' => false, 'min_total' => 0, 'min_item' => 0, 'min_value' => 0, 'max_total' => 0, 'max_item' => 0, 'step' => 1, 'delete_data' => false ];
	}

	/**
	 * Validate limits together instead of silently accepting conflicting rules.
	 *
	 * @param array<string,mixed> $settings Complete sanitized candidate.
	 * @return string[] Translatable error messages; empty means consistent.
	 */
	public static function conflicts( array $settings ): array {
		$errors = [];
		if ( $settings['single'] ) { return $errors; }
		foreach ( [ [ 'min_total', 'max_total' ], [ 'min_item', 'max_item' ], [ 'min_item', 'max_total' ] ] as $pair ) {
			if ( $settings[ $pair[1] ] > 0 && $settings[ $pair[0] ] > $settings[ $pair[1] ] ) {
				$errors[] = __( 'A minimum quantity cannot exceed its maximum quantity.', 'tools-for-fluentcart' );
			}
		}
		$smallest = max( 1, $settings['min_item'] );
		$smallest = (int) ( ceil( $smallest / $settings['step'] ) * $settings['step'] );
		foreach ( [ 'max_item', 'max_total' ] as $key ) {
			if ( $settings[ $key ] > 0 && $smallest > $settings[ $key ] ) {
				$errors[] = __( 'The quantity step leaves no purchasable quantity within the configured limits.', 'tools-for-fluentcart' );
			}
		}
		if ( $settings['max_total'] > 0 && ceil( max( 1, $settings['min_total'] ) / $settings['step'] ) * $settings['step'] > $settings['max_total'] ) {
			$errors[] = __( 'The quantity step leaves no purchasable quantity within the configured limits.', 'tools-for-fluentcart' );
		}
		return array_values( array_unique( $errors ) );
	}

	/**
	 * Evaluate every active rule; fee rows and nested bundle components are excluded.
	 *
	 * @param array<int,array<string,mixed>> $items Merchandise cart rows.
	 * @param array<string,mixed>           $settings Complete rule settings.
	 * @return array<int,array<string,mixed>> Violations with code, target and remaining amount.
	 */
	public static function violations( array $items, array $settings ): array {
		if ( ! $settings['enabled'] || ! $items ) { return []; }
		$total = 0; $value = 0; $errors = [];
		foreach ( $items as $item ) {
			$q = (int) ( $item['quantity'] ?? 0 );
			if ( $q < 1 ) { $errors[] = [ 'code' => 'invalid', 'target' => 1 ]; }
			$total += max( 0, $q );
			$value += max( 0, (int) ( $item['subtotal'] ?? ( (int) ( $item['unit_price'] ?? 0 ) * $q ) ) - (int) ( $item['discount_total'] ?? 0 ) );
			if ( ! $settings['single'] ) {
				foreach ( [ 'min_item', 'max_item' ] as $key ) {
					$target = $settings[ $key ];
					if ( $target > 0 && ( 'min_item' === $key ? $q < $target : $q > $target ) ) {
						$errors[] = [ 'code' => $key, 'target' => $target, 'title' => (string) ( $item['post_title'] ?? $item['title'] ?? '' ) ];
					}
				}
				if ( $settings['step'] > 1 && $q % $settings['step'] !== 0 ) {
					$errors[] = [ 'code' => 'step', 'target' => $settings['step'], 'title' => (string) ( $item['post_title'] ?? $item['title'] ?? '' ) ];
				}
			}
		}
		if ( $settings['single'] && ( count( $items ) !== 1 || $total !== 1 ) ) { $errors[] = [ 'code' => 'single', 'target' => 1 ]; }
		if ( ! $settings['single'] ) {
			foreach ( [ 'min_total', 'max_total' ] as $key ) {
				$target = $settings[ $key ];
				if ( $target > 0 && ( 'min_total' === $key ? $total < $target : $total > $target ) ) {
					$errors[] = [ 'code' => $key, 'target' => $target, 'remaining' => max( 0, $target - $total ) ];
				}
			}
			if ( $settings['min_value'] > $value ) { $errors[] = [ 'code' => 'min_value', 'target' => $settings['min_value'], 'remaining' => $settings['min_value'] - $value ]; }
		}
		return $errors;
	}

	/**
	 * Convert a validated violation into a localized customer message.
	 *
	 * @param array<string,mixed> $error Structured violation.
	 * @return string Plain message; callers escape for their output context.
	 */
	public static function message( array $error ): string {
		$n = number_format_i18n( $error['target'] );
		$title = wp_strip_all_tags( $error['title'] ?? '' );
		switch ( $error['code'] ) {
			case 'single': return __( 'Only one product with quantity one can be ordered.', 'tools-for-fluentcart' );
			case 'min_total':
				/* translators: 1: remaining units, 2: minimum total quantity. */
				return sprintf( __( 'Add %1$s more units to reach the minimum quantity of %2$s.', 'tools-for-fluentcart' ), number_format_i18n( $error['remaining'] ), $n );
			case 'max_total':
				/* translators: %s: maximum total quantity. */
				return sprintf( __( 'Your cart may contain no more than %s units.', 'tools-for-fluentcart' ), $n );
			case 'min_item':
				/* translators: 1: product title, 2: minimum quantity. */
				return sprintf( __( '%1$s requires at least %2$s units.', 'tools-for-fluentcart' ), $title, $n );
			case 'max_item':
				/* translators: 1: product title, 2: maximum quantity. */
				return sprintf( __( '%1$s allows no more than %2$s units.', 'tools-for-fluentcart' ), $title, $n );
			case 'step':
				/* translators: 1: product title, 2: quantity step. */
				return sprintf( __( 'Choose a quantity in multiples of %2$s for %1$s.', 'tools-for-fluentcart' ), $title, $n );
			case 'min_value':
				/* translators: %s: formatted monetary amount remaining. */
				return sprintf( __( 'Add products worth %s more to reach the minimum order value.', 'tools-for-fluentcart' ), wp_strip_all_tags( \FluentCart\Api\CurrencySettings::getFormattedPrice( $error['remaining'] ) ) );
			default: return __( 'Please check the quantities in your cart.', 'tools-for-fluentcart' );
		}
	}
}
