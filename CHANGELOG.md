# Changelog

All notable changes to the SwiftSearch for Algolia WordPress plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2026-10-30

### Added
- **WooCommerce HPOS Compatibility**: Declared official compatibility with WooCommerce High-Performance Order Storage (`custom_order_tables`) and Cart/Checkout Blocks (`cart_checkout_blocks`).
- **Extensibility Hooks**: Added `swift_search_algolia_term_document`, `swift_search_algolia_default_post_types`, `swift_search_algolia_default_taxonomies`, and `swift_search_algolia_default_sort_options` filter hooks.

### Enhanced
- **Administrative Query Hygiene**: Applied `'suppress_filters' => true` to all bulk indexing queries in WP-CLI, Background Indexer, and REST API controllers to ensure smooth database queries alongside multilingual plugins (Polylang, WPML) and custom query interceptors.
- **Centralized Safe Defaults**: Unified default post type, taxonomy, and replica sort definitions into `ConfigLoader` as a single source of truth with automatic WooCommerce detection.
- **Algolia Replica Quota Optimization**: Avoided generating unnecessary price-sorting replica indices on standard content sites without WooCommerce.

### Fixed
- **Defensive Option Management**: Added defensive typing during connection testing to ensure settings are preserved safely without data loss.

## [1.0.1] - 2026-09-25

### Fixed
- **WordPress 6.7 Compatibility**: Deferred recurring cron event scheduling (`swift_search_algolia_daily_cleanup`) to the `init` action hook and plugin activation hook, preventing early translation loading notices (`_load_textdomain_just_in_time`) triggered by third-party `cron_schedules` filters (such as WooCommerce).

## [1.0.0] - 2026-09-15

### Initial Release
- **Core Engine**: Full integration with Algolia Search REST API and Distributed Search Network (DSN).
- **Hybrid Frontend Architecture**: Bundled lightweight official `algoliasearch/lite` v4.24 client (~14KB) for resilient edge queries and retry handling, combined with custom zero-bloat vanilla JavaScript DOM rendering.
- **Instant Search UI**: Responsive, accessibility-friendly instant modal search with keyboard navigation (`Cmd+K` / `Ctrl+K`), custom card layouts, and live autocomplete.
- **WooCommerce Integration**: Native indexing for WooCommerce products, prices (with real-time WooCommerce currency symbol & position formatting), sale statuses, thumbnails, SKUs, and stock conditions.
- **Faceted Navigation**: Drag-and-drop visual builder in WordPress admin to configure multi-select taxonomy and metadata facets mapped to Algolia `attributesForFaceting`.
- **Searchable Attributes & Weighting**: Granular ranking weight controls for post titles, excerpts, body content, and custom fields.
- **Replica Indices Management**: Automatic creation and synchronization of replica indices for multi-criteria sorting (Relevance, Price: Low to High, Price: High to Low, Newest First, Oldest First).
- **Synonyms Management**: Native support for one-way synonyms (`term => target`) and multi-way synonym sets (`term1, term2, term3`), synced directly to Algolia.
- **Visual Merchandising & Pinning**: Visual admin merchandising dashboard allowing store managers to pin specific products or posts to the top of results for designated search queries.
- **Local Search Analytics**: Local database logging and dashboard reporting for total searches, search trends, top keywords, and zero-result queries.
- **WP-CLI Terminal Commands**: Complete terminal support with `wp swift-search-algolia index` (with `--batch-size`, `--offset`, `--limit`), `wp swift-search-algolia status`, and `wp swift-search-algolia reset`.
- **"Search by Algolia" Attribution**: Built-in compliant SVG footer attribution badge with admin toggle for Algolia Free tier accounts.
- **Developer Hooks**: Extensible JavaScript lifecycle events (`swift-search-algolia:render-hit`, `swift-search-algolia:results-rendered`) and WordPress PHP filters (`swift_search_algolia_post_document`, `swift_search_algolia_should_index_post`).
- **Internationalization**: Full gettext i18n localization support with standard `.pot` language catalog.
