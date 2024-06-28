<?php
/**
 * Administrative page.
 *
 * @package WP_Datadog_RUM
 */

namespace WP_Datadog_RUM;

/**
 * Class for managing the admin settings page.
 */
class RUM_Admin {

	/**
	 * Default options array.
	 *
	 * @var array
	 */
	public $datadog_rum_options = array();

	/**
	 * Static site list.
	 *
	 * @var array
	 */
	public $datadog_site_options = array(
		'datadoghq.com'     => 'US1 (datadoghq.com)',
		'us3.datadoghq.com' => 'US3 (us3.datadoghq.com)',
		'us5.datadoghq.com' => 'US5 (us5.datadoghq.com)',
		'datadoghq.eu'      => 'EU1 (datadoghq.eu)',
		'ddog-gov.com'      => 'US1-FED (ddog-gov.com)',
		'ap1.datadoghq.com' => 'AP1 (ap1.datadoghq.com)',
	);

	/**
	 * Field builder list.
	 *
	 * @var array
	 */
	public $datadog_rum_field_list = array();

	/**
	 * Construct.
	 */
	public function __construct() {
		$plugin_link = 'plugin_action_links_' . WP_DATADOG_RUM_BASENAME;
		add_filter( $plugin_link, array( $this, 'add_action_links' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );

		// Build Field List.
		$this->datadog_rum_field_list = array(
			'client_token'               => array(
				'title'       => 'Client Token',
				'description' => 'A Datadog client token.',
				'type'        => 'text',
				'attributes'  => array(
					'placeholder' => 'ex: pubxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
				),
			),
			'app_id'                     => array(
				'title'       => 'Application ID',
				'description' => 'The RUM application ID.',
				'type'        => 'text',
				'attributes'  => array(
					'placeholder' => 'ex: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
				),
			),
			'site'                       => array(
				'title'       => 'Datadog Site',
				'description' => '<a href="https://docs.datadoghq.com/getting_started/site/">The Datadog site parameter of your organization.</a>',
				'type'        => 'select',
				'options'     => $this->datadog_site_options,
			),
			'service'                    => array(
				'title'       => 'Service Name',
				'description' => 'The service name for your application. Follows the <a href="https://docs.datadoghq.com/getting_started/tagging/#define-tags">tag syntax requirements</a>.',
				'type'        => 'text',
				'attributes'  => array(
					'placeholder' => 'ex: web-store',
				),
			),
			'env'                        => array(
				'title'       => 'Environment',
				'description' => 'The application’s environment, for example: prod, pre-prod, and staging. Follows the <a href="https://docs.datadoghq.com/getting_started/tagging/#define-tags">tag syntax requirements</a>.',
				'type'        => 'text',
				'attributes'  => array(
					'placeholder' => 'ex: staging-1 or prod',
				),
			),
			'version'                    => array(
				'title'       => 'Version',
				'description' => 'The application’s version, for example: 1.2.3, 6c44da20, and 2020.02.13. Follows the <a href="https://docs.datadoghq.com/getting_started/tagging/#define-tags">tag syntax requirements</a>.',
				'type'        => 'text',
				'attributes'  => array(
					'placeholder' => 'ex: 1.0.3',
				),
			),
			'session_sample_rate'        => array(
				'title'       => 'Session Sample Rate',
				'description' => 'Set the % of total user sessions you want to capture for this application.',
				'type'        => 'number',
				'attributes'  => array(
					'min' => 0,
					'max' => 100,
				),
			),
			'session_replay_sample_rate' => array(
				'title'       => 'Session Replay Sample Rate',
				'description' => 'Set the % of captured user sessions that should include Session Replay recordings.',
				'type'        => 'number',
				'attributes'  => array(
					'min' => 0,
					'max' => 100,
				),
			),
			'track_user_interactions'    => array(
				'title'       => 'Track User Interactions',
				'description' => 'Enables <a href="https://docs.datadoghq.com/real_user_monitoring/browser/tracking_user_actions">automatic collection of users actions</a>.',
				'type'        => 'checkbox',
			),
			'track_resources'            => array(
				'title'       => 'Track Resources',
				'description' => 'Enables collection of resource events.',
				'type'        => 'checkbox',
			),
			'track_long_tasks'           => array(
				'title'       => 'Track Long Tasks',
				'description' => 'Enables collection of long task events.',
				'type'        => 'checkbox',
			),
			'default_privacy_level'      => array(
				'title'       => 'Default Privacy Level',
				'description' => 'See <a href="https://docs.datadoghq.com/real_user_monitoring/session_replay/privacy_options?tab=maskuserinput">Session Replay Privacy Options</a>.',
				'type'        => 'select',
				'options'     => array(
					'mask-user-input' => 'All user input masked by default',
					'allow'           => 'All text available by default',
					'mask'            => 'All text masked by default',
				),
			),
		);
	}

	/**
	 * Create options page.
	 *
	 * @return void
	 */
	public function add_plugin_page() {
		add_options_page(
			'Datadog RUM',
			'Datadog RUM',
			'manage_options',
			'datadog-rum',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Create admin page.
	 */
	public function create_admin_page() {
		$this->datadog_rum_options = get_option( 'datadog_rum_options' );
		$image_url                 = WP_DATADOG_RUM_PLUGIN_URL . 'datadog.svg';

		$form_top = <<<FORM_TOP
        <div class="wrap">
            <img src="$image_url" width="100" alt="Datadog" />
            <h1>Datadog RUM</h1>

            <form method="post" action="options.php">
        FORM_TOP;

		echo esc_html( $form_top );

		// Render form content.
		settings_fields( 'datadog_rum_option_group' );
		do_settings_sections( 'datadog-rum-admin' );
		submit_button();

		$form_bottom = <<<FORM_BOTTOM
        </form>
        </div>
        FORM_BOTTOM;

		echo esc_html( $form_bottom );
	}

	/**
	 * Initialize page.
	 *
	 * @return void
	 */
	public function page_init() {
		register_setting(
			'datadog_rum_option_group',
			'datadog_rum_options',
		);

		add_settings_section(
			'datadog_rum_setting_section',
			'Settings',
			array( $this, 'datadog_rum_section_info' ),
			'datadog-rum-admin'
		);

		// Add all defined fields.
		foreach ( $this->datadog_rum_field_list as $key => $field ) {
			$field_key = 'datadog_rum_' . $key;
			add_settings_field(
				$field_key,
				$field['title'],
				array( $this, 'datadog_rum_field_callback' ),
				'datadog-rum-admin',
				'datadog_rum_setting_section',
				array(
					'label_for' => $field_key,
					'key'       => $key,
					'field'     => $field,
				)
			);
		}
	}

	/**
	 * Print section information.
	 *
	 * @return void
	 */
	public function datadog_rum_section_info() {
		$section = <<<TEXT
        <p>
            Create a <a target="_blank" href="https://app.datadoghq.com/rum/list/">RUM application</a> in Datadog and enter its settings below. If you do not yet have an account you can sign up for a <a href="https://www.datadoghq.com/free-datadog-trial/">free trial</a>.
        </p>
        TEXT;

		echo esc_html( $section );
	}

	/**
	 * Generic field callback hander.
	 *
	 * @param [type] $options Array of options.
	 * @return void
	 */
	public function datadog_rum_field_callback( $options ) {
		$key   = $options['key'];
		$field = $options['field'];
		$type  = ! empty( $field['type'] ) ? $options['field']['type'] : 'text';

		switch ( $type ) {
			case 'text':
				$output = $this->datadog_rum_field_text( $key, $field );
				break;
			case 'checkbox':
				$output = $this->datadog_rum_field_checkbox( $key, $field );
				break;
			case 'select':
				$output = $this->datadog_rum_field_select( $key, $field );
				break;
			case 'number':
				$output = $this->datadog_rum_field_number( $key, $field );
				break;
			default:
				$output = '';
				break;
		}

		echo esc_html( $output );
	}

	/**
	 * Input text field builder.
	 *
	 * @param [type] $key Key of field.
	 * @param [type] $field Array of field values.
	 * @return string
	 */
	public function datadog_rum_field_text( $key, $field ) {
		$desc_id    = $key . 'description';
		$value      = esc_attr( $this->datadog_rum_options[ $key ] ?? '' );
		$attributes = ( ! empty( $field['attributes'] ) ) ? $this->print_attributes( $field['attributes'] ) : '';

		$input       = "<input type='text' name='datadog_rum_options[$key]' id='$key' value='$value' aria-describedby='$desc_id' $attributes>";
		$description = $this->create_description_markup( $key, $field );

		return $input . $description;
	}

	/**
	 * Input checkbox field builder.
	 *
	 * @param [type] $key Key of field.
	 * @param [type] $field Array of field values.
	 * @return string
	 */
	public function datadog_rum_field_checkbox( $key, $field ) {
		// Checked attribute logic.
		if ( ! empty( $this->datadog_rum_options[ $key ] ) ) {
			$field['attributes']['checked'] = true;
		}

		$desc_id    = $key . 'description';
		$attributes = ( ! empty( $field['attributes'] ) ) ? $this->print_attributes( $field['attributes'] ) : '';

		$input       = "<input type='checkbox' name='datadog_rum_options[$key]' id='$key' aria-describedby='$desc_id' $attributes>";
		$description = $this->create_description_markup( $key, $field );

		return $input . $description;
	}

	/**
	 * Input select field builder.
	 *
	 * @param [type] $key Key of field.
	 * @param [type] $field Array of field values.
	 * @return string
	 */
	public function datadog_rum_field_select( $key, $field ) {
		$desc_id    = $key . 'description';
		$value      = esc_attr( $this->datadog_rum_options[ $key ] ?? '' );
		$title      = $field['title'];
		$attributes = ( ! empty( $field['attributes'] ) ) ? $this->print_attributes( $field['attributes'] ) : '';

		$input = "<select name='datadog_rum_options[$key]' id='$key' aria-label='Select $title' aria-describedby='$desc_id' $attributes>";

		foreach ( $field['options'] as $parameter => $label ) {
			$selected = ( $parameter === $value ) ? ' selected' : '';
			$input   .= "<option value=\"$parameter\"$selected>$label</option>";
		}

		$input      .= '</select>';
		$description = $this->create_description_markup( $key, $field );

		return $input . $description;
	}

	/**
	 * Input number field builder.
	 *
	 * @param [type] $key Key of field.
	 * @param [type] $field Array of field values.
	 * @return string
	 */
	public function datadog_rum_field_number( $key, $field ) {
		$desc_id    = $key . 'description';
		$value      = esc_attr( $this->datadog_rum_options[ $key ] ?? '' );
		$attributes = ( ! empty( $field['attributes'] ) ) ? $this->print_attributes( $field['attributes'] ) : '';

		$input       = "<input type='number' name='datadog_rum_options[$key]' id='$key' value='$value' aria-describedby='$desc_id' $attributes>";
		$description = $this->create_description_markup( $key, $field );

		return $input . $description;
	}

	/**
	 * Create description markup.
	 *
	 * @param [type] $key Key of field.
	 * @param [type] $field Array of field values.
	 * @return string
	 */
	public function create_description_markup( $key, $field ) {
		$desc_id     = $key . 'description';
		$description = ( ! empty( $field['description'] ) ) ? $field['description'] : null;
		if ( ! empty( $description ) ) {
			$description = <<<DESC
            <p id="$desc_id" class="description">$description</p>
            DESC;
		}
		return $description;
	}

	/**
	 * Print attributes string from array key pairs.
	 *
	 * @param [type] $arr Array of attributes.
	 * @return string
	 */
	public function print_attributes( $arr ) {
		$attributes = array();
		foreach ( $arr as $key => $value ) {
			$attributes[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}
		return implode( ' ', $attributes );
	}


	/**
	 * Add action links on the plugin page.
	 *
	 * @param array $links Plugin Action Links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=datadog-rum-config' ) . '" aria-label="' . esc_attr__( 'View Datadog RUM settings', 'wp-datadog-rum' ) . '">' . esc_html__( 'Settings', 'wp-datadog-rum' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}
}
