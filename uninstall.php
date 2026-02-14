<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package TLF
 * @since   1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

/**
 * Delete plugin data on uninstall.
 *
 * @since 1.0.0
 *
 * @return void
 */
function tlf_delete_plugin() {
	// Delete process.
}

tlf_delete_plugin();
