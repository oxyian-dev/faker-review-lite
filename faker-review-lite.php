<?php
/*
Plugin Name: Faker Review Lite 
Plugin URI: https://oxyian.com/
Description: Generate simple fake reviews for WooCommerce products (up to 15 reviews per product). For additional features like custom rating distribution, date range selection, and more reviews per product, please upgrade to the premium version.
Version: 1.1
Author: OXYIAN
Author URI: https://oxyian.com/
License: GPL2
Text Domain: faker-review
Domain Path: /languages
Tested up to: 6.8
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Ensure WordPress functions are available
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-includes/formatting.php');
require_once(ABSPATH . WPINC . '/pluggable.php');

// Define plugin constants
define('FAKER_REVIEW_VERSION', '1.1');
define('FAKER_REVIEW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FAKER_REVIEW_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load plugin textdomain
 */
function drg_load_textdomain() {
    load_plugin_textdomain('faker-review', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'drg_load_textdomain');

/**
 * Check if WooCommerce is active
 */
function drg_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            ?>
            <div class="error notice">
                <p><?php echo esc_html__('Faker Review requires WooCommerce to be installed and active.', 'faker-review'); ?></p>
            </div>
            <?php
        });
        return false;
    }
    return true;
}

/**
 * Initialize the plugin
 */
function drg_init() {
    // Check if WooCommerce is active
    if (!drg_check_woocommerce()) {
        return;
    }

    // Admin menu
    add_action('admin_menu', 'drg_add_admin_menu');

    // Register scripts and styles
    add_action('admin_enqueue_scripts', 'drg_enqueue_admin_assets');
}
add_action('plugins_loaded', 'drg_init');

/**
 * Add admin menu item
 */
function drg_add_admin_menu() {
    add_submenu_page(
        'tools.php',
        __('Faker Review', 'faker-review'),
        __('Faker Review', 'faker-review'),
        'manage_options',
        'faker-generator',
        'drg_render_admin_page'
    );
}

/**
 * Enqueue admin scripts and styles
 */
