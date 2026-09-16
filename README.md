# SwiftSearch for Algolia

<p align="center">
  <img src="assets/images/swift-search-algolia.png" alt="SwiftSearch for Algolia" width="128" height="128">
</p>

[![WordPress Plugin](https://img.shields.io/badge/WordPress-5.8+-21759b.svg?logo=wordpress&logoColor=white)](https://wordpress.org/plugins/swiftsearch-for-algolia/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-777bb4.svg?logo=php&logoColor=white)](https://www.php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2+-lightgrey.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Powered by Algolia](https://img.shields.io/badge/Powered%20By-Algolia-003dff.svg?logo=algolia&logoColor=white)](https://www.algolia.com/)

**SwiftSearch for Algolia** connects your WordPress and WooCommerce search directly to Algolia's Distributed Search Network (DSN). By offloading search from standard WordPress database queries to Algolia's edge network, SwiftSearch delivers fast, typo-tolerant search results without straining your server resources.

---

### 🔗 Quick Links
* **[Official Website](https://loopstates.com/)** — Learn more about Loopstates integrations.
* **[Plugin Documentation](https://docs.loopstates.com/swiftsearch-for-algolia/)** — Setup guides, indexing options, and settings overview.
* **[WordPress.org Directory](https://wordpress.org/plugins/swiftsearch-for-algolia/)** — Official WordPress plugin repository page.
* **[Algolia Sign Up](https://www.algolia.com/)** — Create an Algolia account (Free Build tier includes 10,000 search requests and 10,000 records/month).

---

## ⚡ Performance Overview

In comparison to standard WordPress SQL-driven search, SwiftSearch processes queries directly on your visitor's browser via Algolia's global edge network:

| Metric / Scenario | Default WordPress Search (MySQL) | SwiftSearch + Algolia | Why It Matters |
| :--- | :---: | :---: | :--- |
| **Search Latency** | 500ms – 1,200ms | **15ms – 40ms** | Keeps visitors engaged with immediate results as they type. |
| **Faceted Filter Speed** | 800ms – 2,500ms | **20ms – 50ms** | Instant multi-select filtering without page reloads. |
| **Database Load** | 5 – 25+ SQL queries | **0 queries** on WP Database | Offloads search processing from your database and PHP processes. |
| **High Traffic Handling** | Slows down during traffic spikes | **Cloud Scaled** | Handled directly by Algolia's distributed edge infrastructure. |
| **Typo Tolerance** | None (exact match only) | **Built-in Smart Typo Matching** | Prevents zero-result drop-offs caused by minor spelling mistakes. |

---

## 💡 Why SwiftSearch for Algolia?

Standard WordPress search uses basic database queries that can slow down under high traffic, lack typo tolerance, and return irrelevant results for complex queries.

SwiftSearch for Algolia connects your site directly to Algolia's search infrastructure, giving your visitors an instant search-as-you-type experience with faceted filtering, merchandising, and built-in search analytics.

---

## ⚡ Key Features

### Fast Search Results via Algolia DSN
Search queries travel directly from visitors' browsers to Algolia's nearest edge location. Queries bypass WordPress and MySQL entirely, delivering results in milliseconds while offloading search computing load from your hosting server.

### Instant Autocomplete and Search-As-You-Type
Deliver matching results the moment visitors start typing. The reactive search UI updates dynamically with highlighted keyword matches, product thumbnails, WooCommerce prices, stock status badges, and taxonomy tags.

### Visual Merchandising & Product Pinning
Take control of search rankings to highlight specific items. The Merchandising dashboard allows store managers to manually pin products, posts, or pages to top positions for designated search terms.

### Smart Typo Tolerance and Synonym Dictionaries
Algolia's typo tolerance handles spelling mistakes and pluralizations automatically. Define one-way synonyms (e.g. `sneakers => shoes`) and multi-way synonym sets (e.g. `coat, jacket, outerwear`) directly in your WordPress dashboard and sync them to Algolia with one click.

### Visual Facet Builder & Custom Field Mappings
Enable visitors to filter results dynamically without page reloads. Map WooCommerce product attributes (Size, Color, Brand), standard taxonomies, or custom metadata fields into Algolia using the visual builder with live item counts.

### Automatic Virtual Replica Sorting
Algolia provides fast sorting through replica indices rather than runtime database queries. SwiftSearch automatically creates and synchronizes virtual replica indices for active sort criteria (Relevance, Price: Low to High, Price: High to Low, Newest First, Oldest First) without duplicating record quotas, allowing visitors to switch sort order instantly on the frontend.

### Real-Time Search Analytics & Zero-Result Insights
Track search volume trends and view zero-result queries directly in your WordPress admin. Identifying what visitors searched for but could not find gives you actionable data to adjust inventory or configure synonyms.

### Dedicated WooCommerce Shop & Catalog Mode
Replace default WooCommerce shop and archive pages with an instant-filter catalog layout. Features horizontal sort dropdowns, sticky facet sidebars with live category counts, pagination, and clean card styling.

### Background Indexing & Real-Time Sync
The background indexing engine processes content in self-scheduling batches to prevent server script timeouts. Once your initial index is complete, new, updated, or deleted posts and products synchronize automatically in real time.

### WP-CLI Command-Line Integration
For stores with large catalogs (10,000+ items) or servers with strict execution timeouts, SwiftSearch includes full WP-CLI terminal commands. Run bulk indexing (`wp swift-search-algolia index`), check connection status (`wp swift-search-algolia status`), and reset indices (`wp swift-search-algolia reset`) directly via SSH.

### Shortcode & Theme Compatibility
Customize accent colors, typography, card layouts, and display options (show/hide prices, thumbnails, excerpts, stock status) from the admin panel. Use the `[swift_search_algolia]` shortcode anywhere on your site, or enable the search form override to automatically replace your theme's native search bar.

---

## 📋 Features & Capabilities

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

---

## ⚙️ Minimum Requirements

* **WordPress Version:** `5.8` or higher
* **PHP Version:** `7.4` or higher (PHP 8.0+ recommended)
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
3. **Step 3: Relevance** — Configure title matching priority and define one-way or multi-way synonyms.
4. **Step 4: Search UI** — Configure visual toggles, results limits, facet sidebars, and "Search by Algolia" attribution.
5. **Step 5: Styling** — Customize accent colors, typography, and card border radii to match your theme.
6. **Step 6: Analytics** — Review search queries and zero-result search trends.
7. **Step 7: Pinning** — Visually pin high-converting items or promotions to the top of specific search queries.
8. **Step 8: Sync** — Push your Algolia index settings, configure replica indices, and run your initial bulk index.

---

## 🛠️ Developer Customization

SwiftSearch provides JavaScript DOM CustomEvents and WordPress PHP filters for theme integrations and custom functionality.

### JavaScript Event Hooks
Listen to custom lifecycle events on the document to hook in custom analytics, ratings, or action buttons:

```javascript
// Triggered for every search result card rendered in the results grid
document.addEventListener('swift-search-algolia:render-hit', function(event) {
    const hitData     = event.detail.hit;         // Raw Algolia hit record
    const cardElement = event.detail.card;        // Rendered DOM Node
    const section     = event.detail.section;     // Section type (e.g. 'posts')

    if (hitData.post_type === 'product' && hitData.custom_rating) {
        const badge = document.createElement('span');
        badge.className = 'custom-rating-badge';
        badge.innerText = '★ ' + hitData.custom_rating;
        cardElement.querySelector('.ss-card-content')?.appendChild(badge);
    }
});

// Triggered after the entire search results grid finishes rendering
document.addEventListener('swift-search-algolia:results-rendered', function(event) {
    const totalFound = event.detail.totalFound;
    console.log(`Rendered ${totalFound} results from Algolia.`);
});

// Triggered immediately before queries are sent to Algolia
document.addEventListener('swift-search-algolia:before-search', function(event) {
    console.log('Sending search query to Algolia:', event.detail.query);
});
```

### WordPress PHP Filters
Modify document payloads, adjust indexing conditions, and customize index settings before sending records to Algolia:

```php
// 1. Add custom metadata or attributes to Algolia records
add_filter('swift_search_algolia_post_document', function($document, $post_id, $post) {
    $brand = get_post_meta($post_id, 'brand_name', true);
    if (!empty($brand)) {
        $document['brand'] = $brand;
    }
    return $document;
}, 10, 3);

// 2. Exclude specific posts or items from being indexed
add_filter('swift_search_algolia_should_index_post', function($should_index, $post_id, $post) {
    if (get_post_meta($post_id, '_is_discontinued', true) === 'yes') {
        return false;
    }
    return $should_index;
}, 10, 3);

// 3. Modify compiled Algolia index settings before pushing to API
add_filter('swift_search_algolia_settings', function($settings, $config, $base_index_name) {
    $settings['separatorsToIndex'] = '-_';
    return $settings;
}, 10, 3);
```

---

## 💻 WP-CLI Commands

SwiftSearch includes command-line support via WP-CLI. This is ideal for large catalogs (10,000+ items) or server environments with script timeout limitations:

### Run Bulk Indexing
Runs a complete bulk synchronization of all selected post types in batches:
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
* **Zero Middle-Layer Proxy**: Search queries go directly from your visitor's browser to your own Algolia application. No search traffic is routed through external proxy servers.
* **Self-Contained Analytics**: Search logs and zero-result query tracking data are stored locally in your WordPress database.
* **GDPR Ready**: Visitors query Algolia's secure infrastructure directly under your own Algolia Data Processing Agreement (DPA).
* **Open Core**: No hidden data tracking, telemetry, or artificial locks on core features.

---

## 💬 Frequently Asked Questions (FAQ)

#### Do I need an Algolia account?
Yes. You need an Algolia account to host your search index. Algolia provides a Free (Build) plan that includes up to 10,000 search records and 10,000 search requests per month with no credit card required to get started.

#### What are the Application ID, Admin API Key, and Search-Only API Key?
You can find all three in your Algolia Dashboard under **Settings > API Keys**:
* **Application ID**: The unique identifier for your Algolia cluster.
* **Admin API Key**: Used securely on your server for batch indexing and index configuration. Never exposed to frontend visitors.
* **Search-Only API Key**: A restricted public key used by frontend visitors to execute searches securely.

#### Does SwiftSearch work with WooCommerce?
Yes. SwiftSearch is WooCommerce-compatible out of the box, indexing product titles, prices (with active WooCommerce currency formatting), sale badges, SKUs, inventory status, categories, and attributes.

#### How fast is the search?
Because search queries are sent directly from the visitor's browser to Algolia's edge nodes, queries typically return in **15ms – 40ms**, eliminating the delay of traditional WordPress database searches.

#### What is Result Pinning (Merchandising)?
Result Pinning allows store owners to manually place specific products or posts at the very top of search results for a designated search term—ideal for promotional campaigns, featured items, or clearing seasonal inventory.

#### How do Synonyms improve search?
They allow you to link equivalent terms together. You can configure one-way synonyms (e.g. `sneakers => shoes`) or multi-way synonym sets (e.g. `coat, jacket, outerwear`). When a user searches for any linked term, Algolia returns relevant results automatically, preventing zero-result drop-offs.

#### Does this plugin display the "Search by Algolia" logo?
Yes. Algolia requires displaying a "Search by Algolia" attribution badge on free accounts. SwiftSearch provides a clean, modern SVG badge in the search footer, with an admin toggle available for paid Algolia accounts.

#### How do I index large catalogs (10,000+ products)?
For large catalogs or managed hosting environments with strict script timeouts, we recommend using WP-CLI:
```bash
wp swift-search-algolia index --batch-size=500
```

#### Does it work with page builders and custom themes?
Yes. You can place the `[swift_search_algolia]` shortcode into any page builder or block layout, or enable the "Override Default Search" toggle to automatically replace your theme's native search form.

#### What insights does Search Analytics provide?
Our built-in analytics dashboard tracks your most searched terms, keyword volume trends, and zero-result queries right inside your WordPress admin. Zero-result queries show you exactly what visitors searched for but could not find, giving you actionable data to expand inventory or configure synonyms.

---

## 📄 License & Changelog

* **License:** Licensed under the GPLv2 (or later) License - see the [LICENSE](LICENSE) file for details.
* **Changelog:** For version history and upgrade notes, see [CHANGELOG.md](CHANGELOG.md).
