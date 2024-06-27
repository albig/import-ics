<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://bigga.de
 * @since      1.0.0
 *
 * @package    Import_Ics
 * @subpackage Import_Ics/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the Import ICS, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Import_Ics
 * @subpackage Import_Ics/admin
 * @author     Alexander Bigga <alexander@bigga.de>
 */
class Import_Ics_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name      The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ), 9 );
		add_action( 'admin_init', array( $this, 'register_and_build_fields' ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Import_Ics_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Import_Ics_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/import-ics-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Import_Ics_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Import_Ics_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/import-ics-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Adds plugin admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		add_menu_page( $this->plugin_name, 'Import ICS', 'edit_pages', $this->plugin_name, array( $this, 'display_plugin_admin_dashboard' ), 'dashicons-calendar', 26 );
		add_submenu_page( $this->plugin_name, 'Import ICS Settings', 'Settings', 'edit_pages', $this->plugin_name . '-settings', array( $this, 'display_plugin_admin_settings' ) );
	}

	/**
	 * Include admin display partial.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_dashboard() {
		require_once 'partials/' . $this->plugin_name . '-admin-display.php';
	}

	/**
	 * Include admin settings partial.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_settings() {
		require_once 'partials/' . $this->plugin_name . '-admin-settings-display.php';
	}

	/**
	 * Register and add event settings
	 */
	public function register_and_build_fields(): void {
		/**
		 * First, we add_settings_section. This is necessary since all future settings must belong to one.
		 * Second, add_settings_field
		 * Third, register_setting
		 */
		add_settings_section(
			// ID used to identify this section and with which to register options.
			'import_ics_general_section',
			// Title to be displayed on the administration page.
			esc_html__( 'ICS calendar configuration', 'import-ics' ),
			// Callback used to render the description of the section.
			array( $this, 'import_ics_display_general_account' ),
			// Page on which to add this section of options.
			'import_ics_general_settings'
		);

		add_settings_field(
			'import_ics_setting_1_url',
			esc_html__( 'URL to the calendar ics file', 'import-ics' ),
			array( $this, 'import_ics_render_settings_field' ),
			'import_ics_general_settings',
			'import_ics_general_section',
			array(
				'type'             => 'input',
				'subtype'          => 'text',
				'id'               => 'import_ics_setting_1_url',
				'name'             => 'import_ics_setting_1_url',
				'get_options_list' => '',
				'wp_data'          => 'option',
				'required'         => true,
				'size'             => 120,
			)
		);

		register_setting(
			'import_ics_general_settings',
			'import_ics_setting_1_url'
		);

		$select_options = array(
			'3600'  => esc_html__( '1 Hour', 'import-ics' ),
			'7200'  => esc_html__( '2 Hours', 'import-ics' ),
			'43200' => esc_html__( '12 Hours', 'import-ics' ),
			'86400' => esc_html__( '24 Hours', 'import-ics' ),
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$select_options['2'] = esc_html__( 'Immediately (Development)', 'import-ics' );
		}

		add_settings_field(
			'import_ics_setting_1_interval',
			esc_html__( 'Renew import interval', 'import-ics' ),
			array( $this, 'import_ics_render_settings_field' ),
			'import_ics_general_settings',
			'import_ics_general_section',
			array(
				'type'           => 'select',
				'subtype'        => 'text',
				'id'             => 'import_ics_setting_1_interval',
				'name'           => 'import_ics_setting_1_interval',
				'select_options' => $select_options,
				'default'        => '43200',
				'wp_data'        => 'option',
				'required'       => true,
			)
		);

		register_setting(
			'import_ics_general_settings',
			'import_ics_setting_1_interval'
		);

		add_settings_field(
			'import_ics_setting_interval_before',
			esc_html__( 'Time interval: days before today', 'import-ics' ),
			array( $this, 'import_ics_render_settings_field' ),
			'import_ics_general_settings',
			'import_ics_general_section',
			array(
				'type'             => 'input',
				'subtype'          => 'text',
				'id'               => 'import_ics_setting_interval_before',
				'name'             => 'import_ics_setting_interval_before',
				'default'          => '15',
				'get_options_list' => '',
				'wp_data'          => 'option',
				'required'         => true,
				'size'             => 5,
			)
		);

		register_setting(
			'import_ics_general_settings',
			'import_ics_setting_interval_before'
		);

		add_settings_field(
			'import_ics_setting_interval_after',
			esc_html__( 'Time interval: days after today', 'import-ics' ),
			array( $this, 'import_ics_render_settings_field' ),
			'import_ics_general_settings',
			'import_ics_general_section',
			array(
				'type'             => 'input',
				'subtype'          => 'text',
				'id'               => 'import_ics_setting_interval_after',
				'name'             => 'import_ics_setting_interval_after',
				'default'          => '366',
				'get_options_list' => '',
				'wp_data'          => 'option',
				'required'         => true,
				'size'             => 5,
			)
		);

		register_setting(
			'import_ics_general_settings',
			'import_ics_setting_interval_after'
		);
	}


	/**
	 * Render help description on admin dashboard.
	 *
	 * @since    1.0.0
	 */
	public function import_ics_display_general_account() {
		echo '<p>' . esc_html__( 'Enter the ICS download links and configure the renewal interval.', 'import-ics' ) . '</p>';
	}

	/**
	 * Render the given form field
	 *
	 * @param Array $args The field data array.
	 */
	public function import_ics_render_settings_field( $args ) {

		if ( 'option' === $args['wp_data'] ) {
			$wp_data_value = get_option( $args['name'] );
		} elseif ( 'post_meta' === $args['wp_data'] ) {
			$wp_data_value = get_post_meta( $args['post_id'], $args['name'], true );
		}

		switch ( $args['type'] ) {

			case 'input':
					$value = $wp_data_value;
				if ( empty( $value ) && isset( $args['default'] ) ) {
					$value = $args['default'];
				}
				if ( 'checkbox' !== $args['subtype'] ) {
					$prepend_start = ( isset( $args['prepend_value'] ) ) ? '<div class="input-prepend"> <span class="add-on">' . esc_attr( $args['prepend_value'] ) . '</span>' : '';
					$prepend_end   = ( isset( $args['prepend_value'] ) ) ? '</div>' : '';
					$step          = ( isset( $args['step'] ) ) ? 'step="' . $args['step'] . '"' : '';
					$min           = ( isset( $args['min'] ) ) ? 'min="' . $args['min'] . '"' : '';
					$max           = ( isset( $args['max'] ) ) ? 'max="' . $args['max'] . '"' : '';
					$required      = ( isset( $args['required'] ) ) ? 'required="required"' : '';
					if ( isset( $args['disabled'] ) ) {
							// hide the actual input bc if it was just a disabled input the info saved in the database would be wrong - bc it would pass empty values and wipe the actual information.
							echo wp_kses_post( $prepend_start ) . '<input type="' . esc_attr( $args['subtype'] ) . '" id="' . esc_attr( $args['id'] ) . '_disabled" ' . esc_attr( $step ) . ' ' . esc_attr( $max ) . ' ' . esc_attr( $min ) . ' name="' . esc_attr( $args['name'] ) . '_disabled" size="40" disabled value="' . esc_attr( $value ) . '" /><input type="hidden" id="' . esc_attr( $args['id'] ) . '" ' . esc_attr( $step ) . ' ' . esc_attr( $max ) . ' ' . esc_attr( $min ) . ' name="' . esc_attr( $args['name'] ) . '" size="40" value="' . esc_attr( $value ) . '" />' . wp_kses_post( $prepend_end );
					} else {
							echo wp_kses_post( $prepend_start ) . '<input type="' . esc_attr( $args['subtype'] ) . '" id="' . esc_attr( $args['id'] ) . '" ' . esc_attr( $required ) . ' ' . esc_attr( $step ) . ' ' . esc_attr( $max ) . ' ' . esc_attr( $min ) . ' name="' . esc_attr( $args['name'] ) . '" size="' . esc_attr( $args['size'] ?? '40' ) . '" value="' . esc_attr( $value ) . '" />' . wp_kses_post( $prepend_end );
					}
				} else {
						$checked = ( $value ) ? 'checked' : '';
						echo '<input type="' . esc_attr( $args['subtype'] ) . '" id="' . esc_attr( $args['id'] ) . '" "' . esc_attr( $required ) . '" name="' . esc_attr( $args['name'] ) . '" size="' . esc_attr( $args['size'] ?? '40' ) . '" value="1" ' . esc_attr( $checked ) . ' />';
				}
				break;
			case 'select':
				$value = $wp_data_value;
				if ( empty( $value ) && isset( $args['default'] ) ) {
					$value = $args['default'];
				}
				echo '<select name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '">';
				foreach ( $args['select_options'] as $key => $option ) {
					if ( (int) $value === $key ) {
						$selected = 'selected';
					} else {
						$selected = '';
					}
					echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $option ) . '</option>';
				}
				echo '</select>';
				break;
			default:
					// code...
				break;
		}
	}
}
