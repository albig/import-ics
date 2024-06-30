<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       plugin_name.com/team
 * @since      1.0.0
 *
 * @package    Import_Ics
 * @subpackage Import_Ics/admin/partials
 */

?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h2>Import ICS Calendar Settings</h2>
		<!--
			NEED THE settings_errors below so that the errors/success messages are
			shown after submission - wasn't working once we started using add_menu_page
			and stopped using add_options_page so needed this
		-->
	<?php settings_errors(); ?>

	<?php
	if (  isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) { // phpcs:ignore
		$import_ics_1_interval = get_option( 'import_ics_setting_1_interval' );
		if ( false !== $import_ics_1_interval ) {
			set_transient( 'import_ics_event_manager_done', 1, $import_ics_1_interval );
		}
	}
	?>

	<form method="POST" action="options.php">
		<?php
			settings_fields( 'import_ics_general_settings' );
			do_settings_sections( 'import_ics_general_settings' );
		?>
		<?php submit_button(); ?>
	</form>
</div>
