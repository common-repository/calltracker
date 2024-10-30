<?php
/**
 * Plugin Name: CallTracker
 * Version: 1.5
 * Description: WordPress Integration for Call Tracker
 * Author: calltracker
 * Author URI: https://calltracker.io
 * Plugin URI: https://calltracker.io/integrations/wordpress/
 * Text Domain: calltracker
 */

class CallTrackerIO {

	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
		add_action( 'admin_init', array( $this, 'calltracker_admin_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_dni_to_footer' ) );
		add_filter( 'script_loader_tag', array ( $this, 'add_data_cloudflare_attribute' ), 10, 3 );
	}

	function add_admin_pages() {
		add_options_page(
			'Call Tracker',
			'Call Tracker',
			'manage_options',
			'calltracker-settings',
			array( $this, 'calltracker_options_page' )
		);
	}

	function calltracker_admin_init() {
	    add_settings_section(
	    	'settings',
	    	'Call Tracker API Key',
	    	array($this, 'settings_section'),
	    	'calltracker-settings'
	    );
	    add_settings_field(
	    	'calltracker-api-key',
	    	'API Key',
	    	array($this, 'call_tracker_api_key'),
	    	'calltracker-settings',
	    	'settings'
	    );
	    add_settings_field(
			'dni-cf-rocketscript-support',
			'CloudFlare Rocket Script',
			array($this, 'cloudflare_rocketscript_support'),
			'calltracker-settings',
			'settings'
		);
		register_setting(
			'settings-group',
			'calltracker-api-key'
		);
		register_setting(
			'settings-group',
			'dni-cf-rocketscript-support'
		);
	}

	function settings_section() {
	    echo 'Enter your Call Tracker API Key below. Your API Key can be found on your company settings page, <a href="https://app.calltracker.io/help/article/wordpress-integration-guide/" target="_blank">read more.</a>.';
	}

	function call_tracker_api_key() {
	    $api_key = esc_attr( get_option( 'calltracker-api-key' ) );
	    echo "<input type='text' name='calltracker-api-key' value='$api_key' size='35' />";
	}

	function cloudflare_rocketscript_support() {
		echo '<input name="dni-cf-rocketscript-support" id="dni-cf-rocketscript-support" type="checkbox" value="1" class="code" ';
		echo  checked( 1, get_option( 'dni-cf-rocketscript-support' ), false );
		echo ' /> Enable support CloudFlare Rocket Script.';
	 }

	function calltracker_options_page() {
    ?>
    <div class="wrap">
        <h2>Call Tracker Settings</h2>
        <form action="options.php" method="POST">
            <?php settings_fields( 'settings-group' ); ?>
            <?php do_settings_sections( 'calltracker-settings' ); ?>
            <?php submit_button('Update Settings'); ?>
        </form>
        <p>Thank you for choosing <a href="https://calltracker.io/?utm_source=wordpress-plugin&utm_medium=wordpress&utm_campaign=wordpress%20admin%20link" target="_blank">Call Tracker</a>.
        If you have any questions or issues, don't hesitate to reach out and <a href="mailto:help@calltracker.io">get some help from our team</a>.</p>
    </div>
    <?php
	}

	function add_dni_to_footer(){
		$api_key = get_option('calltracker-api-key');
		$cloudflare_rocketscript = get_option('dni-cf-rocketscript-support');
		if ( $api_key ) {
			$calltracker_dni = 'https://dni.calltracker.io/trackers/';
			$calltracker_dni .= $api_key;
			$calltracker_dni .= '/tracker.js';
			if ( $cloudflare_rocketscript == 0 ) {
				$calltracker_dni .= '?no-jquery=true';
			}
			wp_enqueue_script( 'call-tracker-dni', $calltracker_dni, array( 'jquery' ), '1.0', true );
		}
	}

	function add_data_cloudflare_attribute( $tag, $handle, $src ) {
		$cloudflare_rocketscript = get_option('dni-cf-rocketscript-support');
	    if ( $cloudflare_rocketscript == 1 && 'call-tracker-dni' == $handle ) {
	    	return str_replace( ' src', ' data-cfasync="false" src', $tag );
	    } else {
	    	return $tag;
	    }
	}

}

new calltrackerio();
