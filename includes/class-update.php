<?php
/** Shared updater host adapter and package identity guard. @package ToolsForFluentCart */
namespace Deckerweb\ToolsForFluentCart;
defined( 'ABSPATH' ) || exit;

/** Integrates the stable deckerweb engine without a competing update mechanism. */
final class Update {
	public const REPOSITORY = 'https://github.com/deckerweb/tools-for-fluentcart';

	/**
	 * Register one public updater after host translation setup.
	 *
	 * @return void
	 */
	public static function register(): void {
		if ( ! is_admin() && ! wp_doing_cron() && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) { return; }
		$class = '\\Deckerweb\\GitHubReleaseUpdater\\V2\\Updater';
		if ( ! class_exists( $class ) ) { require_once TFFC_DIR . 'includes/deckerweb-github-release-updater-v2.php'; }
		if ( ! defined( $class . '::SUPPORTS_HOST_TRANSLATIONS' ) ) { return; }
		$translator = require TFFC_DIR . 'includes/updater-translations.php';
		$updater = new $class( TFFC_FILE, self::REPOSITORY, 'Tools for FluentCart', __( 'Order rules for your FluentCart shop.', 'tools-for-fluentcart' ), [], [ 'translate' => $translator ] );
		$updater->register();
		add_filter( 'upgrader_source_selection', [ self::class, 'validate' ], 30, 4 );
		add_filter( 'http_request_args', [ self::class, 'http' ], 20, 2 );
	}

	/**
	 * Bound only this host's public release API and source archive requests.
	 *
	 * @param array  $args WordPress HTTP arguments.
	 * @param string $url Requested URL.
	 * @return array Arguments with modest limits for matching requests only.
	 */
	public static function http( array $args, string $url ): array {
		$api = 'https://api.github.com/repos/deckerweb/tools-for-fluentcart/';
		$archive = 'https://github.com/deckerweb/tools-for-fluentcart/archive/';
		if ( str_starts_with( $url, $api ) || str_starts_with( $url, $archive ) ) {
			$args['timeout'] = 15;
			if ( ! empty( $args['stream'] ) ) { $args['timeout'] = 60; }
			elseif ( str_contains( $url, '/releases/' ) ) { $args['limit_response_size'] = 1048576; }
		}
		return $args;
	}

	/**
	 * Validate the extracted package before replacement, including bulk updates.
	 *
	 * @param mixed  $source Extracted directory or previous WP_Error.
	 * @param string $remote_source Download working directory.
	 * @param object $upgrader WordPress upgrader instance.
	 * @param array  $context Plugin and update context supplied by Core.
	 * @return mixed Unmodified valid source, previous error or host validation error.
	 */
	public static function validate( $source, string $remote_source, $upgrader, array $context ) {
		if ( is_wp_error( $source ) || ( $context['plugin'] ?? '' ) !== plugin_basename( TFFC_FILE ) ) { return $source; }
		if ( ( isset( $context['type'] ) && 'plugin' !== $context['type'] ) || ( isset( $context['action'] ) && 'update' !== $context['action'] ) ) { return $source; }
		if ( ! isset( $context['type'], $context['action'] ) && ! ( $upgrader instanceof \Plugin_Upgrader && true === $upgrader->bulk ) ) { return $source; }
		if ( ! is_string( $source ) || ! is_file( $source . '/tools-for-fluentcart.php' ) ) { return self::error(); }
		$header = get_file_data( $source . '/tools-for-fluentcart.php', [ 'Name' => 'Plugin Name', 'Version' => 'Version', 'Domain' => 'Text Domain', 'Update' => 'Update URI', 'Repo' => 'GitHub Plugin URI', 'WP' => 'Requires at least', 'PHP' => 'Requires PHP' ] );
		$offers = get_site_transient( 'update_plugins' );
		$expected = $offers->response[ plugin_basename( TFFC_FILE ) ]->new_version ?? '';
		global $wp_version;
		if ( 'tools-for-fluentcart' !== basename( untrailingslashit( $source ) ) || 'Tools for FluentCart' !== $header['Name'] || 'tools-for-fluentcart' !== $header['Domain'] || self::REPOSITORY !== $header['Update'] || self::REPOSITORY !== $header['Repo'] || ! preg_match( '/^\d+\.\d+\.\d+(?:-[A-Za-z0-9.-]+)?$/D', $header['Version'] ) || ! $expected || $expected !== $header['Version'] || version_compare( $header['Version'], TFFC_VERSION, '<=' ) || ! preg_match( '/^\d+\.\d+(?:\.\d+)?$/D', $header['WP'] ) || ! preg_match( '/^\d+\.\d+(?:\.\d+)?$/D', $header['PHP'] ) || version_compare( $wp_version, $header['WP'], '<' ) || version_compare( PHP_VERSION, $header['PHP'], '<' ) ) { return self::error(); }
		return $source;
	}

	/**
	 * Provide one non-sensitive package error.
	 *
	 * @return \WP_Error Error preventing installation replacement.
	 */
	private static function error(): \WP_Error {
		return new \WP_Error( 'tffc_update_identity', __( 'The update package does not match this plugin or the offered compatible version.', 'tools-for-fluentcart' ) );
	}
}
