<?php
/*
 * Plugin Name: WooCommerce PayWay Hrvatski Telekom payment gateway
 * Plugin URI:  https://github.com/marinsagovac/woocommerce-tcom-payway
 * Description: WooCommerce PayWay Hrvatski Telekom payment gateway
 * Version:     1.7.1
 * Licence:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Author:      Marin Šagovac
 * Developers:  Marin Šagovac, Matija Kovačević, Danijel Gubić, Ivan Švaljek, Micemade
 * Text Domain: tcom-payway-wc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Plugin directory, with trailing slash.
if ( ! defined( 'TCOM_PAYWAY_DIR' ) ) {
	define( 'TCOM_PAYWAY_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin URL, with trailing slash.
if ( ! defined( 'TCOM_PAYWAY_URL' ) ) {
	define( 'TCOM_PAYWAY_URL', plugin_dir_url( __FILE__ ) );
}

load_plugin_textdomain( 'tcom-payway-wc', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );

add_action('plugins_loaded', 'woocommerce_tpayway_gateway', 0);
function woocommerce_tpayway_gateway() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	require_once TCOM_PAYWAY_DIR . 'classes/class-wc-tpayway.php';

	$wc = new WC_TPAYWAY();

	function woocommerce_add_tpayway_gateway( $methods ) {
		$methods[] = 'WC_TPAYWAY';
		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_tpayway_gateway' );
}

global $jal_db_version;
$jal_db_version = '0.1';

function jal_install_tpayway() {
	global $wpdb;
	global $jal_db_version;

	$table_name      = $wpdb->prefix . 'tpayway_ipg';
	$charset_collate = '';

	if ( ! empty( $wpdb->charset ) ) {
		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}

	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        transaction_id int(9) NOT NULL,
        response_code int(6) NOT NULL,
        response_code_desc VARCHAR(20) NOT NULL,
        reason_code VARCHAR(20) NOT NULL,
        amount VARCHAR(20) NOT NULL,
        or_date DATE NOT NULL,
        status int(6) NOT NULL,
        UNIQUE KEY id (id)
    ) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	add_option( 'jal_db_version', $jal_db_version );
}
register_activation_hook( __FILE__, 'jal_install_tpayway');

function jal_install_data_tpayway() {
	global $wpdb;

	$welcome_name = 'PayWay Hrvatski Telekom';
	$welcome_text = 'Congratulations, you just completed the installation!';

	$table_name = $wpdb->prefix . 'tpayway_ipg';
}
register_activation_hook( __FILE__, 'jal_install_data_tpayway');

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'jal_add_plugin_page_settings_link');
function jal_add_plugin_page_settings_link( $links ) {
    $links[] = '<a href="' .
        admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_tpayway') .
        '">' . __('Settings') . '</a>';
    return $links;
}

if (is_admin()) {
	require_once TCOM_PAYWAY_DIR . 'classes/admin/class-payway-wp-list-table.php';
	new Payway_Wp_List_Table();
}
