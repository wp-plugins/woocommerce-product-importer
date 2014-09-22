=== WooCommerce - Product Importer ===

Contributors: visser
Donate link: http://www.visser.com.au/#donations
Tags: e-commerce, shop, cart, woocommerce, product importer, category importer, tag importer, csv
Requires at least: 2.9.2
Tested up to: 4.0
Stable tag: 1.2

== Description ==

Import new Products into your WooCommerce store from simple formatted files (e.g. CSV, TXT, etc.).

See http://www.visser.com.au/woocommerce/plugins/product-importer/

* Import new Products
* Delete existing Products by SKU
* Update existing Products (*)
* Import Product Images (*)
* Import Product Categories
* Import Product Tags
* Import from CSV file
* Supports external CRON commands (*)

Supported Product fields include:

* SKU
* Product Name
* Description
* Excerpt
* Price
* Sale Price
* Weight
* Length
* Width
* Height
* Category
* Tag
* Quantity
* Sort Order
* Product Status
* Comment Status
* Tax Status

Additional import features will be introduced in regular major Plugin updates, minor Plugin updates will address import issues and compatibility with new WooCommerce releases.

Features unlocked in the Pro upgrade of Product Importer include:

* Sale Price Dates From
* Sale Price Dates To
* Permalink
* Images
* Featured Image
* Product Gallery
* Post Date
* Post Modified
* Type
* Visibility
* Featured
* Tax Status
* Tax Class
* Manage Stock
* Stock Status
* Allow Backorders
* Sold Individually
* Up-sells
* Cross-sells
* File Download
* Download Limit
* Product URL
* Button Text
* Purchase Note
* Import All in One SEO Pack (AIOSEOP)
* Import Advanced Google Product Feed
* Import Ultimate SEO
* Import WordPress SEO

... and more free and Premium extensions for WooCommerce.

For more information visit: http://www.visser.com.au/woocommerce/

== Installation ==

1. Upload the folder 'woocommerce-product-importer' to the '/wp-content/plugins/' directory
2. Activate 'WooCommerce - Product Importer' through the 'Plugins' menu in WordPress
3. You can now import Products by reading the below Usage section

== Usage ==

1. Open WooCommerce > Product Importer
2. Using the file upload field select your CSV-formatted Product Catalog
3. Set the matching import field beside each corresponding column in your uploaded CSV file, this is usually selected automatically
4. Click Import Products
5. Review the Import Log updated during the live import
6. Press Finish Import
7. Review any Products that were skipped during the import
8. You can now manage Products within WooCommerce

Done!

== Screenshots ==

1. From the Import screen you can upload CSV files for import
3. The Settings screen includes options to alter the default formatting and behaviour of import files
3. Link CSV columns to WooCommerce Product fields and review the Import Options
4. Watch as new Products are populated within your WooCommerce store
5. A final review report includes skipped Products with reasons and a detailed import log for re-import

== Support ==

If you have any problems, questions or suggestions please raise a support topic on our dedicated WooCommerce support forum.

http://www.visser.com.au/woocommerce/forums/

== Changelog ==

= 1.2 =
* Added: WooCommerce Branding support
* Added: Tools screen
* Added: Skip import log if generating more than 1000 Categories
* Added: Skip import log if generating more than 1000 Product Tags
* Changed: UI of the Import Options meta box on the Import screen
* Changed: Moved Product related functions to products.php
* Added: Pause and resume support to import engine
* Fixed: Display Price in final import report of skipped Products
* Added: Additional diagnostic/server notices on Import screen

= 1.1 =
* Added: Alias support for Product import columns
* Added: Delete matched Products import method

= 1.0 =
* Initial release of Plugin
* Added: Import new Products import method

== Disclaimer ==

It is not responsible for any harm or wrong doing this Plugin may cause. Users are fully responsible for their own use. This Plugin is to be used WITHOUT warranty.