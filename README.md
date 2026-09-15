# SwiftSearch for Algolia

<p align="center">
  <img src="assets/images/swift-search-algolia.png" alt="SwiftSearch for Algolia" width="128" height="128">
</p>

[![WordPress Plugin](https://img.shields.io/badge/WordPress-5.8+-21759b.svg?logo=wordpress&logoColor=white)](https://wordpress.org/plugins/swiftsearch-for-algolia/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-777bb4.svg?logo=php&logoColor=white)](https://www.php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2+-lightgrey.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Powered by Algolia](https://img.shields.io/badge/Powered%20By-Algolia-003dff.svg?logo=algolia&logoColor=white)](https://www.algolia.com/)

**SwiftSearch for Algolia** is an enterprise-grade, developer-friendly search engine replacement for WordPress and WooCommerce. By connecting visitors directly from their browser to Algolia's lightning-fast Distributed Search Network (DSN), SwiftSearch delivers an instant autocomplete and catalog search experience that loads in milliseconds—all while offloading 100% of search compute load from your WordPress MySQL database and PHP processing layers.

---

### 🔗 Quick Links
* **[Official Website](https://loopstates.com/)** — Learn more about Loopstates integrations and enterprise WordPress solutions.
* **[Plugin Documentation](https://docs.loopstates.com/swiftsearch-for-algolia/)** — Step-by-step setup guides, indexing best practices, and advanced settings.
* **[WordPress.org Directory](https://wordpress.org/plugins/swiftsearch-for-algolia/)** — Official WordPress plugin repository page.
* **[Algolia Sign Up](https://www.algolia.com/)** — Create a free Algolia account (includes 10,000 free search requests and 10,000 records/month).

---

## ⚡ Performance Benchmarks

In comparison to standard WordPress SQL-driven search, SwiftSearch processes queries directly on your visitor's browser via Algolia's global edge network, yielding dramatic speed and resource improvements:

| Metric / Scenario | Default WordPress Search (MySQL) | SwiftSearch + Algolia | Why it matters |
| :--- | :---: | :---: | :--- |
| **Response Latency (Keyword)** | 500ms – 1,200ms | **15ms – 40ms** | Keeps shoppers engaged; search feels instant as they type. |
| **Response Latency (Faceted Filter)** | 800ms – 2,500ms | **20ms – 50ms** | Filters and counts compile immediately without page reloads. |
| **Database Queries on Host DB** | 5 – 25+ SQL queries | **0 queries** on WP Database | Bypassing MySQL prevents database locks and downtime during sales. |
| **Server CPU Utilization** | Spikes to 90%+ under search load | **<5%** (Idle) | Offloads 100% of search computing to Algolia's DSN. |
| **Memory Overhead** | High (Bootstraps full WP Core) | **Negligible** | Bypasses PHP execution entirely for live search queries. |
| **Concurrent Search Users** | Crashes/Slows down at ~50 users | **Millions** of queries | Handled by Algolia's globally distributed cloud infrastructure. |
| **Network Payload Size** | Large (Full HTML page reload) | **Compact JSON** (Client-side) | Blazing fast rendering on mobile devices and weak network signals. |

---

## 🆚 Feature-by-Feature Comparison

Compare SwiftSearch for Algolia against default WordPress search and alternative legacy Algolia plugins:

| Feature / Capability | Default WP Search | Legacy Algolia Plugins (e.g. WP Search with Algolia) | SwiftSearch for Algolia |
| :--- | :---: | :---: | :---: |
| **Typo Tolerance** | None | Yes | **Yes (Algolia Smart Typo-Tolerance)** |
| **Instant Search-as-you-type** | No (Page refresh) | Yes (via heavy InstantSearch.js) | **Yes (Lightweight Hybrid Architecture)** |
| **Sub-millisecond Speed** | No (500ms – 1,200ms) | Yes | **Yes (15ms – 40ms)** |
| **Direct Browser-to-Algolia queries** | No (Hits WP Database) | Mixed (some require PHP proxies) | **Yes (Zero-Middleware Client)** |
| **Visual Merchandising (Pinning)** | No | Requires Algolia Enterprise Rules | **Yes (Included Visual Dashboard)** |
| **Global Synonyms Manager** | No | Raw code or Algolia Dashboard only | **Yes (One-way & Multi-way in WP Admin)** |
| **WooCommerce Catalog Integration** | No | Requires custom theme template coding | **Yes (Out of the box with dynamic currency)** |
| **Faceted Sidebar Navigation** | No | Complex InstantSearch widget configuration | **Yes (Visual Drag-and-Drop builder)** |
| **Custom Post Type (CPT) Indexing**| Requires custom PHP code | Limited | **Yes (Posts, Pages, Products & all CPTs)** |
| **Local Search Analytics** | No | Hosted on Algolia Dashboard | **Yes (Local WP DB + Zero-result insights)** |
| **10KB Record Truncation Guard** | N/A | No (fails silently if record exceeds 10KB) | **Yes (Automatic content truncation & tag strip)** |
| **Result Weighting & Ranking** | No | Requires manual Algolia Dashboard setup | **Yes (Visual sliders in WP Admin)** |
| **Automated Real-Time Sync** | Yes (Direct SQL) | Yes | **Yes (Async batch sync on post save/delete)** |
| **WP-CLI Terminal Tooling** | None | Basic | **Yes (`wp swift-search-algolia index/reset/status`)** |
| **"Search by Algolia" Badge** | N/A | Manual implementation | **Yes (Compliant SVG badge with toggle)** |

---

## 💡 Why SwiftSearch for Algolia?

Native WordPress search is fundamentally flawed for modern web applications and online stores. It executes brute-force MySQL `LIKE '%query%'` queries that fail on basic typos, ignore attribute relevance, and cause severe CPU spikes and database locks when multiple users search at the same time.

While Algolia is universally recognized as the gold standard of search infrastructure, integrating it with WordPress has traditionally been frustrating:
* The official Algolia plugin was abandoned years ago.
* Alternative community plugins rely on rigid, monolithic `InstantSearch.js` templates (often 300KB+) that conflict with theme styles, break responsive mobile layouts, and require manual code adjustments for simple tweaks.
* Commercial search plugins lock vital features like visual facet builders, synonym dictionaries, and result merchandising behind steep recurring SaaS subscriptions.
* Most critically, long WordPress posts and detailed WooCommerce descriptions frequently trigger Algolia's strict **10KB record limit** on free tier accounts, resulting in failed API payloads and incomplete search catalogs.

**SwiftSearch for Algolia eliminates every single one of these obstacles out of the box.** It provides a modern hybrid frontend architecture, zero server footprint during searches, an automated 10KB content truncation engine, and an intuitive 8-step visual administration suite—all with 100% unlocked core features.

---

## ⚡ Key Features

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

### Automatic Virtual Replica Indices for Instant Sorting
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

---

## 📋 Complete Technical Capabilities List

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

---

## ⚙️ Minimum Requirements

* **PHP Version:** `7.4` or higher (PHP 8.0+ recommended)
* **WordPress Version:** `5.8` or higher (tested up to WordPress 6.7)
* **Algolia Account:** Valid Application ID, Search-Only API Key, and Admin API Key

---

## 🚀 Installation & The 8-Step Setup Wizard

### 1. Installation
* **Automatic:** In your WordPress Admin panel, navigate to **Plugins > Add New**, search for `SwiftSearch for Algolia`, click **Install Now**, and **Activate**.
* **Manual:** Download the plugin ZIP from [WordPress.org](https://wordpress.org/plugins/swiftsearch-for-algolia/), upload via **Plugins > Add New > Upload Plugin**, and activate.

### 2. The 8-Step Setup Wizard
Once activated, click **SwiftSearch** in your admin sidebar. The setup wizard will guide you through:
1. **Step 1: Connect** — Enter your Algolia Application ID, Admin API Key, Search-Only API Key, and optional Index Prefix.
2. **Step 2: Content** — Select which Post Types (Posts, Pages, Products, CPTs) to index and map custom metadata fields.
3. **Step 3: Relevance** — Configure searchable attributes, ranking weights, and one-way or multi-way synonyms.
4. **Step 4: Search UI** — Configure visual toggles, results limits, facet sidebars, and "Search by Algolia" attribution.
5. **Step 5: Styling** — Customize accent colors, typography, and card border radii to match your theme.
6. **Step 6: Analytics** — Review search queries and zero-result search trends.
7. **Step 7: Pinning** — Visually pin high-converting items or promotions to the top of specific search queries.
8. **Step 8: Sync** — Push your Algolia index settings, configure replica indices, and run your initial bulk index.

---

## 🛠️ Developer Customization

SwiftSearch provides extensible JavaScript DOM CustomEvents and WordPress PHP filters for advanced frontend customizations, theme integrations, and headless implementations.

### JavaScript Event Hooks
Listen to custom lifecycle events on the document to hook in custom analytics, star ratings, wishlists, or quick-view buttons:

```javascript
// Triggered for every search result card rendered in the results grid
document.addEventListener('swift-search-algolia:render-hit', function(event) {
    const hitData     = event.detail.hit;         // Raw Algolia hit record
    const cardElement = event.detail.card;        // Rendered DOM Node
    const section     = event.detail.section;     // Section type (e.g. 'posts')

    if (hitData.post_type === 'product') {
        const rating = hitData.custom_rating || '';
        if (rating) {
            const badge = document.createElement('span');
            badge.className = 'custom-rating-badge';
            badge.innerText = '★ ' + rating;
            cardElement.querySelector('.ss-card-content')?.appendChild(badge);
        }
    }
});

// Triggered after the entire search results grid finishes rendering
document.addEventListener('swift-search-algolia:results-rendered', function(event) {
    const totalFound = event.detail.totalFound;
    console.log(`Rendered ${totalFound} organic results from Algolia.`);
});

// Triggered immediately before queries are sent to Algolia
document.addEventListener('swift-search-algolia:before-search', function(event) {
    console.log('Sending search query to Algolia:', event.detail.query);
});
```

### WordPress PHP Filters
Modify document payloads, adjust indexing conditions, and customize index settings before sending records to Algolia:

```php
// 1. Add custom metadata or ACF fields to Algolia records
add_filter('swift_search_algolia_post_document', function($document, $post_id, $post) {
    // Add Advanced Custom Fields (ACF)
    if (function_exists('get_field')) {
        $brand = get_field('brand_name', $post_id);
        if ($brand) {
            $document['brand'] = $brand;
        }
    }

    // Strip shortcodes and HTML tags from WYSIWYG content
    $raw_wysiwyg = get_post_meta($post_id, '_custom_product_highlights', true);
    if (!empty($raw_wysiwyg)) {
        $document['product_highlights'] = wp_strip_all_tags(strip_shortcodes($raw_wysiwyg));
    }

    // Add custom computed score or sales velocity
    $document['sales_velocity'] = (int) get_post_meta($post_id, '_total_sales_30d', true);

    return $document;
}, 10, 3);

// 2. Exclude specific posts or out-of-stock items from being indexed
add_filter('swift_search_algolia_should_index_post', function($should_index, $post_id, $post) {
    if (get_post_meta($post_id, '_is_discontinued', true) === 'yes') {
        return false;
    }
    return $should_index;
}, 10, 3);

// 3. Customize maximum content truncation limit (10KB limit safeguard)
add_filter('swift_search_algolia_truncate_content', function($content, $max_length, $post_id) {
    return mb_substr($content, 0, 5000);
}, 10, 3);

// 4. Modify compiled Algolia index settings before pushing to API
add_filter('swift_search_algolia_settings', function($settings, $config, $base_index_name) {
    $settings['separatorsToIndex'] = '-_';
    return $settings;
}, 10, 3);
```

---

## 💻 WP-CLI Commands

SwiftSearch includes native command-line support via WP-CLI. This is ideal for large catalogs (10,000+ items) or server environments (such as WP Engine or Kinsta) with strict HTTP timeout and loopback firewall restrictions:

### Run Bulk Indexing
Runs a complete bulk synchronization of all selected post types in chunks:
```bash
wp swift-search-algolia index
```
*Options:*
* `--batch-size=<size>`: Number of documents per batch (default: `250`).
* `--offset=<offset>`: Starting offset for chunked or part-migration runs.
* `--limit=<limit>`: Maximum total documents to process in this run.

### Check Index Status
Displays connectivity status, application details, and indexed document counts:
```bash
wp swift-search-algolia status
```

### Reset Index
Clears Algolia records and reconfigures index settings and replicas:
```bash
wp swift-search-algolia reset
```

---

## 🔒 Privacy & GDPR Compliance

SwiftSearch is designed with a direct, privacy-conscious architecture:
* **Zero Middle-Layer Proxy**: Search queries go directly from your visitor's browser to your own Algolia application. No search traffic is routed through Loopstates servers.
* **Self-Contained Analytics**: Search logs and zero-result query tracking data are stored locally in your WordPress database.
* **GDPR Ready**: Visitors query Algolia's secure infrastructure directly under your own Algolia Data Processing Agreement (DPA).
* **100% Free & Open Source Core**: No telemetry, no hidden data tracking, and no artificial paywalls.

---

## ⚠️ Known Limitations & Best Practices

1. **Search-Only Keys on Frontend**: Always provide a **Search-Only API Key** for the frontend. SwiftSearch never exposes your Admin API Key to website visitors.
2. **10KB Record Limit**: Algolia's Free tier limits each record to 10KB. SwiftSearch automatically strips HTML tags and truncates excessive body text to ~6,000 characters while preserving all custom metadata and facet fields.
3. **Replica Limits**: Algolia accounts have index limits depending on plan tiers. SwiftSearch only creates replica indices for the sorting criteria you explicitly enable in the admin dashboard (e.g. Price Ascending, Price Descending, Date Descending).

---

## 💬 Frequently Asked Questions (FAQ)

#### Do I need an Algolia account?
Yes. You need an Algolia account to host your search index. Algolia provides a generous Free (Build) plan that includes up to 10,000 search records and 10,000 search requests per month with zero credit card required to get started.

#### How does SwiftSearch compare to legacy Algolia plugins?
Unlike legacy plugins that rely on heavy 300KB+ InstantSearch.js templates or require manual PHP templating, SwiftSearch uses a modern hybrid client: official lightweight network transport (`algoliasearch/lite`) combined with a bespoke, zero-bloat UI engine. It includes a visual facet builder, visual product pinning, synonym manager, and local analytics out of the box with zero coding required.

#### What are the Application ID, Admin API Key, and Search-Only API Key?
You can find all three in your Algolia Dashboard under **Settings > API Keys**:
* **Application ID**: The unique identifier for your Algolia cluster.
* **Admin API Key**: Used securely on your server for batch indexing and index configuration. Never exposed to frontend visitors.
* **Search-Only API Key**: A restricted public key used by frontend visitors to execute searches securely.

#### Does SwiftSearch work with WooCommerce?
Absolutely. SwiftSearch is WooCommerce-native out of the box, indexing product titles, prices (with active WooCommerce currency symbol and position formatting), sale badges, SKUs, inventory status, categories, and attributes.

#### How fast is the search?
Because of our hybrid architecture, search queries are sent directly from the visitor's browser to Algolia's Distributed Search Network (DSN) edge nodes. Queries typically return in **15ms – 40ms**, completely eliminating the 500ms–1500ms delay of traditional WordPress MySQL searches.

#### What is Result Pinning (Merchandising)?
Result Pinning allows store owners to manually force specific products or posts to the very top of search results for a designated search term—ideal for promotional campaigns, featured brand partnerships, or clearing seasonal inventory.

#### How do Synonyms improve search?
They allow you to link equivalent terms together. You can configure one-way synonyms (e.g. `sneakers => shoes`) or multi-way synonym sets (e.g. `coat, jacket, parka`). When a user searches for any linked term, Algolia returns relevant results automatically, preventing zero-result drop-offs.

#### Does this plugin display the "Search by Algolia" logo?
Yes. Algolia's Terms of Service for free-tier accounts require displaying a "Search by Algolia" attribution badge. SwiftSearch provides a clean, modern, and unobtrusive SVG badge in the search overlay footer, with an admin toggle available for paid Algolia accounts.

#### How do I index large catalogs (10,000+ products)?
For large catalogs or managed hosting providers with strict script timeouts (WP Engine, Kinsta, SiteGround), we recommend using WP-CLI:
```bash
wp swift-search-algolia index --batch-size=500
```

#### Does it work with Elementor, Divi, or Gutenberg?
Yes. You can place the `[swift_search_algolia]` shortcode into any page builder module, or enable the "Override Default Search" toggle to automatically replace your theme's native search form.

#### What insights does Search Analytics provide?
Our built-in analytics dashboard tracks your most searched terms, keyword volume trends, and zero-result queries right inside your WordPress admin. Zero-result queries show you exactly what visitors wanted to buy but could not find, giving you actionable data to expand inventory or configure synonyms.

#### Can I customize search result cards with custom code?
Yes. SwiftSearch dispatches standard JavaScript CustomEvents (`swift-search-algolia:render-hit`, `swift-search-algolia:results-rendered`, `swift-search-algolia:before-search`) and provides WordPress PHP filters (`swift_search_algolia_post_document`) so developers can inject ratings, badges, or custom buttons effortlessly.

---

## 📄 License & Changelog

* **License:** Licensed under the GPLv2 (or later) License - see the [LICENSE](LICENSE) file for details.
* **Changelog:** For version history and upgrade notes, see [CHANGELOG.md](CHANGELOG.md).