function drg_enqueue_admin_assets($hook) {
    if ($hook != 'tools_page_faker-generator') {
        return;
    }

    wp_enqueue_style('dashicons');

    // Register and enqueue JavaScript for form handling
    wp_enqueue_script('drg-admin-script', '', array(), FAKER_REVIEW_VERSION, true);
    wp_add_inline_script('drg-admin-script', '
        document.addEventListener("DOMContentLoaded", function() {
            const scopeSelect = document.querySelector("select[name=\'drg_scope\']");
            const productsSelect = document.querySelector("select[name=\'drg_products[]\']");
            const categorySelect = document.querySelector("select[name=\'drg_categories[]\']");
            
            function updateProductsSelect() {
                const isAllSelected = scopeSelect.value === "all";
                const isCategorySelected = scopeSelect.value === "category";
                
                productsSelect.disabled = isAllSelected;
                categorySelect.style.display = isCategorySelected ? "block" : "none";
            }
            
            // Initial state
            updateProductsSelect();
            
            // Update on change
            scopeSelect.addEventListener("change", updateProductsSelect);
        });
    ');
}

/**
 * Admin page render
 */
function drg_render_admin_page() {
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'faker-review'));
    }

    // Check if WooCommerce is active
    if (!drg_check_woocommerce()) {
        return;
    }

    // Get all published products
    $products = wc_get_products(['status' => 'publish', 'limit' => -1]);

    // Handle form submission
    if (isset($_POST['drg_submit']) && check_admin_referer('drg_generate_reviews', 'drg_nonce')) {
        drg_handle_generation();
    }
    ?>
    <div class="wrap">
        <style>
            .drg-container { max-width: 900px; margin: 20px 0; }
            .drg-section {
                background: #fff;
                border: 1px solid #dcdcde;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                border-radius: 4px;
                margin-bottom: 24px;
                padding: 24px;
            }
            .drg-section-title {
                border-bottom: 1px solid #f0f0f1;
                padding-bottom: 16px;
                margin-bottom: 20px;
                font-size: 15px;
                font-weight: 600;
                color: #1d2327;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .drg-section-description {
                color: #646970;
                font-size: 13px;
                margin-bottom: 20px;
                max-width: 700px;
            }
            .drg-form-group {
                display: grid;
                grid-template-columns: minmax(180px, 220px) minmax(300px, 1fr);
                gap: 16px;
                margin-bottom: 20px;
                align-items: start;
            }
            .drg-form-group label { font-weight: 600; }
            .drg-help-text {
                color: #646970;
                font-size: 12px;
                margin-top: 4px;
            }
            .pro-feature {
                background: #f0f6fc;
                border: 1px solid #72aee6;
                padding: 12px;
                border-radius: 4px;
                margin-top: 20px;
            }
            .drg-submit {
                margin-top: 24px;
                text-align: right;
            }
        </style>

        <div class="drg-container">
            <div class="drg-header">
                <h1><?php esc_html_e('Review Generator Lite', 'faker-review'); ?></h1>
                <p><?php esc_html_e('Generate realistic product reviews for testing purposes', 'faker-review'); ?></p>
            </div>
            
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('drg_generate_reviews', 'drg_nonce'); ?>

                <div class="drg-section">
                    <div class="drg-section-title">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php esc_html_e('Review Settings', 'faker-review'); ?>
                    </div>
                    <div class="drg-section-description">
                        <?php esc_html_e('Configure review generation settings. Maximum 15 reviews per product in lite version.', 'faker-review'); ?>
                    </div>
                    <div class="drg-form-group">
                        <label><?php esc_html_e('Number of Reviews', 'faker-review'); ?></label>
                        <div>
                            <input type="number" name="drg_max_reviews" min="1" max="15" value="5" required>
                            <span class="drg-help-text"><?php esc_html_e('Enter the number of reviews to generate (max 15)', 'faker-review'); ?></span>
                        </div>
                    </div>
                    <div class="drg-form-group">
                        <label><?php esc_html_e('Products', 'faker-review'); ?></label>
                        <div>
                            <select name="drg_products[]" multiple class="drg-select" style="min-height: 150px; width: 100%;">
                                <?php 
                                if (!empty($products)) {
                                    foreach ($products as $product) : 
                                        ?>
                                        <option value="<?php echo esc_attr($product->get_id()); ?>"><?php echo esc_html($product->get_name()); ?></option>
                                        <?php 
                                    endforeach;
                                } else {
                                    echo '<option disabled>No products found</option>';
                                }
                                ?>
                            </select>
                            <span class="drg-help-text"><?php esc_html_e('Select products to receive reviews (use Ctrl/Cmd for multiple)', 'faker-review'); ?></span>
                        </div>
                    </div>
                    <div class="drg-form-group">
                        <label><?php esc_html_e('Verified Purchase', 'faker-review'); ?></label>
                        <div>
                            <select name="drg_verified">
                                <option value="yes"><?php esc_html_e('All Verified', 'faker-review'); ?></option>
                                <option value="no"><?php esc_html_e('None Verified', 'faker-review'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="pro-feature">
                    <h4><?php esc_html_e('Premium Features', 'faker-review'); ?></h4>
                    <ul>
                        <li>✨ <?php esc_html_e('Generate up to 500 reviews per product', 'faker-review'); ?></li>
                        <li>✨ <?php esc_html_e('Custom rating distribution', 'faker-review'); ?></li>
                        <li>✨ <?php esc_html_e('Set date ranges for reviews', 'faker-review'); ?></li>
                        <li>✨ <?php esc_html_e('Category-based selection', 'faker-review'); ?></li>
                        <li>✨ <?php esc_html_e('Mixed verified/unverified reviews', 'faker-review'); ?></li>
                    </ul>
                    <p><a href="https://oxyian.com/faker-review-premium" target="_blank" class="button button-secondary"><?php esc_html_e('Upgrade to Premium', 'faker-review'); ?></a></p>
                </div>

                <div class="drg-submit">
                    <input type="submit" name="drg_submit" class="button button-primary" value="<?php esc_html_e('Generate Reviews', 'faker-review'); ?>">
                </div>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Safe wrapper for wp_rand
 */
function drg_safe_rand($min, $max) {
    if (function_exists('wp_rand')) {
        return wp_rand($min, $max);
    }
    return mt_rand($min, $max); // Fallback to mt_rand if wp_rand isn't available
}

/**
 * Safe wrapper for sanitization functions
 */
function drg_safe_sanitize($input) {
    if (function_exists('sanitize_text_field')) {
        return sanitize_text_field($input);
    }
    return strip_tags(trim($input));
}

/**
 * Safe wrapper for date functions
 */
function drg_safe_date($format, $timestamp = null) {
    if (function_exists('wp_date')) {
        return wp_date($format, $timestamp);
    }
    return gmdate($format, $timestamp ?? time());
}

/**
 * Get random number using WordPress cryptographic functions
 * 
 * @param int $min Minimum value
 * @param int $max Maximum value
 * @return int Random number
 */
function drg_secure_rand($min, $max) {
    try {
        $range = $max - $min + 1;
        $bytes = wp_rand($min, $max);
        return $bytes;
    } catch (Exception $e) {
        return wp_rand($min, $max);
    }
}

/**
 * Handle review generation
 */
function drg_handle_generation() {
    // Verify nonce
    if (!isset($_POST['drg_nonce']) || !wp_verify_nonce(sanitize_key($_POST['drg_nonce']), 'drg_generate_reviews')) {
        wp_die(esc_html__('Security check failed', 'faker-review'));
    }
    
    // Validate and sanitize input data
    $min_reviews = isset($_POST['drg_min_reviews']) ? absint(wp_unslash($_POST['drg_min_reviews'])) : 1;
    $max_reviews = isset($_POST['drg_max_reviews']) ? absint(wp_unslash($_POST['drg_max_reviews'])) : 40;
    $max_reviews = min($max_reviews, 500); // Upper limit for performance
    
    $weights = [
        isset($_POST['drg_5']) ? absint(wp_unslash($_POST['drg_5'])) : 70 => 5,
        isset($_POST['drg_4']) ? absint(wp_unslash($_POST['drg_4'])) : 20 => 4,
        isset($_POST['drg_3']) ? absint(wp_unslash($_POST['drg_3'])) : 5 => 3,
        isset($_POST['drg_2']) ? absint(wp_unslash($_POST['drg_2'])) : 3 => 2,
        isset($_POST['drg_1']) ? absint(wp_unslash($_POST['drg_1'])) : 2 => 1,
    ];
    
    $total_weight = array_sum(array_keys($weights));
    if ($total_weight < 95 || $total_weight > 105) {
        add_settings_error(
            'drg_messages',
            'drg_weights_error',
            sprintf(__('Rating distributions should add up to 100%% (current total: %d%%).', 'faker-review'), $total_weight),
            'error'
        );
        settings_errors('drg_messages');
        return;
    }

    $scope = isset($_POST['drg_scope']) ? sanitize_text_field(wp_unslash($_POST['drg_scope'])) : 'all';
    $selected_products = isset($_POST['drg_products']) ? array_map('absint', (array) $_POST['drg_products']) : [];
    $selected_categories = isset($_POST['drg_categories']) ? array_map('absint', (array) $_POST['drg_categories']) : [];

    // Get product IDs
    if ($scope === 'all') {
        $products = wc_get_products(['status' => 'publish', 'limit' => -1]);
        $product_ids = !empty($products) ? wp_list_pluck($products, 'id') : [];
    } elseif ($scope === 'category' && !empty($selected_categories)) {
        $product_ids = [];
        foreach ($selected_categories as $category_id) {
            $category_products = wc_get_products([
                'status' => 'publish',
                'limit' => -1,
                'category' => [get_term($category_id)->slug],
            ]);
            $product_ids = array_merge($product_ids, !empty($category_products) ? wp_list_pluck($category_products, 'id') : []);
        }
        $product_ids = array_unique($product_ids);
    } else {
        $product_ids = $selected_products;
    }
    
    if (empty($product_ids)) {
        echo '<div class="error notice is-dismissible"><p>' . 
            esc_html__('No products selected or available. Please select at least one product.', 'faker-review') . 
            '</p></div>';
        return;
    }

    // Load files with validation
    $reviews = drg_load_file_content('drg_review_file', FAKER_REVIEW_PLUGIN_DIR . '/reviews.txt');
    if (empty($reviews)) {
        echo '<div class="error notice is-dismissible"><p>' . 
            esc_html__('No review text available. Please upload a file or make sure the default file exists.', 'faker-review') . 
            '</p></div>';
        return;
    }
    
    $names = drg_load_file_content('drg_names_file', FAKER_REVIEW_PLUGIN_DIR . '/indian_names.txt');
    if (empty($names)) {
        echo '<div class="error notice is-dismissible"><p>' . 
            esc_html__('No names available. Please upload a file or make sure the default file exists.', 'faker-review') . 
            '</p></div>';
        return;
    }

    $verified_option = isset($_POST['drg_verified']) ? sanitize_text_field(wp_unslash($_POST['drg_verified'])) : 'mixed';
    $start_date = isset($_POST['drg_start_date']) ? sanitize_text_field(wp_unslash($_POST['drg_start_date'])) : gmdate('Y-m-d', strtotime('-30 days'));
    $end_date = isset($_POST['drg_end_date']) ? sanitize_text_field(wp_unslash($_POST['drg_end_date'])) : gmdate('Y-m-d');
    
    $total_generated = 0;
    
    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        if (!$product) continue;
        
        $num_reviews = drg_secure_rand($min_reviews, $max_reviews);
        
        $available_names = $names;
        $available_reviews = $reviews;
        shuffle($available_names);
        shuffle($available_reviews);

        for ($i = 0; $i < $num_reviews; $i++) {
            if (empty($available_names)) {
                $available_names = $names;
                shuffle($available_names);
            }
            
            if (empty($available_reviews)) {
                $available_reviews = $reviews;
                shuffle($available_reviews);
            }

            $author = array_pop($available_names);
            $review_text = array_pop($available_reviews);
            $rating = drg_weighted_rating($weights);
            
            $is_verified = 'no';
            if ($verified_option === 'yes') {
                $is_verified = 'yes';
            } elseif ($verified_option === 'mixed') {
                $is_verified = (drg_secure_rand(1, 100) <= 70) ? 'yes' : 'no';
            }

            $name_parts = explode(' ', $author);
            $first_name = sanitize_user(strtolower($name_parts[0]));
            $email_domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'example.com'];
            $random_domain = $email_domains[wp_rand(0, count($email_domains) - 1)];
            $random_number = drg_secure_rand(1, 999);
            $email = sanitize_email($first_name . $random_number . '@' . $random_domain);
            
            $review_date = drg_random_date($start_date, $end_date);
            
            $comment_data = [
                'comment_post_ID'      => $product_id,
                'comment_author'       => $author,
                'comment_author_email' => $email,
                'comment_author_url'   => '',
                'comment_content'      => $review_text,
                'comment_type'         => 'review',
                'comment_parent'       => 0,
                'user_id'             => 0,
                'comment_approved'     => 1,
                'comment_date'         => $review_date,
            ];

            $comment_id = wp_insert_comment($comment_data);
            
            if ($comment_id) {
                update_comment_meta($comment_id, 'rating', $rating);
                update_comment_meta($comment_id, 'verified', $is_verified);
                drg_update_product_rating($product_id);
                $total_generated++;
            }
        }
    }

    $used_names_file = !empty($_FILES['drg_names_file']['tmp_name']) ? 'uploaded file' : 'default indian_names.txt';
    $used_reviews_file = !empty($_FILES['drg_review_file']['tmp_name']) ? 'uploaded file' : 'default reviews.txt';
    
    echo '<div class="updated notice is-dismissible">
        <p>' . esc_html(sprintf(__('Successfully generated %d reviews!', 'faker-review'), $total_generated)) . '</p>
        <p>' . esc_html(sprintf(__('Used %1$s for names and %2$s for reviews.', 'faker-review'), $used_names_file, $used_reviews_file)) . '</p>
    </div>';
}

