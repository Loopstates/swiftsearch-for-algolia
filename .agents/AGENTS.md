# Active Clients & Project Memory

## 1. Walter Kelly (Joel)
* **Domain**: `https://wtwarms.wpenginepowered.com`
* **Hosting Host**: WP Engine
* **Initial Troubleshooting & Background**:
  * **Stalled Indexing**: Faced extremely slow indexing that stalled at exactly 5,499 documents out of a total 50,000 catalog size. WP Engine's WAF / server rules blocked local background loopback requests (`admin-ajax.php`) and enforced strict script timeouts.
  * **CLI Workaround**: Introduced part-migration support in the WP-CLI tool (version **1.6.2**) so they could run the indexer in chunks of 5,000 (e.g., `wp swift-search index --offset=0 --limit=5000`).
  * **Cloud Sync Workaround**: Gifted them the Cloud Sync addon for free using the coupon code `WTWARMS` to handle indexing serverless and bypass WP Engine server execution limits entirely.
* **Known Issues & Solutions**:
  * **Mobile Search Freeze**: Solved in base plugin version **1.6.3** and AI Search addon **1.0.1** by switching the JS selector bindings from absolute IDs to relative query selectors on `.ss-wrapper` classes (resolving duplicate ID elements output by responsive theme headers).
  * **Cloud Sync History**: Config/logs originally had 24-hour/7-day expirations (TTL) in Redis. This was disabled to store records permanently. The overview dashboard was updated with a dynamic fallback decoder to recover Walter's completed status, custom timeline logs, and counts.
  * **Price Facet Bug**: Core `price` field in the schema is registered without `'facet' => true` by default, causing search errors when Price filters are enabled in the sidebar navigation (since schema compiler skips it as a duplicate core name). Next step is to fix this in the base plugin schema list.

---

## 2. Morgan Davis (Fireman Creative)
* **End-Client**: JFCS (Jewish Family and Children's Services - Nonprofit)
* **Target Launch**: Early September 2026
* **Known Issues & Architecture**:
  * **Staging Host**: WordPress hosted on Kinsta. Loopback sync is blocked by Kinsta's firewall; indexing must be performed via WP-CLI.
  * **ACF Integration**: Mapped ~50 custom ACF fields into SwiftSearch, which represents almost all searchable page content.
  * **Memory Limits & OOM**: Encountered `OUT_OF_MEMORY` errors on Typesense Cloud when trying to run local/in-cluster embeddings (`ts/gte-small`) on a small RAM server tier.
  * **Recommending OpenAI**: The recommended solution is to switch to external OpenAI embeddings (`text-embedding-3-small`). This keeps model files off the cluster RAM, allowing the nonprofit to run on the smallest, cheapest Typesense Cloud tier (512MB RAM) safely.
  * **Addon Integration**: Our AI Search Addon acts as a subscription companion that sits alongside the base plugin, managing auto-embedding schemas programmatically and running the frontend vector query logic (`search-semantic.js`).
