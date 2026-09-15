=== SwiftSearch for Algolia ===
Contributors: loopstates
Tags: algolia, woocommerce search, instant search, autocomplete, facets
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightning-Fast, typo-tolerant search for WordPress, WooCommerce, CPTs, and taxonomies with direct Algolia DSN queries, facets, and merchandising.

== Description ==

**SwiftSearch for Algolia** is an enterprise-grade search engine replacement for WordPress and WooCommerce. By replacing standard, resource-intensive WordPress MySQL search queries with Algolia's world-class Distributed Search Network (DSN), SwiftSearch delivers an instant, typo-tolerant search experience that loads in milliseconds.

Built by [Loopstates](https://loopstates.com), SwiftSearch is engineered around a modern **hybrid client-side architecture**. Search queries travel **directly from your visitors' browser to Algolia's nearest edge node** via the official, lightweight `algoliasearch/lite` library. Your WordPress server never executes a single database query during searches, no heavy PHP processes are spawned, and visitor traffic surges have zero impact on your hosting resources.

#### Why SwiftSearch for Algolia?
Native WordPress search is fundamentally limited: it cannot handle typos, ignores keyword relevance, and runs sluggish MySQL `LIKE '%query%'` queries that choke your database during peak traffic. Meanwhile, legacy Algolia plugins on WordPress are often abandoned, require complex developer coding, inject hundreds of kilobytes of rigid InstantSearch.js templates that break your theme, or lock essential e-commerce capabilities behind expensive monthly subscriptions.

SwiftSearch solves this by putting an unlocked, full-featured search powerhouse right inside your WordPress admin:

* **Direct-to-DSN Edge Queries**: Sub-40ms search responses routed to Algolia's closest global edge data center, bypassing WordPress PHP entirely.
* **100% Unlocked Core Power**: Visual facet builder, custom attribute weighting, one-way and multi-way synonyms, visual merchandising (pinning), and local search analytics are completely free and unlocked.
* **Algolia 10KB Free Tier Guardrail**: Automatic HTML stripping and smart content truncation prevent indexing rejections, keeping your records strictly within Algolia's 10,000-byte free tier threshold while preserving all vital metadata.
* **Automatic Virtual Replica Sorting**: Generates and syncs virtual replica indices for instant multi-criteria sorting (Price: Low to High, Price: High to Low, Date: Newest) without touching your database.
* **Self-Hosted Search Analytics**: Search volume trends and zero-result query tracking stored directly in your WordPress database for actionable insights with zero third-party tracking.
* **WooCommerce Native**: Automatically indexes prices, active currency formatting, sale status, inventory badges, and product attributes.
* **Enterprise Background Sync & WP-CLI**: Self-scheduling background queue with HMAC verification to bypass host loopback firewalls (WP Engine, Kinsta), plus full WP-CLI terminal commands for 50,000+ SKU catalogs.

Whether you manage a high-traffic content publication, a large WooCommerce store, or an enterprise directory, SwiftSearch ensures your visitors find exactly what they are looking for the moment they press their first keystroke.

**Official Documentation**: [https://docs.loopstates.com/swiftsearch-for-algolia/](https://docs.loopstates.com/swiftsearch-for-algolia/)

== Key Features ==

### Sub-Millisecond Search Results via Algolia DSN
Deliver search results in 15ms–40ms. Because SwiftSearch connects visitors' browsers directly to Algolia's 70+ global edge locations, search queries bypass WordPress and MySQL entirely—yielding instantaneous search results while offloading 100% of search compute load from your hosting server.

### Instant Autocomplete and Search-As-You-Type
Deliver matching results the moment visitors start typing. The reactive search UI updates dynamically with each keystroke, featuring highlighted text matches, high-resolution product thumbnails, dynamic WooCommerce prices, stock status badges, and taxonomy tags.

### Built-in WooCommerce Merchandising & Product Pinning
Take complete control of your search rankings to drive sales and maximize conversions. The visual Merchandising dashboard allows store managers to manually "pin" specific products, posts, or pages to the top positions (#1, #2, #3) for designated search keywords—ideal for seasonal promotions, brand campaigns, and clearance inventory.

### Smart Typo Tolerance and Synonym Dictionaries
Never lose a customer to a simple spelling mistake. Algolia's industry-leading typo tolerance automatically handles misspellings, letter transpositions, and pluralizations. Additionally, define one-way synonyms (e.g. `sneakers => shoes`) and multi-way synonym sets (e.g. `coat, jacket, outerwear`) directly in your WordPress dashboard and push them to Algolia with one click.

### Visual Facet Builder & Metadata Mappings
Empower shoppers to filter results dynamically without page reloads. Map any WooCommerce product attribute (Size, Color, Brand), standard taxonomy (Categories, Tags), or custom metadata field (ACF, Pods, Meta Box) into Algolia `attributesForFaceting` using our visual drag-and-drop builder with live count updates.

### Automatic Replica Indices for Instant Sorting
Algolia achieves sub-millisecond sorting via dedicated replica indices rather than runtime database queries. SwiftSearch automatically provisions and synchronizes virtual replica indices for active sort criteria (Price: Low to High, Price: High to Low, Date: Newest First) and switches indices seamlessly on the frontend without page refreshes.

### Engineered for Algolia's Free Tier (10KB Record Limit Guardrail)
The most common issue when syncing WordPress to Algolia is the strict 10,000-byte record size limit on Algolia's Free (Build) tier. SwiftSearch includes an automated content optimization engine that strips HTML tags, removes shortcodes, and intelligently truncates long body content to ~6,000 characters while preserving 100% of your crucial taxonomies, prices, and custom metadata fields.

### Real-Time Search Analytics & Zero-Result Insights
Understand user intent and discover unmet inventory demand. Our built-in analytics dashboard provides visual charts of popular search queries, volume trends, and zero-result searches stored locally in your WordPress database. Discover exactly what visitors searched for but could not find, giving you actionable data to expand your catalog or configure synonyms.

### Dedicated WooCommerce Shop & Catalog Mode
Easily replace your default WooCommerce Shop and product archive pages with a modern, high-performance search and filter catalog page. Features horizontal sort dropdowns, sticky facet sidebars with live category counts, pagination, and clean flat card styling that matches modern e-commerce standards.

### Enterprise Background Sync & Real-Time Updates
When you first connect, our self-scheduling background indexing engine processes your content in chunked batches to prevent server script timeouts. Built with stateless HMAC verification, it effortlessly bypasses loopback and cURL restrictions on cached hosts (such as WP Engine and Kinsta). Once indexed, any new, edited, or deleted posts and products are automatically synchronized in real time.

### Native WP-CLI Command-Line Integration
For stores with massive catalogs (10,000 to 100,000+ items) or servers with strict security firewalls that block background HTTP loopbacks, SwiftSearch includes full WP-CLI terminal commands. Run bulk indexing (`wp swift-search-algolia index`), check connection status (`wp swift-search-algolia status`), and reset indices (`wp swift-search-algolia reset`) directly via SSH with custom `--batch-size`, `--offset`, and `--limit` flags.

### Page Builder & Shortcode Flexibility
Customize your search experience without writing code. Control accent colors, typography, card layouts, and toggles (show/hide prices, thumbnails, excerpts, and stock) directly from the settings. It works with Elementor, Divi, Beaver Builder, Gutenberg, or any custom theme via the lightweight shortcode `[swift_search_algolia]`, or automatically overrides your theme's native search form with a single toggle.

### Complete Technical Capabilities List
* **Direct Edge Routing**: Instant queries directly to Algolia DSN edge nodes bypassing WordPress PHP.
* **Search-As-You-Type Autocomplete**: Reactive results modal with dynamic keyword highlighting.
* **Visual Facet Builder**: Multi-select sidebar filters for WooCommerce attributes and custom metadata.
* **Visual Merchandising (Pinning)**: Pin promoted products to top positions per keyword query.
* **Searchable Attribute Weights**: Fine-tune ranking priority across titles, excerpts, content, and metadata.
* **One-Way & Multi-Way Synonyms**: Integrated synonyms dashboard synced directly to Algolia.
* **Automatic Virtual Replica Sorting**: Instant sorting (Price ASC/DESC, Date) without SQL queries.
* **10KB Record Limit Guardrail**: Smart content truncation protecting Algolia Free tier accounts.
* **Self-Hosted Search Analytics**: Local tracking of search trends and zero-result queries in WordPress DB.
* **Zero Third-Party Tracking**: No user search logs shared with outside analytics services (GDPR-ready).
* **Dedicated Shop & Catalog Mode**: Replaces default WooCommerce shop pages with instant-filter layout.
* **WooCommerce Native**: Supports dynamic prices with active store currency formatting and sale badges.
* **Custom Post Type (CPT) Support**: Index Posts, Pages, Products, Portfolios, and custom taxonomies.
* **Advanced Custom Fields (ACF) Integration**: Search and filter by custom post metadata fields.
* **Background Batch Sync**: Self-scheduling engine handles large catalogs without timing out.
* **Automated Lifecycle Sync**: Index updates triggered on publish, update, trash, and restore.
* **WP-CLI Terminal Tooling**: Bulk sync, reset, and check status via SSH with `--batch-size`, `--offset`, `--limit`.
* **Page Builder Ready**: Integrates with Elementor, Divi, Beaver Builder, and Gutenberg blocks.
* **No Coding Required**: Configure layouts, color schemes, and search behaviors visually.
* **Global UI Toggles**: Show or hide thumbnails, prices, stock status, and excerpts globally.
* **Mobile & Touch Optimized**: Fully responsive overlay and catalog layouts for phones and tablets.
* **Algolia ToS Compliance**: Includes compliant SVG "Search by Algolia" attribution badge with admin toggle.
* **Developer Extensible**: Built with standard JavaScript CustomEvents and WordPress PHP filters.
* **Conflict-Free Isolation**: Completely isolated namespace, database tables, and scoped CSS/JS wrappers.
* **Translation Ready**: Fully localized gettext catalog (`swiftsearch-for-algolia.pot`).

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

SwiftSearch is built with extensible JavaScript custom event dispatching and standard WordPress PHP filter hooks for deep theme integration, custom analytics, wishlists, or headless architectures.

= JavaScript Event Hooks =
Listen to custom lifecycle events on the document or wrapper element:

1. `swift-search-algolia:render-hit` - Dispatched for every individual hit/card element that is rendered.
   * `event.detail.hit`: The raw Algolia hit record.
   * `event.detail.card`: The DOM element node of the rendered card.
   * `event.detail.section`: The section type (e.g. `posts`, `terms`, `users`).

2. `swift-search-algolia:results-rendered` - Dispatched after the entire search grid and all sections have completed rendering.
   * `event.detail.response`: The raw search results payload returned from Algolia.
   * `event.detail.totalFound`: Total matching hits count.

3. `swift-search-algolia:before-search` - Dispatched immediately before queries are sent to Algolia.
   * `event.detail.query`: Current search query string.
   * `event.detail.queries`: Array of query objects prepared for the Algolia client.

= Code Example: JavaScript Customization =
`document.addEventListener('swift-search-algolia:render-hit', function(event) {
    const hit  = event.detail.hit;
    const card = event.detail.card;

    if (hit.post_type === 'product') {
        const rating = hit.custom_rating || '';
        if (rating) {
            const badge = document.createElement('span');
            badge.className = 'custom-rating-badge';
            badge.innerText = '★ ' + rating;
            card.querySelector('.ss-card-content').appendChild(badge);
        }
    }
});`

= WordPress PHP Filters =
Backend developers can customize data payloads, adjust indexing conditions, and modify search parameters using standard WordPress filters:

1. `swift_search_algolia_post_document` - Filters the structured document array before it is sent to Algolia. Use this to add custom ACF fields, dynamic inventory rules, or calculated metadata:
`add_filter('swift_search_algolia_post_document', function($document, $post_id, $post) {
    // 1. Fetch Advanced Custom Fields (ACF)
    if (function_exists('get_field')) {
        $brand = get_field('brand_name', $post_id);
        if ($brand) {
            $document['brand'] = $brand;
        }
    }

    // 2. Strip shortcodes and HTML from WYSIWYG fields
    $raw_wysiwyg = get_post_meta($post_id, '_custom_product_highlights', true);
    if (!empty($raw_wysiwyg)) {
        $document['product_highlights'] = wp_strip_all_tags(strip_shortcodes($raw_wysiwyg));
    }

    // 3. Add custom sorting or calculated sales scores
    $document['sales_velocity'] = (int) get_post_meta($post_id, '_total_sales_30d', true);

    return $document;
}, 10, 3);`

2. `swift_search_algolia_should_index_post` - Return `false` to exclude specific posts, password-protected content, or out-of-stock products:
`add_filter('swift_search_algolia_should_index_post', function($should_index, $post_id, $post) {
    if (get_post_meta($post_id, '_is_discontinued', true) === 'yes') {
        return false;
    }
    return $should_index;
}, 10, 3);`

3. `swift_search_algolia_truncate_content` - Adjust the maximum character length for body content to stay within Algolia's 10KB record threshold:
`add_filter('swift_search_algolia_truncate_content', function($content, $max_length, $post_id) {
    return mb_substr($content, 0, 5000);
}, 10, 3);`

4. `swift_search_algolia_settings` - Customize the compiled Algolia index settings array before pushing to Algolia API:
`add_filter('swift_search_algolia_settings', function($settings, $config, $base_index_name) {
    $settings['separatorsToIndex'] = '-_';
    return $settings;
}, 10, 3);`

== Privacy & Compliance ==

SwiftSearch is designed with a direct, privacy-conscious architecture:
* **Zero Middle-Layer Proxy**: Search queries go directly from your visitor's browser to your own Algolia application. No search traffic is routed through Loopstates servers.
* **Self-Contained Analytics**: Search logs and zero-result query tracking data are stored locally in your WordPress database.
* **GDPR Ready**: Visitors query Algolia's secure infrastructure directly under your own Algolia Data Processing Agreement (DPA).

== Known Limitations & Best Practices ==

1. **Search-Only Keys on Frontend**: For public-facing sites, always use a "Search-Only" API Key for frontend visitors. SwiftSearch keeps your Admin API Key confidential on your server for background indexing.
2. **10KB Record Limit**: Algolia's Free tier enforces a 10KB limit per record. SwiftSearch automatically strips HTML tags and truncates long body content to ~6,000 characters while preserving all custom metadata and facet fields.
3. **Replica Indices Count**: Algolia limits total index count per account tier. SwiftSearch only creates replica indices for the specific sorting options enabled in your admin settings.

== Frequently Asked Questions ==

= Do I need an Algolia account? =
Yes. You need an Algolia account to host your search index. Algolia provides a Free (Build) plan that includes up to 10,000 search records and 10,000 search requests per month with no credit card required to get started.

= How does SwiftSearch compare to legacy Algolia plugins? =
Unlike legacy plugins that rely on heavy 300KB+ InstantSearch.js templates or require manual PHP templating, SwiftSearch uses a modern hybrid client: official lightweight network transport (`algoliasearch/lite`) combined with a bespoke, zero-bloat UI engine. It includes a visual facet builder, visual product pinning, synonym manager, and local analytics out of the box with zero coding required.

= What are the Application ID, Admin API Key, and Search-Only API Key? =
You can find all three in your Algolia Dashboard under **Settings > API Keys**:
* **Application ID**: The unique identifier for your Algolia cluster.
* **Admin API Key**: Used securely on your server for batch indexing and index configuration. Never exposed to frontend visitors.
* **Search-Only API Key**: A restricted public key used by frontend visitors to execute searches securely.

= Does SwiftSearch work with WooCommerce? =
Absolutely. SwiftSearch is WooCommerce-native out of the box, indexing product titles, prices (with active WooCommerce currency symbol and position formatting), sale badges, SKUs, inventory status, categories, and attributes.

= How fast is the search? =
Because of our hybrid architecture, search queries are sent directly from the visitor's browser to Algolia's Distributed Search Network (DSN) edge nodes. Queries typically return in **15ms – 40ms**, completely eliminating the 500ms–1500ms delay of traditional WordPress MySQL searches.

= What is Result Pinning (Merchandising)? =
Result Pinning allows store owners to manually force specific products or posts to the very top of search results for a designated search term—ideal for promotional campaigns, featured brand partnerships, or clearing seasonal inventory.

= How do Synonyms improve search? =
They allow you to link equivalent terms together. You can configure one-way synonyms (e.g. `sneakers => shoes`) or multi-way synonym sets (e.g. `coat, jacket, parka`). When a user searches for any linked term, Algolia returns relevant results automatically, preventing zero-result drop-offs.

= Does this plugin display the "Search by Algolia" logo? =
Yes. Algolia's Terms of Service for free-tier accounts require displaying a "Search by Algolia" attribution badge. SwiftSearch provides a clean, modern, and unobtrusive SVG badge in the search overlay footer, with an admin toggle available for paid Algolia accounts.

= How do I index large catalogs (10,000+ products)? =
For large catalogs or managed hosting providers with strict script timeouts (WP Engine, Kinsta, SiteGround), we recommend using WP-CLI:
`wp swift-search-algolia index --batch-size=500`

= Does it work with Elementor, Divi, or Gutenberg? =
Yes. You can place the `[swift_search_algolia]` shortcode into any page builder module, or enable the "Override Default Search" toggle to automatically replace your theme's native search form.

= What insights does Search Analytics provide? =
Our built-in analytics dashboard tracks your most searched terms, keyword volume trends, and zero-result queries right inside your WordPress admin. Zero-result queries show you exactly what visitors wanted to buy but could not find, giving you actionable data to expand inventory or configure synonyms.

= Can I customize search result cards with custom code? =
Yes. SwiftSearch dispatches standard JavaScript CustomEvents (`swift-search-algolia:render-hit`, `swift-search-algolia:results-rendered`) and provides WordPress PHP filters (`swift_search_algolia_post_document`) so developers can inject ratings, badges, or custom buttons effortlessly.

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

= 1.0.0 =
* Initial release of SwiftSearch for Algolia.
* Direct browser-to-Algolia query architecture via `algoliasearch/lite`.
* Automatic index settings generation (`searchableAttributes`, `attributesForFaceting`, `customRanking`).
* Sort support using Algolia Virtual Replicas (Price ASC/DESC, Date ASC/DESC).
* Record size guardrail with smart content truncation protecting Algolia 10KB free limits.
* Built-in visual merchandising and client-side hit pinning.
* Support for one-way (`root => synonyms`) and multi-way (`syn1, syn2`) synonyms.
* Local search analytics dashboard with zero-result query tracking.
* Dedicated Catalog and Shop layout mode replacing WooCommerce default shop pages.
* WP-CLI integration (`wp swift-search-algolia index`, `wp swift-search-algolia status`, `wp swift-search-algolia reset`).
* Compliant SVG "Search by Algolia" attribution badge with admin toggle.