/**
 * Load content from a file
 * 
 * @param string $file_key File key in $_FILES
 * @param string $default_path Default file path if no upload
 * @return array Content array
 */
function drg_load_file_content($file_key, $default_path) {
    $content = [];
    
    // Try to load from uploaded file
    if (!empty($_FILES[$file_key]['tmp_name'])) {
        $uploaded = file($_FILES[$file_key]['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($uploaded) {
            $content = array_map('trim', $uploaded);
        }
    }
    
    // If no content from upload, try default file
    if (empty($content) && file_exists($default_path)) {
        $default_content = file($default_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($default_content) {
            // Remove the first line if it's a filepath comment
            if (isset($default_content[0]) && strpos($default_content[0], '// filepath:') === 0) {
                array_shift($default_content);
            }
            $content = array_map('trim', $default_content);
        }
    }
    
    return $content;
}

/**
 * Update product rating after reviews are added
 * 
 * @param int $product_id
 */
function drg_update_product_rating($product_id) {
    $product = wc_get_product($product_id);
    if (!$product) return;
    
    // This uses WooCommerce's built-in system to recalculate ratings
    WC_Comments::clear_transients($product_id);
}

/**
 * Generate a weighted rating based on percentages
 * 
 * @param array $weights Array of percentage => rating
 * @return int Rating (1-5)
 */
function drg_weighted_rating($weights) {
    $total = array_sum(array_keys($weights));
    if ($total <= 0) return 5; // Default to 5 stars if no weights
    
    $rand = wp_rand(1, min(100, $total));
    $acc = 0;
    
    foreach ($weights as $percent => $rating) {
        $acc += $percent;
        if ($rand <= $acc) return $rating;
    }
    
    return 5; // Default fallback
}

/**
 * Generate a random date between start and end dates
 * 
 * @param string $start_date Start date in Y-m-d format
 * @param string $end_date End date in Y-m-d format
 * @return string Date in Y-m-d H:i:s format
 */
function drg_random_date($start_date, $end_date) {
    $start_timestamp = strtotime($start_date . ' 00:00:00');
    $end_timestamp = strtotime($end_date . ' 23:59:59');
    
    if (!$start_timestamp || !$end_timestamp || $start_timestamp > $end_timestamp) {
        // Fallback to last 30 days if dates are invalid
        $end_timestamp = current_time('timestamp');
        $start_timestamp = strtotime('-30 days', $end_timestamp);
    }
    
    $random_timestamp = drg_secure_rand($start_timestamp, $end_timestamp);
    return gmdate('Y-m-d H:i:s', $random_timestamp);
}

/**
 * Add plugin action links
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'drg_add_action_links');
function drg_add_action_links($links) {
    $plugin_links = [
        '<a href="' . admin_url('tools.php?page=faker-generator') . '">' . __('Generate Reviews', 'faker-review') . '</a>',
    ];
    return array_merge($plugin_links, $links);
}

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, 'drg_activation');
function drg_activation() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        // Deactivate the plugin
        deactivate_plugins(plugin_basename(__FILE__));
        
        // Show an error message
        wp_die(__('Faker Review requires WooCommerce to be installed and active.', 'faker-review'), 'Plugin Activation Error', [
            'back_link' => true,
        ]);
    }
}
