=== Faker Review Lite === 
Contributors: oxyian
Tags: woocommerce, reviews, testing, fake reviews, development, dummy data, test data, product reviews
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generate simple fake reviews for WooCommerce products for testing purposes (up to 5 reviews per product, unverified only).

== Description ==

Faker Review Lite is a simple tool designed to generate realistic fake reviews for WooCommerce products. Perfect for development and testing environments, this plugin helps developers and store owners quickly add sample reviews to their products.

= Free Features =
* Generate up to 5 reviews per product
* Select up to 2 products at a time
* Unverified reviews only
* Balanced rating distribution (3-5 stars)
* Simple and intuitive interface

= Premium Features =
* Generate up to 500 reviews per product
* Custom rating distribution
* Set date ranges for reviews
* Select products by category
* Mixed verified/unverified reviews
* Custom review text and names upload
* Advanced review settings

= Perfect For =
* Development environments
* Theme testing
* Plugin compatibility testing
* Demo store setup 
* UI/UX testing with varied review counts
* Testing review sorting and filtering features
* Performance testing with large review datasets

= Custom Content Support =
* Upload your own review text file
* Add custom reviewer names
* Control review sentiment through rating distribution
* Set custom date ranges for historical data testing

= Important Note =
This plugin is strictly intended for testing and development purposes only. Using fake reviews on a live e-commerce store may violate terms of service and consumer trust. Always use this plugin in a development or testing environment.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/faker-review-lite` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Ensure that WooCommerce is installed and activated
4. Go to Tools > Faker Review to use the plugin

= System Requirements =
* WordPress 5.0 or higher
* WooCommerce 3.0 or higher
* PHP 7.2 or higher
* MySQL 5.6 or higher

== Frequently Asked Questions ==

= Can I use this on a production store? =

This plugin is intended for testing and development environments only. Using fake reviews on a live store could potentially violate terms of service for various platforms and diminish consumer trust. We strongly recommend using this plugin only in development or staging environments.

= Can I customize the review content? =

Yes, you can upload your own text file with custom review content. Each line in the file will be treated as a separate review. The file should be a plain text file (.txt) with one review per line. There's no limit to the length of each review, but we recommend keeping them realistic (between 50-500 characters).

= Can I customize reviewer names? =

Yes, you can upload your own text file with custom reviewer names. Each line in the file will be treated as a separate name. The plugin comes with a default set of realistic names, but you can override these with your own list. Names should be in "First Last" format for best results.

= What is the maximum number of reviews I can generate? =

For performance reasons, you can generate up to 500 reviews per batch. You can run multiple batches if you need more reviews. For large stores, we recommend generating reviews in smaller batches (100-200) to ensure optimal performance.

= Can I control the rating distribution? =

Yes, you can set the percentage distribution for each star rating (1-5 stars). The total distribution must add up to 100%. For example:
* 5 stars: 40%
* 4 stars: 30%
* 3 stars: 15%
* 2 stars: 10%
* 1 star: 5%

= Can I set specific dates for the reviews? =

Yes, you can set a date range for the generated reviews. The plugin will randomly distribute reviews within your specified date range. This is particularly useful for creating a realistic review history for products. You can set dates from the past up to the current date.

= Are the generated reviews marked as verified purchases? =

You have the option to control whether the generated reviews are marked as verified purchases or not. This setting can be toggled for each batch of reviews you generate, allowing you to create a mix of verified and unverified reviews.

= How are review author emails generated? =

The plugin generates fictitious email addresses that look realistic but are not real. These are randomly generated using common email providers (like @example.com) and the reviewer's name. You can also specify your own email domain for consistency.

= Can I delete the generated reviews later? =

Yes, you can delete the generated reviews just like any other WooCommerce product review through the WordPress admin interface. The plugin also tags all generated reviews internally, making them easy to identify and remove in bulk if needed.

= Will this plugin affect my site's performance? =

The plugin only runs when you explicitly generate reviews. It doesn't add any overhead to your site's normal operation. However, generating large numbers of reviews (400+) at once may temporarily impact server performance, so we recommend generating reviews in smaller batches.

= Does this work with all WooCommerce product types? =

Yes, the plugin works with all WooCommerce product types including:
* Simple products
* Variable products
* Grouped products
* External/Affiliate products
* Virtual products
* Downloadable products

= Can I generate reviews for specific products only? =

Yes, you can choose to generate reviews for all products or select specific products from your store. This is useful when you want to focus testing on particular products or create varying review counts across your store.

= How realistic are the generated reviews? =

The generated reviews are designed to be as realistic as possible, with:
* Natural-sounding content
* Proper grammar and punctuation
* Varied length and detail
* Realistic timestamps
* Proper name formatting
* Valid email structures

= Is there a way to export the generated reviews? =

Yes, all generated reviews can be exported using the standard WordPress comment export functionality. They are stored as standard WooCommerce product reviews in your database.

= Can I use this plugin in multiple environments? =

Yes, you can use the plugin in as many development or staging environments as needed. Just remember not to use it in production environments.

== Screenshots ==

1. Faker Review generator interface - The main control panel for generating reviews
2. Rating distribution settings - Configure the percentage of each star rating
3. Product selection and configuration - Choose specific products or generate store-wide
4. Date range selector - Set custom timeframes for review generation
5. Custom content upload interface - Upload your own review text and names
6. Batch generation progress - Monitor the review generation process

== Changelog ==

= 1.1 =
* Added date range selection for reviews
* Added verified purchase option
* Improved UI with tooltips and better organization
* Added email generation for reviewers
* Enhanced performance for bulk generation
* Added progress indicators for long operations
* Improved error handling and user feedback
* Added custom domain support for generated emails

= 1.0 =
* Initial release
* Basic review generation functionality
* Product selection feature
* Rating distribution controls
* Custom content upload support

== Upgrade Notice ==

= 1.1 =
New features including date range selection, verified purchase option, and improved UI. Adds email generation and better performance for bulk operations.
