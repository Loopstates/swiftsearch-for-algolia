<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="swift-search-dashboard swift-search-algolia-dashboard" id="swift-search-app" data-engine="algolia">
    <header class="ss-header">
        <div class="ss-logo">
            <img src="<?php echo esc_url(SWIFT_SEARCH_ALGOLIA_URL . 'assets/images/swift-search-algolia.png'); ?>"
                alt="SwiftSearch for Algolia" class="ss-brand-logo"
                style="max-height: 40px; margin-right: 12px; vertical-align: middle;">
            <h1 style="display: inline-block; vertical-align: middle; margin: 0;">SwiftSearch for Algolia <span
                    class="ss-version">v<?php echo esc_html(SWIFT_SEARCH_ALGOLIA_VERSION); ?></span></h1>
        </div>
        <div class="ss-actions" style="display: flex; align-items: center; gap: 15px;">
            <a href="https://docs.loopstates.com/swiftsearch-for-algolia/?ref=wp-plugin" target="_blank"
                class="ss-link" style="margin-left: 0;"><?php esc_html_e('Documentation', 'swiftsearch-for-algolia'); ?></a>
            <span class="ss-link-separator" style="color: var(--ss-text-muted); opacity: 0.5;">|</span>
            <a href="https://dashboard.algolia.com/account/api-keys" target="_blank"
                class="ss-link" style="margin-left: 0;"><?php esc_html_e('Algolia Dashboard', 'swiftsearch-for-algolia'); ?></a>
        </div>
    </header>

    <div class="ss-container">
        <!-- Sidebar Navigation -->
        <aside class="ss-sidebar">
            <nav class="ss-nav">
                <ul>
                    <li class="ss-nav-item active" data-step="connect">
                        <span class="step-num">1</span>
                        <span class="step-label"><?php esc_html_e('Connect', 'swiftsearch-for-algolia'); ?></span>
                    </li>
                    <li class="ss-nav-item" data-step="content">
                        <span class="step-num">2</span>
                        <span class="step-label"><?php esc_html_e('Content', 'swiftsearch-for-algolia'); ?></span>
                    </li>
                    <li class="ss-nav-item" data-step="relevance">
                        <span class="step-num">3</span>
                        <span class="step-label"><?php esc_html_e('Relevance', 'swiftsearch-for-algolia'); ?></span>
                    </li>
                    <li class="ss-nav-item" data-step="search-ui">
                        <span class="step-num">4</span>
                        <span class="step-label"><?php esc_html_e('Search UI', 'swiftsearch-for-algolia'); ?></span>
                    </li>
                    <li class="ss-nav-item" data-step="styling">
                        <span class="step-num">5</span>
                        <span class="step-label"><?php esc_html_e('Styling & UI', 'swiftsearch-for-algolia'); ?></span>
                    </li>
                    <li class="ss-nav-item" data-step="analytics">
                        <span class="step-num">6</span>
                        <span class="step-label"><?php esc_html_e('Analytics', 'swiftsearch-for-algolia'); ?></span>
                    </li>
                    <li class="ss-nav-item" data-step="pinning">
                        <span class="step-num">7</span>
                        <span class="step-label"><?php esc_html_e('Pinning', 'swiftsearch-for-algolia'); ?></span>
                    </li>
                    <li class="ss-nav-item" data-step="sync">
                        <span class="step-num">8</span>
                        <span class="step-label"><?php esc_html_e('Sync', 'swiftsearch-for-algolia'); ?></span>
                    </li>
                </ul>
            </nav>

            <div class="ss-status-card">
                <h3><?php esc_html_e('System Status', 'swiftsearch-for-algolia'); ?></h3>
                <div class="status-row">
                    <span class="label"><?php esc_html_e('Algolia', 'swiftsearch-for-algolia'); ?></span>
                    <span class="value disconnected"
                        id="ss-connection-status"><?php esc_html_e('Disconnected', 'swiftsearch-for-algolia'); ?></span>
                </div>
                <div class="status-row">
                    <span class="label"><?php esc_html_e('Records', 'swiftsearch-for-algolia'); ?></span>
                    <span class="value" id="ss-doc-count">0</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="ss-content">
            <!-- Global Warning Container -->
            <div id="ss-global-notice-container" style="margin-bottom: 20px;"></div>

            <!-- Step 1: Connect -->
            <section class="ss-step-view active" id="view-connect">
                <div class="ss-card">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Connect to Algolia', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Enter your Algolia Application ID and API credentials. You can find these in your Algolia Dashboard under Settings > API Keys.', 'swiftsearch-for-algolia'); ?>
                        </p>
                    </div>
                    <div class="ss-card-body">
                        <form id="ss-connect-form">
                            <div class="ss-form-group">
                                <label for="ts-app-id"><?php esc_html_e('Application ID', 'swiftsearch-for-algolia'); ?></label>
                                <input type="text" id="ts-app-id" name="app_id" placeholder="e.g. 5ABCDEF123" required>
                                <p class="description">
                                    <?php esc_html_e('Your unique Algolia Application identifier.', 'swiftsearch-for-algolia'); ?>
                                </p>
                            </div>

                            <div class="ss-form-group">
                                <label for="ts-admin-api-key"><?php esc_html_e('Admin API Key', 'swiftsearch-for-algolia'); ?></label>
                                <div class="ss-input-wrapper">
                                    <input type="password" id="ts-admin-api-key" name="admin_api_key" required>
                                    <button type="button" class="ss-input-toggle ss-toggle-api-key" title="<?php esc_attr_e('Toggle Visibility', 'swiftsearch-for-algolia'); ?>">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </button>
                                </div>
                                <p class="description">
                                    <?php esc_html_e('Used securely on your server for batch indexing and index configuration. Never shared with frontend visitors.', 'swiftsearch-for-algolia'); ?>
                                </p>
                            </div>

                            <div class="ss-form-group">
                                <label for="ts-search-api-key"><?php esc_html_e('Search-Only API Key', 'swiftsearch-for-algolia'); ?></label>
                                <div class="ss-input-wrapper">
                                    <input type="password" id="ts-search-api-key" name="search_api_key" required>
                                    <button type="button" class="ss-input-toggle ss-toggle-api-key" title="<?php esc_attr_e('Toggle Visibility', 'swiftsearch-for-algolia'); ?>">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </button>
                                </div>
                                <p class="description">
                                    <?php esc_html_e('Public search-only key loaded by browser visitors to query Algolia indices securely.', 'swiftsearch-for-algolia'); ?>
                                </p>
                            </div>

                            <div class="ss-form-group">
                                <label for="ts-index-prefix"><?php esc_html_e('Index Prefix (Optional)', 'swiftsearch-for-algolia'); ?></label>
                                <input type="text" id="ts-index-prefix" name="index_prefix" placeholder="e.g. wp_">
                                <p class="description">
                                    <?php esc_html_e('Optional prefix prepended to all index names (e.g. wp_posts). Default is empty.', 'swiftsearch-for-algolia'); ?>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="ss-form-actions" style="margin-top: 20px;">
                    <button type="button" id="ss-connect-btn"
                        class="ss-btn ss-btn-primary"><?php esc_html_e('Save & Test Connection', 'swiftsearch-for-algolia'); ?></button>
                </div>
            </section>

            <!-- Step 2: Content -->
            <section class="ss-step-view" id="view-content" style="display:none;">
                <div class="ss-card">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Search Behavior', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Configure how SwiftSearch integrates with your theme.', 'swiftsearch-for-algolia'); ?>
                        </p>
                    </div>
                    <div class="ss-card-body">
                        <div class="ss-form-group-row">
                            <div class="ss-form-group">
                                <label class="ss-checkbox-card" style="width: 100%;">
                                    <input type="checkbox" id="ss-override-default">
                                    <div class="info">
                                        <span
                                            class="title"><?php esc_html_e('Override Default Search', 'swiftsearch-for-algolia'); ?></span>
                                        <span
                                            class="meta"><?php esc_html_e('Automatically replace the native WordPress search form with SwiftSearch.', 'swiftsearch-for-algolia'); ?></span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ss-card">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Searchable Content', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Configure which content types (Posts, Pages, Products, Custom Types) should be indexed into Algolia.', 'swiftsearch-for-algolia'); ?>
                        </p>
                    </div>
                    <div class="ss-card-body">
                        <div class="ss-option-list" id="ss-content-settings-container">
                            <!-- Dynamic Checkboxes -->
                        </div>
                    </div>
                </div>

                <!-- Advanced Data -->
                <div class="ss-card" style="margin-top: 20px;">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Advanced Data & Custom Fields', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Map custom meta fields to Algolia records (e.g. Price, SKU, Brand).', 'swiftsearch-for-algolia'); ?>
                        </p>
                    </div>
                    <div class="ss-card-body" id="ss-custom-fields-container">
                        <div style="padding: 20px; text-align: center; color: #6b7280;">
                            <span class="ss-loader"></span> Loading Fields...
                        </div>
                    </div>
                </div>

                <div class="ss-form-actions" style="margin-top: 20px;">
                    <button type="button" class="ss-btn ss-btn-primary"
                        id="ss-save-content"><?php esc_html_e('Save Content Settings', 'swiftsearch-for-algolia'); ?></button>
                </div>
            </section>

            <!-- Step 3: Relevance -->
            <section class="ss-step-view" id="view-relevance" style="display:none;">
                <div class="ss-card">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Relevance & Synonyms', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Fine-tune ranking weights and define synonyms pushed directly to Algolia.', 'swiftsearch-for-algolia'); ?></p>
                    </div>
                    <div class="ss-card-body">
                        <div class="ss-relevance-settings-wrapper">
                            <div class="ss-form-group">
                                <label><?php esc_html_e('Title Matching Priority:', 'swiftsearch-for-algolia'); ?> <span id="ss-relevance-val" style="font-weight:700;">50</span></label>
                                <input type="range" id="ss-relevance-range" min="1" max="100" value="50" oninput="document.getElementById('ss-relevance-val').innerText = this.value">
                                <p class="description">
                                    <?php esc_html_e('Controls attribute priority in Algolia searchableAttributes. Higher values prioritize post title matches over content.', 'swiftsearch-for-algolia'); ?>
                                </p>
                            </div>
                            <div class="ss-form-group">
                                <label><?php esc_html_e('Synonyms (One-Way & Multi-Way)', 'swiftsearch-for-algolia'); ?></label>
                                <textarea id="ss-synonyms-list" rows="6" class="ss-input" placeholder="jacket, coat, blazer&#10;laptop => macbook, thinkpad&#10;bag, backpack, tote"></textarea>
                                <p class="description">
                                    <?php esc_html_e('Enter one group per line. Use comma separation for two-way synonyms (e.g. jacket, coat). Use "root => synonym1, synonym2" for one-way synonyms (e.g. tablet => ipad).', 'swiftsearch-for-algolia'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ss-form-actions" style="margin-top: 20px;">
                    <button type="button" class="ss-btn ss-btn-primary" id="ss-save-relevance"><?php esc_html_e('Save Relevance Settings', 'swiftsearch-for-algolia'); ?></button>
                </div>
            </section>

            <!-- Step 4: Search UI -->
            <section class="ss-step-view" id="view-search-ui" style="display:none;">
                <!-- Experience Options -->
                <div class="ss-card">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Experience Options', 'swiftsearch-for-algolia'); ?></h2>
                    </div>
                    <div class="ss-card-body">
                        <div class="ss-form-group-row">
                            <div class="ss-form-group">
                                <label class="ss-checkbox-card" id="ss-instant-search-card">
                                    <input type="checkbox" id="ss-instant-search" checked>
                                    <div class="info">
                                        <span class="title"><?php esc_html_e('Instant Search', 'swiftsearch-for-algolia'); ?></span>
                                        <span class="meta"><?php esc_html_e('Search as you type with debounced fast queries.', 'swiftsearch-for-algolia'); ?></span>
                                    </div>
                                </label>
                            </div>
                            <div class="ss-form-group">
                                <label class="ss-checkbox-card">
                                    <input type="checkbox" id="ss-typo-tolerance" checked>
                                    <div class="info">
                                        <span class="title"><?php esc_html_e('Typo Tolerance', 'swiftsearch-for-algolia'); ?></span>
                                        <span class="meta"><?php esc_html_e('Enable Algolia fuzzy matching for spelling mistakes.', 'swiftsearch-for-algolia'); ?></span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="ss-form-group-row">
                            <div class="ss-form-group">
                                <label class="ss-checkbox-card">
                                    <input type="checkbox" id="ss-global-show-thumb" checked>
                                    <div class="info">
                                        <span class="title"><?php esc_html_e('Show Thumbnails', 'swiftsearch-for-algolia'); ?></span>
                                        <span class="meta"><?php esc_html_e('Display product or post images in results.', 'swiftsearch-for-algolia'); ?></span>
                                    </div>
                                </label>
                            </div>
                            <div class="ss-form-group">
                                <label class="ss-checkbox-card">
                                    <input type="checkbox" id="ss-global-show-price" checked>
                                    <div class="info">
                                        <span class="title"><?php esc_html_e('Show Prices', 'swiftsearch-for-algolia'); ?></span>
                                        <span class="meta"><?php esc_html_e('Display WooCommerce product prices.', 'swiftsearch-for-algolia'); ?></span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="ss-form-group-row">
                            <div class="ss-form-group">
                                <label class="ss-checkbox-card">
                                    <input type="checkbox" id="ss-global-show-excerpt">
                                    <div class="info">
                                        <span class="title"><?php esc_html_e('Show Excerpts', 'swiftsearch-for-algolia'); ?></span>
                                        <span class="meta"><?php esc_html_e('Display short descriptions below titles.', 'swiftsearch-for-algolia'); ?></span>
                                    </div>
                                </label>
                            </div>
                            <div class="ss-form-group">
                                <label class="ss-checkbox-card">
                                    <input type="checkbox" id="ss-show-algolia-badge" checked>
                                    <div class="info">
                                        <span class="title"><?php esc_html_e('Display "Search by Algolia" Badge', 'swiftsearch-for-algolia'); ?></span>
                                        <span class="meta"><?php esc_html_e('Required by Algolia Terms of Service on free/Build tier plans.', 'swiftsearch-for-algolia'); ?></span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="ss-form-group-row">
                            <div class="ss-form-group">
                                <label><?php esc_html_e('Global Results Limit', 'swiftsearch-for-algolia'); ?></label>
                                <input type="number" id="ss-global-limit" value="10" min="1" max="50">
                                <p class="description"><?php esc_html_e('Default number of items to return per search.', 'swiftsearch-for-algolia'); ?></p>
                            </div>
                            <div class="ss-form-group">
                                <label style="display:block; margin-bottom: 8px; font-weight: 500;"><?php esc_html_e('Layout Setting', 'swiftsearch-for-algolia'); ?></label>
                                <select id="ss-global-layout" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #d1d5db; height: 40px; box-sizing: border-box;">
                                    <option value="overlay"><?php esc_html_e('Floating Search Overlay (Modal)', 'swiftsearch-for-algolia'); ?></option>
                                    <option value="catalog"><?php esc_html_e('Dedicated Catalog Page (Sticky Sidebar)', 'swiftsearch-for-algolia'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shortcode Builder -->
                <div class="ss-card" style="margin-top: 30px;">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Shortcode Generator', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Customize individual search bars. Unchecked items will use global defaults.', 'swiftsearch-for-algolia'); ?></p>
                    </div>
                    <div class="ss-card-body">
                        <div class="ss-form-group-row">
                            <div class="ss-form-group">
                                <label><?php esc_html_e('Placeholder Text', 'swiftsearch-for-algolia'); ?></label>
                                <input type="text" id="sc-placeholder" value="Search...">
                            </div>
                            <div class="ss-form-group">
                                <label><?php esc_html_e('Override Results Limit', 'swiftsearch-for-algolia'); ?></label>
                                <input type="number" id="sc-limit" placeholder="Inherit Global">
                            </div>
                        </div>

                        <div class="ss-card-grid">
                            <div class="ss-form-group">
                                <label class="ss-checkbox-card">
                                    <input type="checkbox" id="sc-show-thumb" checked>
                                    <div class="info">
                                        <span class="title"><?php esc_html_e('Show Thumbnail', 'swiftsearch-for-algolia'); ?></span>
                                    </div>
                                </label>
                            </div>
                            <div class="ss-form-group">
                                <label class="ss-checkbox-card">
                                    <input type="checkbox" id="sc-show-price" checked>
                                    <div class="info">
                                        <span class="title"><?php esc_html_e('Show Price', 'swiftsearch-for-algolia'); ?></span>
                                    </div>
                                </label>
                            </div>
                            <div class="ss-form-group">
                                <label class="ss-checkbox-card">
                                    <input type="checkbox" id="sc-show-excerpt">
                                    <div class="info">
                                        <span class="title"><?php esc_html_e('Show Excerpt', 'swiftsearch-for-algolia'); ?></span>
                                    </div>
                                </label>
                            </div>
                            <div class="ss-form-group">
                                <label style="display:block; margin-bottom: 8px; font-weight: 500;"><?php esc_html_e('Layout Override', 'swiftsearch-for-algolia'); ?></label>
                                <select id="sc-layout" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #d1d5db; height: 40px; box-sizing: border-box;">
                                    <option value="default"><?php esc_html_e('Inherit Global Layout', 'swiftsearch-for-algolia'); ?></option>
                                    <option value="overlay"><?php esc_html_e('Floating Search Overlay', 'swiftsearch-for-algolia'); ?></option>
                                    <option value="catalog"><?php esc_html_e('Dedicated Catalog Page', 'swiftsearch-for-algolia'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="ss-code-preview" style="margin-top: 20px;">
                            <code id="sc-preview">[swift_search_algolia]</code>
                            <button type="button" class="ss-btn ss-btn-sm ss-btn-secondary" id="ss-copy-sc"><?php esc_html_e('Copy Shortcode', 'swiftsearch-for-algolia'); ?></button>
                        </div>
                    </div>
                </div>

                <!-- Faceted Navigation -->
                <div class="ss-card" style="margin-top: 30px;">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Faceted Navigation (Sidebar)', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Configure filters for your search results sidebar. Facets are dynamically registered into Algolia index settings.', 'swiftsearch-for-algolia'); ?>
                        </p>
                    </div>
                    <div class="ss-card-body">
                        <div id="ss-facets-config-container">
                            <div style="padding: 20px; text-align: center; color: #6b7280;">
                                <span class="ss-loader"></span> Loading Facets...
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ss-form-actions" style="margin-top: 20px;">
                    <button type="button" class="ss-btn ss-btn-primary"
                        id="ss-save-search-ui"><?php esc_html_e('Save Search UI Settings', 'swiftsearch-for-algolia'); ?></button>
                </div>
            </section>
            
            <!-- Step 5: Styling & UI -->
            <section class="ss-step-view" id="view-styling" style="display:none;">
                <div class="ss-card">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Styling & Customization', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Customize colors and border radiuses to match your theme perfectly.', 'swiftsearch-for-algolia'); ?></p>
                    </div>
                    <div class="ss-card-body">
                         <div class="ss-form-group-row" style="gap: 20px;">
                            <div class="ss-form-group" style="flex:1;">
                                <label for="ss-primary-color"><?php esc_html_e('Primary Accent Color', 'swiftsearch-for-algolia'); ?></label>
                                <input type="color" id="ss-primary-color" class="ss-input-color" value="#1e3a8a">
                                <p class="description"><?php esc_html_e('Used for buttons, active facets, and highlights.', 'swiftsearch-for-algolia'); ?></p>
                            </div>
                            <div class="ss-form-group" style="flex:1;">
                                <label for="ss-text-color"><?php esc_html_e('Main Text Color', 'swiftsearch-for-algolia'); ?></label>
                                <input type="color" id="ss-text-color" class="ss-input-color" value="#1f2937">
                                <p class="description"><?php esc_html_e('Color for titles and body text.', 'swiftsearch-for-algolia'); ?></p>
                            </div>
                            <div class="ss-form-group" style="flex:1;">
                                <label for="ss-card-bg"><?php esc_html_e('Card Background', 'swiftsearch-for-algolia'); ?></label>
                                <input type="color" id="ss-card-bg" class="ss-input-color" value="#ffffff">
                                <p class="description"><?php esc_html_e('Background color for result cards and modals.', 'swiftsearch-for-algolia'); ?></p>
                            </div>
                        </div>

                        <div class="ss-form-group" style="margin-top: 20px;">
                            <label for="ss-border-radius"><?php esc_html_e('Border Radius (px)', 'swiftsearch-for-algolia'); ?></label>
                            <input type="number" id="ss-border-radius" class="ss-input" value="16" min="0" max="100">
                            <p class="description"><?php esc_html_e('Global roundness for cards, inputs, and buttons.', 'swiftsearch-for-algolia'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="ss-form-actions" style="margin-top: 20px;">
                    <button type="button" class="ss-btn ss-btn-primary" id="ss-save-styling"><?php esc_html_e('Save Styling Settings', 'swiftsearch-for-algolia'); ?></button>
                </div>
            </section>

            <!-- Step 6: Analytics -->
            <section class="ss-step-view" id="view-analytics" style="display:none;">
                <div class="ss-card">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Local Search Analytics', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Self-hosted search metrics tracking query volume, top searches, and zero-result queries.', 'swiftsearch-for-algolia'); ?>
                        </p>
                    </div>
                    <div class="ss-card-body">
                        <div class="ss-analytics-dashboard" id="ss-analytics-dashboard">
                            <div class="ss-form-group" style="margin-bottom: 2rem; position: relative; height: 300px;">
                                <canvas id="ss-analytics-chart"></canvas>
                            </div>
                            <div class="ss-form-group-row">
                                <div class="ss-form-group" style="flex:1;">
                                    <h3><?php esc_html_e('Top Searches', 'swiftsearch-for-algolia'); ?></h3>
                                    <table class="wp-list-table widefat fixed striped" id="ss-analytics-top">
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e('Query', 'swiftsearch-for-algolia'); ?></th>
                                                <th><?php esc_html_e('Count', 'swiftsearch-for-algolia'); ?></th>
                                                <th><?php esc_html_e('Last Searched', 'swiftsearch-for-algolia'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="3"><?php esc_html_e('Loading...', 'swiftsearch-for-algolia'); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="ss-form-group" style="flex:1;">
                                    <h3><?php esc_html_e('Zero Result Queries', 'swiftsearch-for-algolia'); ?></h3>
                                    <table class="wp-list-table widefat fixed striped" id="ss-analytics-zero">
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e('Query', 'swiftsearch-for-algolia'); ?></th>
                                                <th><?php esc_html_e('Count', 'swiftsearch-for-algolia'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="2"><?php esc_html_e('Loading...', 'swiftsearch-for-algolia'); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Step 7: Results Pinning -->
            <section class="ss-step-view" id="view-pinning" style="display:none;">
                <div class="ss-card">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Visual Merchandising & Pinned Results', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Promote specific products or posts to the top of search results.', 'swiftsearch-for-algolia'); ?>
                        </p>
                    </div>
                    <div class="ss-card-body">
                        <div class="ss-pinning-settings-wrapper">
                            <div class="ss-pinning-ui">
                                <div class="ss-form-group" style="position:relative;">
                                    <label><?php esc_html_e('Select Item to Pin', 'swiftsearch-for-algolia'); ?></label>
                                    <input type="text" id="ss-pinning-search" class="ss-input"
                                        placeholder="<?php esc_attr_e('Type a product or post name to pin...', 'swiftsearch-for-algolia'); ?>"
                                        autocomplete="off">
                                    <div id="ss-pinning-results" class="ss-autocomplete-results"></div>
                                </div>

                                <div id="ss-pinned-list-container">
                                    <!-- Populated by JS -->
                                </div>

                                <div class="ss-empty-state" id="ss-pinning-empty"
                                    style="border: 2px dashed #e5e7eb; padding: 40px; text-align: center; border-radius: 8px; color: #6b7280; display:none;">
                                    <h3><?php esc_html_e('No pinned items', 'swiftsearch-for-algolia'); ?></h3>
                                    <p><?php esc_html_e('Search above to pin products to the top of search results.', 'swiftsearch-for-algolia'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ss-form-actions" style="margin-top: 20px;">
                    <button type="button" class="ss-btn ss-btn-primary"
                        id="ss-save-pinning"><?php esc_html_e('Save Pinned Items', 'swiftsearch-for-algolia'); ?></button>
                </div>
            </section>

            <!-- Step 8: Sync & Usage -->
            <section class="ss-step-view" id="view-sync" style="display:none;">
                <div class="ss-card">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Index Synchronization', 'swiftsearch-for-algolia'); ?></h2>
                    </div>
                    <div class="ss-card-body">
                        <div class="ss-sync-card-container" style="display: flex; gap: 30px; align-items: center;">
                            <div class="ss-sync-visual" style="flex: 0 0 150px; text-align: center;">
                                <div class="progress-circle" data-percent="0" style="margin: 0 auto 15px;">
                                    <span style="font-size: 24px;">0%</span>
                                </div>
                                <span class="ss-status-badge"
                                    style="background: #e5e7eb; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; color: #4b5563;"><?php esc_html_e('Progress', 'swiftsearch-for-algolia'); ?></span>
                            </div>
                            <div class="ss-sync-details" style="flex: 1;">
                                <h3 style="margin: 0 0 10px 0; font-size: 16px;"><?php esc_html_e('Index Synchronization', 'swiftsearch-for-algolia'); ?></h3>
                                <p style="margin: 0 0 20px 0; font-size: 14px; color: #6b7280; line-height: 1.5;">
                                    <?php esc_html_e('Synchronize your WordPress content with Algolia. This process pushes all selected post types and taxonomies to your Algolia index. Run this initial sync or whenever doing major catalog updates.', 'swiftsearch-for-algolia'); ?>
                                </p>
                                <div class="ss-actions-row" style="display: flex; gap: 15px;">
                                    <button type="button" id="ss-sync-btn"
                                        class="ss-btn ss-btn-primary"><?php esc_html_e('Index All Content', 'swiftsearch-for-algolia'); ?></button>
                                    <button type="button" id="ss-reset-btn" class="ss-btn ss-btn-danger ss-btn-outline"
                                        style="background: transparent; border: 1px solid #ef4444; color: #ef4444;"><?php esc_html_e('Clear Index', 'swiftsearch-for-algolia'); ?></button>
                                </div>
                                <p id="ss-sync-msg"
                                    style="margin-top: 15px; font-size: 13px; font-weight: 500; min-height: 20px;">
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WP-CLI Commands Panel -->
                <div class="ss-card" style="margin-top: 20px;">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('WP-CLI Terminal Commands', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Recommended for large catalogs or hosting environments with strict execution timeouts.', 'swiftsearch-for-algolia'); ?></p>
                    </div>
                    <div class="ss-card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div>
                                <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: 600; color: #374151;"><?php esc_html_e('Run Bulk Indexing', 'swiftsearch-for-algolia'); ?></h4>
                                <p style="margin: 0 0 10px 0; font-size: 12px; color: #6b7280; line-height: 1.4;"><?php esc_html_e('Synchronize content in chunks without web server timeouts.', 'swiftsearch-for-algolia'); ?></p>
                                <code style="display: block; padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; font-family: monospace; font-size: 12px; color: #111827;">wp swift-search-algolia index</code>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: 600; color: #374151;"><?php esc_html_e('Check Connection & Stats', 'swiftsearch-for-algolia'); ?></h4>
                                <p style="margin: 0 0 10px 0; font-size: 12px; color: #6b7280; line-height: 1.4;"><?php esc_html_e('Verify connectivity to Algolia and view record counts.', 'swiftsearch-for-algolia'); ?></p>
                                <code style="display: block; padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; font-family: monospace; font-size: 12px; color: #111827;">wp swift-search-algolia status</code>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: 600; color: #374151;"><?php esc_html_e('Reset Search Index', 'swiftsearch-for-algolia'); ?></h4>
                                <p style="margin: 0 0 10px 0; font-size: 12px; color: #6b7280; line-height: 1.4;"><?php esc_html_e('Clear Algolia indices and re-initialize settings.', 'swiftsearch-for-algolia'); ?></p>
                                <code style="display: block; padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; font-family: monospace; font-size: 12px; color: #111827;">wp swift-search-algolia reset</code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sync Logs Panel -->
                <div class="ss-card" style="margin-top: 20px;">
                    <div class="ss-card-header">
                        <h2><?php esc_html_e('Sync Logs', 'swiftsearch-for-algolia'); ?></h2>
                        <p><?php esc_html_e('Recent errors and status messages.', 'swiftsearch-for-algolia'); ?></p>
                    </div>
                    <div class="ss-card-body">
                        <div id="ss-sync-log-container" class="ss-log-viewer" style="min-height: 100px;">
                            <div class="ss-log-placeholder" style="color: #9ca3af; text-align: center; padding: 20px;">
                                <?php esc_html_e('No errors recorded.', 'swiftsearch-for-algolia'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone Panel -->
                <div class="ss-card" style="margin-top: 20px; border: 1px solid #fca5a5;">
                    <div class="ss-card-header" style="border-bottom: 1px solid #fee2e2; background: #fff5f5; padding: 15px 20px;">
                        <h2 style="color: #b91c1c; margin: 0; font-size: 16px; font-weight: 600;"><?php esc_html_e('Danger Zone', 'swiftsearch-for-algolia'); ?></h2>
                        <p style="color: #7f1d1d; margin: 5px 0 0 0; font-size: 13px;"><?php esc_html_e('Destructive actions to reset the plugin configuration.', 'swiftsearch-for-algolia'); ?></p>
                    </div>
                    <div class="ss-card-body" style="background: #fffafa; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 250px;">
                                <h4 style="margin: 0 0 5px 0; font-size: 14px; font-weight: 600; color: #991b1b;"><?php esc_html_e('Reset Plugin to Factory Defaults', 'swiftsearch-for-algolia'); ?></h4>
                                <p style="margin: 0; font-size: 13px; color: #7f1d1d; line-height: 1.4;"><?php esc_html_e('This will permanently delete all local settings, custom fields mapping, synonyms, facets, design styling, and Algolia credentials.', 'swiftsearch-for-algolia'); ?></p>
                            </div>
                            <div>
                                <button type="button" id="ss-factory-reset-btn" class="ss-btn ss-btn-danger" style="background: #dc2626; border-color: #dc2626; color: #fff; font-weight: 600; padding: 10px 20px; border-radius: 8px; cursor: pointer;"><?php esc_html_e('Factory Reset', 'swiftsearch-for-algolia'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>
    <div class="ss-footer"
        style="text-align: center; margin-top: 40px; padding: 20px 20px 40px; color: #6b7280; font-size: 13px; border-top: 1px solid #f3f4f6;">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
            <p style="margin: 0;">
                A <a href="https://loopstates.com" target="_blank" style="text-decoration: none; font-weight: 600; color: #632489;">Loopstates</a> Product. SwiftSearch for Algolia v<?php echo esc_html(SWIFT_SEARCH_ALGOLIA_VERSION); ?>.
            </p>
            <p style="margin: 0; color: #4b5563;">
                <?php esc_html_e('For custom integrations, enterprise solutions, or dedicated engineering support, contact', 'swiftsearch-for-algolia'); ?> 
                <a href="mailto:hello@loopstates.com" style="color: #632489; text-decoration: none; font-weight: 500;">hello@loopstates.com</a>
            </p>
            <div style="margin-top: 5px;">
                <img src="<?php echo esc_url(SWIFT_SEARCH_ALGOLIA_URL . 'assets/images/loopstates.png'); ?>" 
                     alt="Loopstates" 
                     style="height: 30px; width: auto;">
            </div>
        </div>
    </div>
</div>