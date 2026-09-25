=== SwiftSearch for Algolia ===
Contributors: loopstates
Tags: algolia, woocommerce search, instant search, autocomplete, facets
Requires at least: 5.8
Tested up to: 7.1
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fast, typo-tolerant search for WordPress and WooCommerce with direct Algolia DSN queries, facets, and merchandising.

== Description ==

**SwiftSearch for Algolia** connects your WordPress and WooCommerce search directly to Algolia's Distributed Search Network (DSN). By offloading search from standard WordPress database queries to Algolia's edge network, SwiftSearch delivers fast, typo-tolerant search results without straining your server resources.

Built by [Loopstates](https://loopstates.com), SwiftSearch is architected for direct connectivity. Search queries travel **directly from your visitors' browser to Algolia's nearest edge node** via the official, lightweight `algoliasearch/lite` library. Your WordPress server does not execute database queries during live searches, keeping page loads responsive even during traffic surges.

#### Why SwiftSearch for Algolia?
Standard WordPress search lacks typo tolerance, does not rank results by custom relevance, and can slow down database performance under high traffic. SwiftSearch connects your site directly to Algolia to provide an instant, reliable search experience for your visitors.

Whether you run a blog, a content site, or a WooCommerce store, SwiftSearch ensures your visitors find what they are looking for in real time.

**Official Documentation**: [https://docs.loopstates.com/swiftsearch-for-algolia/](https://docs.loopstates.com/swiftsearch-for-algolia/)

== Key Features ==

### Fast Search Results via Algolia DSN
Deliver search results in milliseconds. Because SwiftSearch connects visitors' browsers directly to Algolia's edge locations, search queries bypass WordPress entirely—delivering fast results while offloading search compute load from your hosting server.

### Instant Autocomplete and Search-As-You-Type
Deliver matching results the moment visitors start typing. The reactive search UI updates dynamically with each keystroke, featuring highlighted text matches, product thumbnails, WooCommerce prices, stock status badges, and taxonomy tags.

### Built-in WooCommerce Merchandising & Product Pinning
Take control of your search rankings to drive sales. The visual Merchandising dashboard allows store managers to manually "pin" specific products, posts, or pages to the top positions (#1, #2, #3) for designated search keywords—suited for promotional campaigns, featured items, and clearance inventory.

### Smart Typo Tolerance and Synonym Dictionaries
Never lose a customer to a simple spelling mistake. Algolia's typo tolerance automatically handles misspellings and pluralizations. Additionally, define one-way synonyms (e.g. `sneakers => shoes`) and multi-way synonym sets (e.g. `coat, jacket, outerwear`) directly in your WordPress dashboard and push them to Algolia with one click.

### Visual Facet Builder & Metadata Mappings
Empower shoppers to filter results dynamically without page reloads. Map WooCommerce product attributes (Size, Color, Brand), taxonomies (Categories, Tags), or custom metadata fields into Algolia using our visual drag-and-drop builder with live count updates.

### Automatic Virtual Replica Sorting
Algolia provides fast sorting via dedicated replica indices rather than runtime database queries. SwiftSearch automatically provisions and synchronizes virtual replica indices in the background for active sort criteria (Relevance, Price: Low to High, Price: High to Low, Newest First, Oldest First) without duplicating record quotas, allowing visitors to switch sort order instantly on the frontend.

### Real-Time Search Analytics & Zero-Result Insights
Understand user intent and discover unmet inventory demand. Our built-in analytics dashboard provides visual charts of popular search queries, volume trends, and zero-result searches stored locally in your WordPress database. Discover exactly what visitors searched for but could not find, giving you actionable data to expand your catalog or configure synonyms.

### Dedicated WooCommerce Shop & Catalog Mode
Easily replace your default WooCommerce Shop and product archive pages with a modern, high-performance search and filter catalog page. Features horizontal sort dropdowns, sticky facet sidebars with live category counts, pagination, and clean card styling that matches modern e-commerce standards.

### Background Indexing & Real-Time Updates
When you first connect, the background indexing engine processes your content in self-scheduling batches to prevent server script timeouts. Built with stateless HMAC verification, it handles loopback and firewall restrictions reliably. Once indexed, any new, edited, or deleted posts and products are automatically synchronized in real time.

### Native WP-CLI Command-Line Integration
For stores with large catalogs (10,000+ items) or servers with strict security firewalls that block background HTTP requests, SwiftSearch includes full WP-CLI terminal commands. Run bulk indexing (`wp swift-search-algolia index`), check connection status (`wp swift-search-algolia status`), and reset indices (`wp swift-search-algolia reset`) directly via SSH with custom `--batch-size`, `--offset`, and `--limit` flags.

### Shortcode & Theme Integration
Customize your search experience without writing code. Control accent colors, typography, card layouts, and display toggles (prices, thumbnails, excerpts, stock status) directly from the settings. Use the lightweight shortcode `[swift_search_algolia]` anywhere on your site, or automatically replace your theme's native search form with a single toggle.

### Features & Capabilities
* **Instant Autocomplete**: Displays matching products and posts the moment visitors start typing.
* **Faceted Navigation & Filters**: Create and manage multi-select sidebar filters for taxonomies and metadata.
* **Merchandising & Pinning**: Fix specific items to the top of results for a curated user experience.
* **Virtual Replica Sorting**: Multi-criteria sorting (Relevance, Price: Low to High, Price: High to Low, Newest First, Oldest First) without duplicate record quotas.
* **Synonym Sets**: Link equivalent search terms directly from your WordPress dashboard.
* **Custom Fields Mapping**: Search and filter by custom fields and post metadata.
* **Search Analytics**: Dashboard for tracking popular searches and identifying zero-result query gaps.
* **Background Indexing**: Sync engine handles items in self-scheduling batches without timing out.
* **Automated Sync**: Real-time indexing when you Save, Update, or Delete content.
* **WP-CLI Support**: Bulk sync, reset, and check connection status from your terminal via SSH.
* **CPT Support**: Index Posts, Pages, Products, and Custom Post Types.
* **No Coding Required**: Configure search layouts, color schemes, and settings visually.
* **Global UI Toggles**: Show or hide Thumbnails, Prices, and Excerpts globally.
* **WooCommerce Search**: Optimized for product titles, prices, stock status, and product imagery.
* **Translation Ready**: Fully localized gettext catalog (`swiftsearch-for-algolia.pot`).
* **Mobile Ready**: Responsive overlay and catalog layouts for phones, tablets, and desktops.
* **Full Search Replacement**: Replace default WordPress search sitewide with a single click.
* **Easy Placement**: Put custom search bars anywhere on your site using simple shortcodes.
* **Algolia Attribution Badge**: Compliant SVG "Search by Algolia" attribution badge with admin toggle.

== Installation ==

1. Upload `swiftsearch-for-algolia` to the `/wp-content/plugins/` directory, or install it directly via the WordPress Plugins dashboard (**Plugins > Add New**).
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **SwiftSearch** in your admin sidebar.
4. Follow our **8-Step Setup Wizard**:
   * **Step 1: Connect** - Enter your Algolia Application ID, Admin API Key, and Search-Only API Key.
   * **Step 2: Content** - Select which post types (Posts, Products, Pages, CPTs) to index and map custom metadata fields.
   * **Step 3: Relevance** - Configure attribute weights and define one-way or multi-way synonyms.
   * **Step 4: Search UI** - Configure instant autocomplete toggles, facets, and attribution badge.
   * **Step 5: Styling** - Customize accent colors, text colors, and border radii.
   * **Step 6: Analytics** - Review search volume trends and zero-result queries.
   * **Step 7: Pinning** - Merchandise high-converting products to the top of specific search queries.
   * **Step 8: Sync** - Push index settings, configure replicas, and run the initial bulk index to Algolia.

== Developer Customization ==

SwiftSearch provides JavaScript DOM CustomEvents and WordPress PHP filters for theme integrations and advanced customizations.

= JavaScript Event Hooks =
You can listen to these custom events on the document:

1. `swift-search-algolia:render-hit` - Dispatched for every individual card rendered in the results grid.
   * `event.detail.hit`: Raw Algolia hit record.
   * `event.detail.card`: DOM element of the rendered card.
   * `event.detail.section`: Section type (e.g. 'posts').

2. `swift-search-algolia:results-rendered` - Dispatched after the entire search results grid finishes rendering.
   * `event.detail.totalFound`: Total number of results found.
   * `event.detail.query`: Current search query.

3. `swift-search-algolia:before-search` - Dispatched immediately before queries are sent to Algolia.
   * `event.detail.query`: Current search query string.

= JavaScript Code Example =
`document.addEventListener('swift-search-algolia:render-hit', function(event) {
    const hitData     = event.detail.hit;
    const cardElement = event.detail.card;

    if (hitData.post_type === 'product' && hitData.custom_rating) {
        const badge = document.createElement('span');
        badge.className = 'custom-rating-badge';
        badge.innerText = '★ ' + hitData.custom_rating;
        cardElement.querySelector('.ss-card-content')?.appendChild(badge);
    }
});`

= WordPress PHP Filters =
Backend developers can customize data synchronization and index configurations using WordPress filters:

1. `swift_search_algolia_post_document` - Filters the structured document data before sending to Algolia. Use this to add custom meta fields, attributes, or formatted values.
   * Arguments: `$document` (array), `$post_id` (int), `$post` (WP_Post object).

2. `swift_search_algolia_should_index_post` - Filter returning a boolean. Return `false` to exclude specific posts or out-of-stock products from the search index.
   * Arguments: `$should_index` (bool), `$post_id` (int), `$post` (WP_Post object).

3. `swift_search_algolia_settings` - Filters compiled Algolia index settings (searchableAttributes, customRanking, etc.) before pushing to Algolia.
   * Arguments: `$settings` (array), `$config` (array), `$base_index_name` (string).

4. `swift_search_algolia_vars` - Filters configuration variables passed from PHP to frontend JavaScript.
   * Arguments: `$vars` (array).

= PHP Code Example =
`add_filter('swift_search_algolia_post_document', function($document, $post_id, $post) {
    // Add custom field or post metadata
    $brand = get_post_meta($post_id, 'brand_name', true);
    if (!empty($brand)) {
        $document['brand'] = $brand;
    }
    return $document;
}, 10, 3);

add_filter('swift_search_algolia_should_index_post', function($should_index, $post_id, $post) {
    if (get_post_meta($post_id, '_is_discontinued', true) === 'yes') {
        return false;
    }
    return $should_index;
}, 10, 3);`

== Privacy & Compliance ==

SwiftSearch is designed with a direct, privacy-conscious architecture:
* **Zero Middle-Layer Proxy**: Search queries go directly from your visitor's browser to your own Algolia application. No search traffic is routed through Loopstates servers.
* **Self-Contained Analytics**: Search logs and zero-result query tracking data are stored locally in your WordPress database.
* **GDPR Ready**: Visitors query Algolia's secure infrastructure directly under your own Algolia Data Processing Agreement (DPA).

== Third-Party Services ==

This plugin integrates with and connects directly to Algolia (https://www.algolia.com) to provide search indexing, instant search-as-you-type querying, and faceted filtering.

* Service Provider: Algolia, Inc.
* Service Website: https://www.algolia.com
* Terms of Service: https://www.algolia.com/policies/terms/
* Privacy Policy: https://www.algolia.com/policies/privacy/
* Data Transmitted: When you configure and run indexing, this plugin sends selected WordPress content (such as post titles, URLs, body content, taxonomies, and custom metadata) to your configured Algolia application. When visitors search on your site, search queries and filter selections travel directly from the visitor's browser to Algolia's edge network. No user search data is sent to or stored on Loopstates servers.

== Frequently Asked Questions ==

= Do I need an Algolia account? =
Yes. You need an Algolia account to host your search index. Algolia provides a Free (Build) plan that includes up to 10,000 search records and 10,000 search requests per month with no credit card required to get started.

= What are the Application ID, Admin API Key, and Search-Only API Key? =
You can find all three in your Algolia Dashboard under **Settings > API Keys**:
* **Application ID**: The unique identifier for your Algolia cluster.
* **Admin API Key**: Used securely on your server for batch indexing and index configuration. Never exposed to frontend visitors.
* **Search-Only API Key**: A restricted public key used by frontend visitors to execute searches securely.

= Does SwiftSearch work with WooCommerce? =
Absolutely. SwiftSearch is WooCommerce-native out of the box, indexing product titles, prices (with active WooCommerce currency symbol and position formatting), sale badges, SKUs, inventory status, categories, and attributes.

= How fast is the search? =
Because search queries are sent directly from the visitor's browser to Algolia's edge nodes, queries typically return in **15ms – 40ms**, eliminating the delay of traditional WordPress database searches.

= What is Result Pinning (Merchandising)? =
Result Pinning allows store owners to manually place specific products or posts at the very top of search results for a designated search term—ideal for promotional campaigns, featured items, or clearing seasonal inventory.

= How do Synonyms improve search? =
They allow you to link equivalent terms together. You can configure one-way synonyms (e.g. `sneakers => shoes`) or multi-way synonym sets (e.g. `coat, jacket, outerwear`). When a user searches for any linked term, Algolia returns relevant results automatically, preventing zero-result drop-offs.

= Does this plugin display the "Search by Algolia" logo? =
Yes. Algolia requires displaying a "Search by Algolia" attribution badge on free accounts. SwiftSearch provides a clean, modern SVG badge in the search footer, with an admin toggle available for paid Algolia accounts.

= How do I index large catalogs (10,000+ products)? =
For large catalogs or managed hosting providers with strict script timeouts, we recommend using WP-CLI:
`wp swift-search-algolia index --batch-size=500`

= Does it work with page builders and custom themes? =
Yes. You can place the `[swift_search_algolia]` shortcode into any page layout, or enable the "Override Default Search" toggle to automatically replace your theme's native search form.

= What insights does Search Analytics provide? =
Our built-in analytics dashboard tracks your most searched terms, keyword volume trends, and zero-result queries right inside your WordPress admin. Zero-result queries show you exactly what visitors searched for but could not find, giving you actionable data to expand inventory or configure synonyms.

== Screenshots ==

1. **Connection Settings**: Configure Algolia Application ID, Admin API Key, and Search-Only API Key.
2. **Content Settings**: Choose searchable post types and register custom field mappings.
3. **Relevance and Synonyms**: Manage attribute weighting sliders and define one-way or multi-way synonyms.
4. **Search UI Configuration**: Setup instant autocomplete toggles, facets sidebar, and item limits.
5. **Styling Customizer**: Visually customize colors, fonts, and border radii to match your theme.
6. **Search Analytics Dashboard**: Track search volume trends and flag zero-result queries.
7. **Merchandising & Pinning**: Pin selected products or posts to the top of search results.
8. **Sync Management**: Run bulk indexing processes and monitor real-time sync status logs.

== Changelog ==

= 1.0.1 =
* Fix: Defer recurring cron event scheduling to the init action to prevent early translation loading notices in WordPress 6.7+.

= 1.0.0 =
* Initial release of SwiftSearch for Algolia.
* Direct browser-to-Algolia query architecture via `algoliasearch/lite`.
* Automatic index settings generation (`searchableAttributes`, `attributesForFaceting`, `customRanking`).
* Sort support using Algolia Virtual Replicas (Relevance, Price: Low to High, Price: High to Low, Newest First, Oldest First).
* Built-in visual merchandising and client-side hit pinning.
* Support for one-way (`root => synonyms`) and multi-way (`syn1, syn2`) synonyms.
* Local search analytics dashboard with zero-result query tracking.
* Dedicated Catalog and Shop layout mode replacing WooCommerce default shop pages.
* WP-CLI integration (`wp swift-search-algolia index`, `wp swift-search-algolia status`, `wp swift-search-algolia reset`).
* Compliant SVG "Search by Algolia" attribution badge with admin toggle.
