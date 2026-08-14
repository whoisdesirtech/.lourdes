<?php
/**
 * Freshness Refresh
 *
 * A WP-Cron job that keeps cached product data fresh (within the configured
 * cache lifetime) so prices stay accurate without forcing a visitor to wait
 * for a live API call. Acts as a complement to the per-request transient cache.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Freshness_Refresh {

	/**
	 * Cron event hook name.
	 */
	const CRON_HOOK = 'aa_freshness_refresh';

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( self::CRON_HOOK, array( __CLASS__, 'run' ) );
	}

	/**
	 * Schedule the recurring cron event (twice daily).
	 */
	public static function schedule() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'twicedaily', self::CRON_HOOK );
		}
	}

	/**
	 * Remove the scheduled cron event.
	 */
	public static function unschedule() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Refresh stale cached products. Scans cached product transients and
	 * re-fetches any older than the configured cache lifetime.
	 *
	 * @param int $batch Maximum number of items to refresh per run.
	 * @return int Number of items refreshed.
	 */
	public static function run( $batch = 20 ) {
		global $wpdb;

		$max_age_hours = intval( get_option( 'aa_cache_expiry', 24 ) );
		if ( $max_age_hours <= 0 ) {
			$max_age_hours = 24;
		}

		$like   = $wpdb->esc_like( '_transient_aa_item_' );
		$rows   = $wpdb->get_results(
			$wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT %d", $like . '%', (int) $batch )
		);

		$refreshed = 0;

		foreach ( $rows as $row ) {
			$transient = substr( (string) $row->option_name, strlen( '_transient_' ) );
			$value     = get_transient( $transient );

			if ( ! is_array( $value ) ) {
				continue;
			}

			$updated = isset( $value['updated_at'] ) ? strtotime( (string) $value['updated_at'] ) : 0;
			if ( ! $updated || ( time() - $updated ) < ( $max_age_hours * HOUR_IN_SECONDS ) ) {
				continue;
			}

			$ref = AA_Product_Reference::parse( isset( $value['asin'] ) ? $value['asin'] : '' );
			if ( $ref ) {
				AA_Product_Provider_Registry::get_product( $ref );
				++$refreshed;
			}
		}

		return $refreshed;
	}
}
