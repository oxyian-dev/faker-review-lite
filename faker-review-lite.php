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
    ]);

    if (isset($_POST['generate_reviews'])) {
        // Limit product selection to 2
        if (isset($_POST['products']) && is_array($_POST['products']) && count($_POST['products']) > 2) {
            $_POST['products'] = array_slice($_POST['products'], 0, 2);
        }
        faker_review_lite_generate_reviews($_POST);
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Faker Review Lite', 'faker-review-lite'); ?></h1>
        <p><?php echo esc_html__('Generate up to 5 fake reviews for testing purposes.', 'faker-review-lite'); ?></p>

        <div class="notice notice-info" style="background: #f0f6fc; border-left-color: #2271b1; padding: 20px;">
            <h3 style="margin-top: 0; color: #2271b1;">
                <?php echo esc_html__('✨ Unlock Premium Features!', 'faker-review-lite'); ?>
            </h3>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0;">
                <div>
                    <h4 style="margin: 0; color: #1d2327;">
                        <span style="color: #2271b1;">⭐</span>
                        <?php echo esc_html__('Enhanced Review Generation', 'faker-review-lite'); ?>
                    </h4>
                    <ul style="margin: 10px 0;">
                        <li><?php echo esc_html__('Generate up to 500 reviews per product', 'faker-review-lite'); ?></li>
                        <li><?php echo esc_html__('Custom rating distribution control', 'faker-review-lite'); ?></li>
                        <li><?php echo esc_html__('Mixed verified/unverified reviews', 'faker-review-lite'); ?></li>
                    </ul>
                </div>

                <div>
                    <h4 style="margin: 0; color: #1d2327;">
                        <span style="color: #2271b1;">🎯</span>
                        <?php echo esc_html__('Advanced Targeting', 'faker-review-lite'); ?>
                    </h4>
                    <ul style="margin: 10px 0;">
                        <li><?php echo esc_html__('Category-based product selection', 'faker-review-lite'); ?></li>
                        <li><?php echo esc_html__('Custom date range for reviews', 'faker-review-lite'); ?></li>
                        <li><?php echo esc_html__('Bulk generation across categories', 'faker-review-lite'); ?></li>
                    </ul>
                </div>

                <div>
                    <h4 style="margin: 0; color: #1d2327;">
                        <span style="color: #2271b1;">📝</span>
                        <?php echo esc_html__('Content Customization', 'faker-review-lite'); ?>
                    </h4>
                    <ul style="margin: 10px 0;">
                        <li><?php echo esc_html__('Upload custom review templates', 'faker-review-lite'); ?></li>
                        <li><?php echo esc_html__('Custom reviewer names and emails', 'faker-review-lite'); ?></li>
                        <li><?php echo esc_html__('Advanced text formatting options', 'faker-review-lite'); ?></li>
                    </ul>
                </div>

                <div>
                    <h4 style="margin: 0; color: #1d2327;">
                        <span style="color: #2271b1;">⚙️</span>
                        <?php echo esc_html__('Professional Tools', 'faker-review-lite'); ?>
                    </h4>
                    <ul style="margin: 10px 0;">
                        <li><?php echo esc_html__('Batch processing for large stores', 'faker-review-lite'); ?></li>
                        <li><?php echo esc_html__('Review export/import features', 'faker-review-lite'); ?></li>
                        <li><?php echo esc_html__('Advanced scheduling options', 'faker-review-lite'); ?></li>
                    </ul>
                </div>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <p style="font-size: 15px; margin-bottom: 15px;">
                    <?php echo esc_html__('Take your testing to the next level with our Premium features!', 'faker-review'); ?>
                </p>
                <a href="https://oxyian.com/faker-review-premium" class="button button-primary button-hero" target="_blank"
                    style="font-size: 16px; padding: 8px 20px;">
                    <?php echo esc_html__('🌟 Upgrade to Premium', 'faker-review'); ?>
                </a>
            </div>
        </div>

        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php echo esc_html__('Select Products', 'faker-review'); ?></th>
                    <td>
                        <select name="products[]" multiple style="width: 100%; max-width: 400px; height: 200px;"
                            onchange="limitProductSelection(this);">
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo esc_attr($product->get_id()); ?>">
                                    <?php echo esc_html($product->get_name()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php echo esc_html__('Hold Ctrl/Cmd to select products (maximum 2 products in Lite version)', 'faker-review-lite'); ?>
                        </p>
                        <p class="description" style="color: #2271b1;">
                            <?php echo esc_html__('Want to generate reviews for more products? Upgrade to Premium!', 'faker-review'); ?>
                        </p>
                        <script>
                            function limitProductSelection(select) {
                                if (select.selectedOptions.length > 2) {
                                    alert('<?php echo esc_js(__('Lite version is limited to 2 products. Upgrade to Premium for unlimited product selection!', 'faker-review')); ?>');
                                    // Keep only the first 2 selected options
                                    for (let i = 2; i < select.options.length; i++) {
                                        select.options[i].selected = false;
                                    }
                                }
                            }
                        </script>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Reviews per Product', 'faker-review'); ?></th>
                    <td>
                        <input type="number" name="reviews_count" min="1" max="5" value="5" required>
                        <p class="description">
                            <?php echo esc_html__('Maximum 5 reviews per product in Lite version', 'faker-review-lite'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Review Status', 'faker-review'); ?></th>
                    <td>
                        <p class="description">
                            <?php echo esc_html__('All reviews will be unverified in Lite version', 'faker-review'); ?>
                        </p>
                        <input type="hidden" name="verified_status" value="unverified">
                    </td>
                </tr>
            </table>
            <?php wp_nonce_field('faker_review_lite_generate', 'faker_review_lite_nonce'); ?>
            <p class="submit">
                <input type="submit" name="generate_reviews" class="button button-primary"
                    value="<?php echo esc_attr__('Generate Reviews', 'faker-review'); ?>">
            </p>
        </form>
    </div>
    <?php
}

// Generate reviews
function faker_review_lite_generate_reviews($data)
{
    if (
        !isset($data['faker_review_lite_nonce']) ||
        !wp_verify_nonce($data['faker_review_lite_nonce'], 'faker_review_lite_generate')
    ) {
        add_settings_error(
            'faker_review_lite',
            'security_check_failed',
            __('Security check failed', 'faker-review-lite'),
            'error'
        );
        return;
    }

    $products = isset($data['products']) ? array_map('absint', (array) $data['products']) : [];
    $count = isset($data['reviews_count']) ? absint($data['reviews_count']) : 5;
    $count = min($count, 5); // Enforce the 5 review limit
    $verified_status = 'unverified'; // Force unverified reviews only

    if (empty($products)) {
        add_settings_error(
            'faker_review_lite',
            'no_products',
            __('No products selected. Please select at least one product.', 'faker-review-lite'),
            'error'
        );
        return;
    }

    $names_file = FAKER_REVIEW_LITE_PLUGIN_DIR . 'indian_names.txt';
    $reviews_file = FAKER_REVIEW_LITE_PLUGIN_DIR . 'reviews.txt';

    if (!file_exists($reviews_file)) {
        add_settings_error(
            'faker_review_lite',
            'no-reviews',
            __('Reviews file not found.', 'faker-review')
        );
        return;
    }

    if (!file_exists($names_file)) {
        add_settings_error(
            'faker_review_lite',
            'no-names',
            __('Names file not found.', 'faker-review')
        );
        return;
    }

    $names = file($names_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $reviews = file($reviews_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $total_generated = 0;

    foreach ($products as $product_id) {
        for ($i = 0; $i < $count; $i++) {
            $name = $names[array_rand($names)];
            $review_text = $reviews[array_rand($reviews)];
            $rating = mt_rand(3, 5); // Balanced rating distribution (3-5 stars only)
            $verified = $verified_status === 'all' ? (mt_rand(0, 1) === 1) : ($verified_status === 'verified');

            $email = strtolower(str_replace(' ', '.', $name)) . mt_rand(1, 999) . '@example.com';

            $comment_data = array(
                'comment_post_ID' => $product_id,
                'comment_author' => $name,
                'comment_author_email' => $email,
                'comment_author_url' => '',
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
                add_comment_meta($comment_id, 'verified', $verified);
                add_comment_meta($comment_id, 'faker_review_lite', true);
                $total_generated++;
            }
        }
    }

    add_settings_error(
        'faker_review_lite',
        'reviews-generated',
        sprintf(
            __('Successfully generated %d reviews! 🎉', 'faker-review'),
            $total_generated
        ),
        'success'
    );
}

// Add settings link on plugin page
function faker_review_lite_add_settings_link($links)
{
    $settings_link = '<a href="tools.php?page=faker-review-lite">' .
        __('Generate Reviews', 'faker-review') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Load text domain for translations    
function faker_review_lite_load_textdomain()
{
    load_plugin_textdomain('faker-review-lite', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'faker_review_lite_load_textdomain');