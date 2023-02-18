<?php
/**
 * Import events into Events-Manager
 *
 * @link       https://bigga.de
 * @since      1.0.0
 *
 * @package    Import_Ics
 * @subpackage Import_Ics/includes
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/../vendor/autoload.php';

use ICal\ICal;

class Import_Ics_Event_Manager {

	// The Events Calendar Event Taxonomy
	protected $taxonomy;

	// The Events Calendar Event Posttype
	protected $event_posttype;

	// filter for events 2 weeks < now < 1 year
	protected $filterDaysAfter = 366;
	protected $filterDaysBefore = 15;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		add_action('admin_init', array($this, 'setup_success_messages') );

	}

	/**
	 * Register Session
	 *
	 * @since    1.0.0
	 */
	public function setup_success_messages() {
		echo esc_html__( 'Hello world.', 'import-ics' );
	}


	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_form_submit() {

		// if background job already scheduled - skip
		if (get_transient('import_ics_event_manager_done')) {
			return false;
		}

		if (! $import_ics_url_1 = get_option('import_ics_setting_1_url')) {
			return false;
		}

		if (! filter_var($import_ics_url_1, FILTER_VALIDATE_URL) ) {
			return false;
		}

		$event_data = array();

		$import_1_interval = get_option('import_ics_setting_1_interval') ?: 86400;

		set_transient('import_ics_event_manager_done', 1, $import_1_interval);

		try {

			$ical = new ICal('', array(
				'defaultSpan'                 => 2,     // Default value
				'defaultTimeZone'             => 'Europe/Berlin',
				'defaultWeekStart'            => 'MO',  // Default value
				'disableCharacterReplacement' => false, // Default value
				'filterDaysAfter'             => $this->filterDaysAfter,   // Default value
				'filterDaysBefore'            => $this->filterDaysBefore,  	// Default value
				'httpUserAgent'               => null,  // Default value
				'skipRecurrence'              => false, // Default value
			));

			$ical->initUrl($import_ics_url_1);

		} catch (\Exception $e) {

			die($e);

		}

		$response = $this->import_ics_event_manager($ical);
		$ids_from_remote = $response[0];

		$local_events_having_uid = $this->events_having_uid();

		// move all local events into trash which have been deleted on remote side
		$deleted_on_remote = array_diff($local_events_having_uid, $ids_from_remote);
		foreach ($deleted_on_remote as $to_be_deleted) {
			wp_trash_post($to_be_deleted);
		}
	}

	/**
	 * Import events to event manager
	 *
	 * @since    1.0.0
	 */
	private function import_ics_event_manager($ical) {
		global $wpdb;

		$updated_events = 0;

		foreach ($ical->events() as $event) {
			$uid = $event->uid;

			// if (isset($event->rrule)) {
			// 	if ( isset( $count_recurring_events[ $uid ] ) ) {
			// 		$count_recurring_events[ $uid ]++;
			// 	} else {
			// 		$count_recurring_events[ $uid ] = 1;
			// 	}

			// 	if ( $count_recurring_events[ $uid ] > $recurring_events_max ) {
			// 		continue;
			// 	}
			// 	$uid .= '_' . $event->dtstart;
			// }

			// is this event already imported
			$is_imported = $this->get_event_by_uid($uid);

			$wp_id = 0;

			if ($is_imported->have_posts()) {
				$is_imported->the_post();
				$wp_id = get_the_ID();
				$updated_events++;
			}

			$event_summary = [];
			// we use the EM_DateTime object from event manager
			$em_start_datetime = new EM_DateTime($ical->iCalDateToUnixTimestamp($event->dtstart));
			$em_end_datetime = new EM_DateTime($ical->iCalDateToUnixTimestamp($event->dtend));

			$event_summary['startDateTime'] = $em_start_datetime;
			if (strlen($event->dtstart) == 8 && strlen($event->dtend) == 8) {
				$event_summary['isAllDay'] = true;
			} else {
				$event_summary['isAllDay'] = false;
			}
			$event_summary['endDateTime'] = $em_end_datetime;

			$post = array(
				'ID' => $wp_id,
				'post_type' => 'event',
				'post_title' => $event->summary,
				'post_content' => sprintf('<!-- wp:paragraph -->%s<!-- /wp:paragraph -->', nl2br($event->description ?? '')),
				'post_status' => 'publish',
			);

			$inserted_post_id = wp_insert_post((array) $post, true);

			if (is_wp_error($inserted_post_id)) {
				return false;
			}

			$ids_from_remote[] = $inserted_post_id;

			update_post_meta($inserted_post_id, '_event_timezone', 'Europe/Berlin');

			update_post_meta($inserted_post_id, '_event_start', $event_summary['startDateTime']->getDateTime(true));
			update_post_meta($inserted_post_id, '_event_start_date', $event_summary['startDateTime']->getDate());
			update_post_meta($inserted_post_id, '_event_start_time', ($event_summary['isAllDay'] ? '00:00:00' : $event_summary['startDateTime']->getTime()),);
			update_post_meta($inserted_post_id, '_event_start_local', $event_summary['startDateTime']->getDateTime());

			update_post_meta($inserted_post_id, '_event_end', $event_summary['endDateTime']->getDateTime(true));
			update_post_meta($inserted_post_id, '_event_end_date', $event_summary['endDateTime']->getDate());
			update_post_meta($inserted_post_id, '_event_end_time', $event_summary['isAllDay'] ? '23:59:59' : $event_summary['endDateTime']->getTime());
			update_post_meta($inserted_post_id, '_event_end_local', $event_summary['endDateTime']->getDateTime());

			update_post_meta($inserted_post_id, '_importics_event_uid', $uid);

			if ($event_summary['isAllDay'] == true) {
				update_post_meta($inserted_post_id, '_event_all_day', 1);
				update_post_meta($inserted_post_id, '_event_start_time', '00:00:00');
			}

			// if ( isset( $event->location ) ) {
			// 	update_post_meta( $inserted_post_id, '_sunflower_event_location_name', $event->location );
			// 	$coordinates = sunflower_geocode( $event->location );
			// 	if ( $coordinates ) {
			// 		list($lon, $lat) = $coordinates;
			// 		update_post_meta( $inserted_post_id, '_sunflower_event_lat', $lat );
			// 		update_post_meta( $inserted_post_id, '_sunflower_event_lon', $lon );
			// 		$zoom = sunflower_get_constant( 'SUNFLOWER_EVENT_IMPORTED_ZOOM' ) ?: 12;
			// 		update_post_meta( $inserted_post_id, '_sunflower_event_zoom', $zoom );
			// 	}
			// }

			$categories  = (isset($event->categories)) ? $event->categories : '';
			//$categories .= ( $auto_categories ) ? ',' . $auto_categories : '';

			if ($categories) {
				wp_set_post_terms($inserted_post_id, $categories, 'importics_event_tag');
			}

			// write post / event into Events Manager custom table
			$inserted_event = get_post($inserted_post_id);
			if (empty($inserted_event)) {
				return false;
			}

			// Custom table Details
			$event_array = array(
				'post_id' 		   	=> $inserted_post_id,
				'event_slug' 	   	=> $inserted_event->post_name,
				'event_owner' 	   	=> $inserted_event->post_author,
				'event_name'       	=> $inserted_event->post_title,
				'event_start_time' 	=> $event_summary['isAllDay'] ? '00:00:00' : $event_summary['startDateTime']->getTime(),
				'event_end_time'   	=> $event_summary['isAllDay'] ? '23:59:59' : $event_summary['endDateTime']->getTime(),
				'event_all_day'    	=> $event_summary['isAllDay'],
				'event_start'		=> $event_summary['startDateTime']->getDateTime(true),
				'event_end'		   	=> $event_summary['endDateTime']->getDateTime(true),
				'event_timezone'	=> 'UTC',
				'event_start_date' 	=> $event_summary['startDateTime']->getDate(),
				'event_end_date'   	=> $event_summary['endDateTime']->getDate(),
				'post_content' 	   	=> $inserted_event->post_content,
				'location_id' 	   	=> $location_id,
				'event_status' 	   	=> ($inserted_event->post_status == 'publish' ? 1 : 0),
				'event_date_created'=> $inserted_event->post_date,
			);

			$event_table = $wpdb->prefix . 'em_events';

			// check for already existing event
			$event_count = $wpdb->get_var('SELECT COUNT(*) FROM `' . $event_table . '` WHERE `post_id` = ' . absint($inserted_post_id));
			if ($event_count > 0 && is_numeric($event_count)) {
				$where = array('post_id' => absint($inserted_post_id));
				$wpdb->update($event_table, $event_array, $where);
			} else {
				if ($wpdb->insert($event_table, $event_array)) {
					update_post_meta($inserted_post_id, '_event_id', $wpdb->insert_id);
				}
			}

		}

		return array($ids_from_remote, count($ical->events()) - $updated_events, $updated_events);
	}

	private function get_event_by_uid ($uid) {
		return new WP_Query(
			array(
				'post_type' => 'event',
				'meta_key' => '_importics_event_uid',
				'orderby' => 'meta_value',
				'meta_query' => array(
					array(
						'key' => '_importics_event_uid',
						'value' => $uid,
						'compare' => '=',
					),
				),
			)
		);
	}

	private function events_having_uid() {
		$events_with_uid = new WP_Query(
			array(
				'nopaging' => true,
				'post_type' => 'event',
				'meta_key' => '_importics_event_uid',
				'orderby' => 'meta_value',
				'date_query' => array(
					array(
						'after' => (new \DateTime('now'))->sub(new \DateInterval('P' . $this->filterDaysBefore . 'D'))->format('Y-m-d'),
						'before' => (new \DateTime('now'))->add(new \DateInterval('P' . $this->filterDaysAfter . 'D'))->format('Y-m-d')
					)
				),
				'meta_query' => array(
					array(
						'key' => '_importics_event_uid',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		$ids = array();
		while ( $events_with_uid->have_posts() ) {
			$events_with_uid->the_post();
			$ids[] = get_the_ID();
		}

		return $ids;
	}

}

function run_import_ics_event_manager() {

	$plugin = new Import_Ics_Event_Manager();

	$plugin->handle_import_form_submit();


}
add_action('init', 'run_import_ics_event_manager');
