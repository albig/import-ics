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

	private $import_ics_events;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name      The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('admin_menu', array( $this, 'addPluginAdminMenu' ), 9);
		add_action('admin_init', array( $this, 'registerAndBuildFields'));

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
	public function addPluginAdminMenu() {

		//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page( $this->plugin_name, 'Import ICS', 'administrator', $this->plugin_name, array( $this, 'displayPluginAdminDashboard' ), 'dashicons-calendar', 26 );

		//add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		add_submenu_page( $this->plugin_name, 'Import ICS Settings', 'Settings', 'administrator', $this->plugin_name.'-settings', array( $this, 'displayPluginAdminSettings' ));
	}

	/**
	 * Include admin display partial.
	 *
	 * @since    1.0.0
	 */
	public function displayPluginAdminDashboard() {
		require_once 'partials/' . $this->plugin_name . '-admin-display.php';
  	}

	/**
	 * Include admin settings partial.
	 *
	 * @since    1.0.0
	 */
	public function displayPluginAdminSettings() {
		// set this var to be used in the settings-display view
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		if(isset($_GET['error_message'])){
			add_action('admin_notices', array($this,'importIcsSettingsMessages'));
			do_action( 'admin_notices', $_GET['error_message'] );
		}
		require_once 'partials/'.$this->plugin_name.'-admin-settings-display.php';
	}

	public function importIcsSettingsMessages($error_message){
		switch ($error_message) {
			case '1':
				$message = __( 'There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'my-text-domain' );
				$err_code = esc_attr( 'import_ics_example_setting' );
				$setting_field = 'import_ics_example_setting';
				break;
		}
		$type = 'error';
		add_settings_error(
			   $setting_field,
			   $err_code,
			   $message,
			   $type
		   );
	}

	public function registerAndBuildFields() {
		/**
		 * First, we add_settings_section. This is necessary since all future settings must belong to one.
		 * Second, add_settings_field
		 * Third, register_setting
		 */
		add_settings_section(
			// ID used to identify this section and with which to register options
			'import_ics_general_section',
			// Title to be displayed on the administration page
			esc_html__('ICS calendar configuration', 'import-ics'),
			// Callback used to render the description of the section
			array($this, 'import_ics_display_general_account'),
			// Page on which to add this section of options
			'import_ics_general_settings'
		);

		// import_ics_setting_1_url
		add_settings_field(
			'import_ics_setting_1_url',
			esc_html__('URL to the calendar ics file', 'import-ics'),
			array($this, 'import_ics_render_settings_field'),
			'import_ics_general_settings',
			'import_ics_general_section',
			array (
				'type' => 'input',
				'subtype' => 'text',
				'id' => 'import_ics_setting_1_url',
				'name' => 'import_ics_setting_1_url',
				'get_options_list' => '',
				'value_type'=>'normal',
				'wp_data' => 'option',
				'required' => true,
				'size' => 120
			)
		);

		register_setting(
			'import_ics_general_settings',
			'import_ics_setting_1_url'
		);

		// import_ics_setting_1_interval
		$select_options = array(
			'3600' => esc_html__('1 Hour', 'import-ics'),
			'7200' => esc_html__('2 Hours', 'import-ics'),
			'43200' => esc_html__('12 Hours', 'import-ics'),
			'86400' => esc_html__('24 Hours', 'import-ics'),
		);

		if (defined('WP_DEBUG') && WP_DEBUG) {
			$select_options['2'] = esc_html__('Immediately (Development)', 'import-ics');
		}

		add_settings_field(
			'import_ics_setting_1_interval',
			esc_html__('Renew import interval', 'import-ics'),
			array($this, 'import_ics_render_settings_field'),
			'import_ics_general_settings',
			'import_ics_general_section',
			array (
				'type' => 'select',
				'subtype' => 'text',
				'id' => 'import_ics_setting_1_interval',
				'name' => 'import_ics_setting_1_interval',
				'select_options' => $select_options,
				'default' => '43200',
				'value_type'=>'normal',
				'wp_data' => 'option',
				'required' => true,
			)
		);

		register_setting(
			'import_ics_general_settings',
			'import_ics_setting_1_interval'
		);

		// import_ics_setting_interval_before
		add_settings_field(
			'import_ics_setting_interval_before',
			esc_html__('Time interval: days before today', 'import-ics'),
			array($this, 'import_ics_render_settings_field'),
			'import_ics_general_settings',
			'import_ics_general_section',
			array (
				'type' => 'input',
				'subtype' => 'text',
				'id' => 'import_ics_setting_interval_before',
				'name' => 'import_ics_setting_interval_before',
				'default' => '15',
				'get_options_list' => '',
				'value_type'=>'normal',
				'wp_data' => 'option',
				'required' => true,
				'size' => 5
			)
		);

		register_setting(
			'import_ics_general_settings',
			'import_ics_setting_interval_before'
		);

		// import_ics_setting_interval_after
		add_settings_field(
			'import_ics_setting_interval_after',
			esc_html__('Time interval: days after today', 'import-ics'),
			array($this, 'import_ics_render_settings_field'),
			'import_ics_general_settings',
			'import_ics_general_section',
			array (
				'type' => 'input',
				'subtype' => 'text',
				'id' => 'import_ics_setting_interval_after',
				'name' => 'import_ics_setting_interval_after',
				'default' => '366',
				'get_options_list' => '',
				'value_type'=>'normal',
				'wp_data' => 'option',
				'required' => true,
				'size' => 5
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
		echo '<p>' . esc_html__('Enter the ICS download links and configure the renewal interval.', 'import-ics') . '</p>';
	}

	public function import_ics_render_settings_field($args) {
		/* EXAMPLE INPUT
							'type'      => 'input',
							'subtype'   => '',
							'id'    => $this->plugin_name.'_example_setting',
							'name'      => $this->plugin_name.'_example_setting',
							'required' => 'required="required"',
							'get_option_list' => "",
								'value_type' = serialized OR normal,
		'wp_data'=>(option or post_meta),
		'post_id' =>
		*/
		if($args['wp_data'] == 'option'){
			$wp_data_value = get_option($args['name']);
		} elseif($args['wp_data'] == 'post_meta'){
			$wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
		}

		switch ($args['type']) {

			case 'input':
					$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
					if (empty($value) && isset($args['default'])) {
						$value = $args['default'];
					}
					if ($args['subtype'] != 'checkbox') {
						$prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">'.$args['prepend_value'].'</span>' : '';
						$prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
						$step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
						$min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
						$max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
						$required = (isset($args['required'])) ? 'required="required"' : '';
						if (isset($args['disabled'])) {
								// hide the actual input bc if it was just a disabled input the info saved in the database would be wrong - bc it would pass empty values and wipe the actual information
								echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
						} else {
								echo $prependStart . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" ' . $required . ' ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="' . ($args['size'] ?? '40') . '" value="' . esc_attr($value) . '" />' . $prependEnd;
						}
						/*<input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" />*/
					} else {
							$checked = ($value) ? 'checked' : '';
							echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "' . $required . '" name="' . $args['name'] . '" size="' . ($args['size'] ?? '40') . '" value="1" '.$checked.' />';
					}
					break;
			case 'select':
				$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
				if (empty($value) && isset($args['default'])) {
					$value = $args['default'];
				}
				echo '<select name="' . $args['name'] . '" id="' . $args['id'] . '">';
				foreach ($args['select_options'] as $key => $option) {
					if ($value == $key) {
						$selected = 'selected';
					} else {
						$selected = '';
					}
					echo '<option value="' . $key . '" ' . $selected . '>' . $option . '</option>';
				}
				echo '</select>';
				break;
			default:
					# code...
					break;
		}

	}

}
