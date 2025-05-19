<?php
/**
 * Plugin Name: Faker Review Lite
 * Plugin URI: https://oxyian.com/
 * Description: Generate simple fake reviews for WooCommerce products for testing purposes (up to 5 reviews per product, unverified only)
 * Version: 1.1
 * Author: OXYIAN
 * Author URI: https://oxyian.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: faker-review-lite
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 */

// If this file is called directly, abort
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('FAKER_REVIEW_LITE_VERSION', '1.1');

function faker_review_lite_init()
{
    // Check if WooCommerce is active
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        add_action('admin_notices', 'faker_review_lite_wc_missing_notice');
        return;
    }

    // Plugin constants that need WordPress functions
    if (!defined('FAKER_REVIEW_LITE_PLUGIN_DIR')) {
        define('FAKER_REVIEW_LITE_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('FAKER_REVIEW_LITE_PLUGIN_URL', plugin_dir_url(__FILE__));
    }

    // Add menu item
    add_action('admin_menu', 'faker_review_lite_add_menu');

    // Add settings link
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'faker_review_lite_add_settings_link');
}
add_action('init', 'faker_review_lite_init');

// Show notice if WooCommerce is missing
function faker_review_lite_wc_missing_notice()
{
    echo '<div class="error"><p>' .
        esc_html__('Faker Review Lite requires WooCommerce to be installed and active.', 'faker-review-lite') .
        '</p></div>';
}

// Add admin menu
function faker_review_lite_add_menu()
{
    add_management_page(
        __('Faker Review Lite', 'faker-review-lite'),
        __('Faker Review Lite', 'faker-review-lite'),
        'manage_options',
        'faker-review-lite',
        'faker_review_lite_admin_page'
    );
}

// Admin page callback
function faker_review_lite_admin_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $products = wc_get_products([
        'status' => 'publish',
        'limit' => -1,
        'return' => 'objects', // Ensure we get product objects
    ]);

    if (isset($_POST['generate_reviews'])) {
        check_admin_referer('faker_review_lite_generate', 'faker_review_lite_nonce');

        // Sanitize inputs
        $products = isset($_POST['products']) ? array_map('absint', (array) $_POST['products']) : [];
        $reviews_count = isset($_POST['reviews_count']) ? absint($_POST['reviews_count']) : 5;

        // Limit product selection to 2
        if (count($products) > 2) {
            $products = array_slice($products, 0, 2);
        }

        faker_review_lite_generate_reviews($products, $reviews_count);
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Faker Review Lite', 'faker-review-lite'); ?></h1>
        <p><?php echo esc_html__('Generate up to 5 fake reviews for testing purposes.', 'faker-review-lite'); ?></p>

        <form method="post" action="">
            <?php wp_nonce_field('faker_review_lite_generate', 'faker_review_lite_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php echo esc_html__('Select Products', 'faker-review-lite'); ?></th>
                    <td>
                        <select name="products[]" multiple style="width: 100%; max-width: 400px; height: 200px;">
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo esc_attr($product->get_id()); ?>">
                                    <?php echo esc_html($product->get_name()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php echo esc_html__('Hold Ctrl/Cmd to select products (maximum 2 products in Lite version)', 'faker-review-lite'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Reviews per Product', 'faker-review-lite'); ?></th>
                    <td>
                        <input type="number" name="reviews_count" min="1" max="5" value="5" required>
                        <p class="description">
                            <?php echo esc_html__('Maximum 5 reviews per product in Lite version', 'faker-review-lite'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="generate_reviews" class="button button-primary"
                    value="<?php echo esc_attr__('Generate Reviews', 'faker-review-lite'); ?>">
            </p>
        </form>
    </div>
    <?php
}

// Generate reviews
function faker_review_lite_generate_reviews($products, $count)
{
    $names_file = FAKER_REVIEW_LITE_PLUGIN_DIR . 'indian_names.txt';
    $reviews_file = FAKER_REVIEW_LITE_PLUGIN_DIR . 'reviews.txt';

    if (!file_exists($reviews_file) || !file_exists($names_file)) {
        add_settings_error(
            'faker_review_lite',
            'file_missing',
            __('Required files are missing.', 'faker-review-lite'),
            'error'
        );
        return;
    }

    $names = file($names_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $reviews = file($reviews_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $total_generated = 0;

    foreach ($products as $product_id) {
        for ($i = 0; $i < $count; $i++) {
            $name = sanitize_text_field($names[array_rand($names)]);
            $review_text = sanitize_textarea_field($reviews[array_rand($reviews)]);
            $rating = mt_rand(3, 5);

            $email = sanitize_email(strtolower(str_replace(' ', '.', $name)) . mt_rand(1, 999) . '@example.com');

            $comment_data = array(
                'comment_post_ID' => $product_id,
                'comment_author' => $name,
                'comment_author_email' => $email,
                'comment_content' => $review_text,
                'comment_type' => 'review',
                'comment_parent' => 0,
                'user_id' => 0,
                'comment_approved' => 1,
                'comment_date' => current_time('mysql')
            );

            $comment_id = wp_insert_comment($comment_data);

            if ($comment_id) {
                add_comment_meta($comment_id, 'rating', $rating);
                add_comment_meta($comment_id, 'faker_review_lite', true);
                $total_generated++;
            }
        }
    }

    add_settings_error(
        'faker_review_lite',
        'reviews_generated',
        sprintf(
            __('Successfully generated %d reviews!', 'faker-review-lite'),
            $total_generated
        ),
        'success'
    );
}

// Add settings link on plugin page
function faker_review_lite_add_settings_link($links)
{
    $settings_link = '<a href="tools.php?page=faker-review-lite">' .
        __('Generate Reviews', 'faker-review-lite') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Load text domain for translations
function faker_review_lite_load_textdomain()
{
    load_plugin_textdomain('faker-review-lite', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'faker_review_lite_load_textdomain');