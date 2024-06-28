<?php
/**
 * RUM integration.
 *
 * @package WP_Datadog_RUM
 */

namespace WP_Datadog_RUM;

/**
 * RUM integration.
 */
class RUM_Integration {

	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'add_datadog_rum' ) );
	}

	/**
	 * Build and inject RUM browser SDK.
	 *
	 * @return void
	 */
	public function add_datadog_rum() {
		$options = get_option( 'datadog_rum_options' );
		if ( empty( $options ) ) {
			return;
		}

		// Check for client token and app id at a minimum.
		if ( empty( $options['client_token'] ) && $options['app_id'] ) {
			return;
		}

		// Enqueue the Datadog RUM script.
		// phpcs:ignore
		wp_enqueue_script( 'datadog-rum', 'https://www.datadoghq-browser-agent.com/datadog-rum-v5.js', array(), null, false );

		// Prepare inline script data.
		$rum_init_data = array(
			'clientToken'             => esc_js( $options['client_token'] ),
			'applicationId'           => esc_js( $options['app_id'] ),
			'site'                    => esc_js( $options['site'] ),
			'service'                 => esc_js( $options['site'] ),
			'env'                     => esc_js( $options['env'] ),
			'version'                 => esc_js( $options['version'] ),
			'sessionSampleRate'       => (int) esc_js( $options['session_sample_rate'] ),
			'sessionReplaySampleRate' => (int) esc_js( $options['session_replay_sample_rate'] ),
			'trackUserInteractions'   => ! empty( $options['track_user_interactions'] ) ? true : false,
			'trackResources'          => ! empty( $options['track_resources'] ) ? true : false,
			'trackLongTasks'          => ! empty( $options['track_long_tasks'] ) ? true : false,
			'defaultPrivacyLevel'     => esc_js( $options['default_privacy_level'] ),
		);

		$rum_data = array_filter(
			$rum_init_data,
			function ( $value ) {
				return '' !== $value;
			}
		);

		$current_user     = is_user_logged_in() ? wp_get_current_user() : null;
		$rum_context_data = array(
			'logged_in' => $current_user ? 'true' : 'false',
		);

		if ( $current_user ) {
			$rum_context_data = array_merge(
				$rum_context_data,
				array(
					'id'    => esc_js( $current_user->ID ),
					'login' => esc_js( $current_user->user_login ),
					'email' => esc_js( $current_user->user_email ),
					'name'  => esc_js( $current_user->display_name ),
				)
			);
		}

		// Enqueue inline script.
		wp_add_inline_script(
			'datadog-rum',
			'window.DD_RUM && window.DD_RUM.init('
			. wp_json_encode( $rum_data, JSON_PRETTY_PRINT )
			. ');
            window.DD_RUM && window.DD_RUM.setGlobalContextProperty("usr", '
			. wp_json_encode( $rum_context_data, JSON_PRETTY_PRINT )
			. ');'
		);
	}
}
