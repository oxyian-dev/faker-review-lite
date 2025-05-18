<?php
/**
 * Uninstall file for Faker Review
 * 
 * This file runs when the plugin is uninstalled.
 * The plugin doesn't create any database tables or persistent data, 
 * so no cleanup is needed.
 * 
 * @package Faker Review
 */

// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Note: The plugin doesn't create any database tables or options to clean up.
// The reviews created by this plugin are stored as standard WooCommerce product reviews
// and are not deleted on uninstall as they might be intentionally kept for testing/demo purposes.
