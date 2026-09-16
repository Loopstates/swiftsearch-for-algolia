/* SwiftSearch for Algolia — Frontend Search JS (Hybrid Architecture) */
(function () {
    'use strict';

    const config = (typeof swiftSearchAlgoliaVars !== 'undefined') ? swiftSearchAlgoliaVars : ((typeof swiftSearchVars !== 'undefined') ? swiftSearchVars : null);
    if (!config) {
        return;
    }

    // Initialize Algolia Client
    let algoliaClient = null;
    if (typeof window.algoliasearch === 'function' && config.appId && config.searchApiKey) {
        try {
            algoliaClient = window.algoliasearch(config.appId, config.searchApiKey);
        } catch (e) {
            console.error('SwiftSearch: Failed to initialize Algolia client', e);
        }
    }

    function formatPrice(price) {
        const symbol = config.currencySymbol || '$';
        const position = config.currencyPosition || 'left';
        if (position === 'left') {
            return `${symbol}${price}`;
        } else if (position === 'left_space') {
            return `${symbol} ${price}`;
        } else if (position === 'right') {
            return `${price}${symbol}`;
        } else if (position === 'right_space') {
            return `${price} ${symbol}`;
        }
        return `${symbol}${price}`;
    }

    function initSearchInstance(wrapper) {
        if (!wrapper || wrapper.dataset.ssAlgoliaInit === 'true') {
            return;
        }
        wrapper.dataset.ssAlgoliaInit = 'true';

        const input = wrapper.querySelector('.ss-search-input') || wrapper.querySelector('input[type="text"]');
        const resultsContainer = wrapper.querySelector('.ss-results-container');
        const hitsContainer = wrapper.querySelector('.ss-hits') || wrapper.querySelector('[id^="ss-hits"]');
        const facetsContainer = wrapper.querySelector('.ss-facets') || wrapper.querySelector('[id^="ss-facets"]');
        const loader = wrapper.querySelector('.ss-loader');

        if (!input) return;

        // Read Data Attributes
        const limit = parseInt(wrapper.dataset.limit, 10) || 10;
        const showThumb = wrapper.dataset.thumb !== 'false';
        const showPrice = wrapper.dataset.price !== 'false';
        const showExcerpt = wrapper.dataset.excerpt === 'true';
        const layout = wrapper.dataset.layout || 'overlay';
        const browseMode = wrapper.dataset.browse === 'true' || layout === 'catalog';

        let currentPage = 1;
        let currentSort = 'relevance';

        const globalInstant = config.experience && typeof config.experience.instant_search !== 'undefined' ? config.experience.instant_search : true;
        const globalScopeTerms = config.experience && config.experience.search_scope ? config.experience.search_scope.terms : (config.indexed_taxonomies && config.indexed_taxonomies.length > 0);
        const globalScopeUsers = config.experience && config.experience.search_scope ? config.experience.search_scope.users : config.indexed_users;

        const rawInstant = wrapper.dataset.instant;
        const rawScope = wrapper.dataset.scope;

        let instantSearch = globalInstant;
        if (rawInstant && rawInstant !== 'default') {
            instantSearch = rawInstant === 'true';
        }

        let scopeTerms = globalScopeTerms;
        let scopeUsers = globalScopeUsers;
        if (rawScope && rawScope !== 'default') {
            const scopes = rawScope.split(',');
            scopeTerms = scopes.includes('terms');
            scopeUsers = scopes.includes('users');
        }

        if (layout === 'catalog') {
            scopeTerms = false;
            scopeUsers = false;
        }

        // Active Facet Filters State { fieldName: ['value1', 'value2'] }
        const activeFilters = {};

        function getFacetField(item) {
            if (item.target) return item.target;
            if (item.type === 'taxonomy') {
                if (item.source === 'category') return 'category';
                if (item.source === 'post_tag') return 'tag';
                return 'tax_' + item.source;
            }
            if (item.source === '_sku') return 'sku';
            if (item.source === '_stock_status') return 'in_stock';
            if (item.source === '_price') return 'price';
            return item.source;
        }

        let storedFacets = config.facets_config || [];
        if (!Array.isArray(storedFacets)) {
            storedFacets = Object.values(storedFacets);
        }
        const activeFacetsConfig = storedFacets.filter(f => f && (f.enabled === true || f.enabled === 'true'));
        const facetFieldNames = activeFacetsConfig.map(f => getFacetField(f)).filter(Boolean);

        let debounceTimer;
        let logTimer;

        function handleInput() {
            clearTimeout(debounceTimer);
            const query = input.value.trim();
            currentPage = 1;

            if (query.length === 0) {
                if (browseMode) {
                    if (loader) loader.style.display = 'block';
                    debounceTimer = setTimeout(function () {
                        performSearch('*');
                    }, 200);
                } else {
                    if (resultsContainer) resultsContainer.style.display = 'none';
                }
                return;
            }

            if (loader) loader.style.display = 'block';

            debounceTimer = setTimeout(function () {
                performSearch(query);
            }, 200);
        }

        if (instantSearch) {
            input.addEventListener('input', handleInput);
        } else {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleInput();
                }
            });
        }

        // Build Algolia Filter String (e.g. `(category:"Shoes" OR category:"Boots") AND post_type:"product"`)
        function buildFilterString() {
            const andParts = [];

            for (const fieldName in activeFilters) {
                const values = activeFilters[fieldName];
                if (values && values.length > 0) {
                    const orParts = values.map(v => {
                        const escaped = String(v).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
                        return `${fieldName}:"${escaped}"`;
                    });
                    if (orParts.length === 1) {
                        andParts.push(orParts[0]);
                    } else if (orParts.length > 1) {
                        andParts.push(`(${orParts.join(' OR ')})`);
                    }
                }
            }

            // Post type scoping
            let activePTs = [];
            if (wrapper.dataset.postTypes) {
                activePTs = wrapper.dataset.postTypes.split(',').map(s => s.trim()).filter(Boolean);
            } else if (config.experience && config.experience.post_types && Array.isArray(config.experience.post_types) && config.experience.post_types.length > 0) {
                activePTs = config.experience.post_types;
            }

            if (activePTs.length > 0) {
                const ptParts = activePTs.map(pt => `post_type:"${pt}"`);
                if (ptParts.length === 1) {
                    andParts.push(ptParts[0]);
                } else {
                    andParts.push(`(${ptParts.join(' OR ')})`);
                }
            }

            return andParts.join(' AND ');
        }

        function performSearch(query) {
            if (!query) {
                if (browseMode) {
                    query = '*';
                } else {
                    if (resultsContainer) resultsContainer.style.display = 'none';
                    return;
                }
            }

            if (!algoliaClient) {
                if (hitsContainer) {
                    hitsContainer.innerHTML = '<div class="ss-no-results">Algolia credentials missing or client not initialized.</div>';
                }
                if (loader) loader.style.display = 'none';
                return;
            }

            // Resolve target index name based on sort replica
            let targetIndexName = config.indexName || 'posts';
            if (currentSort !== 'relevance' && config.replicas && config.replicas[currentSort]) {
                targetIndexName = config.replicas[currentSort];
            }

            const filterString = buildFilterString();
            const searchQuery = query === '*' ? '' : query;

            // Build Queries Payload
            const queries = [];

            // 1. Primary Posts Query
            const postsParams = {
                hitsPerPage: limit,
                page: currentPage - 1, // Algolia is 0-indexed
                facets: facetFieldNames,
                highlightPreTag: '<mark>',
                highlightPostTag: '</mark>',
            };

            if (filterString) {
                postsParams.filters = filterString;
            }

            queries.push({
                indexName: targetIndexName,
                query: searchQuery,
                params: postsParams
            });

            // 2. Terms Query (Categories / Tags)
            if (scopeTerms && config.indexed_taxonomies && config.indexed_taxonomies.length > 0) {
                queries.push({
                    indexName: 'terms',
                    query: searchQuery,
                    params: {
                        hitsPerPage: 5,
                        highlightPreTag: '<mark>',
                        highlightPostTag: '</mark>',
                    }
                });
            }

            // 3. Users Query (Authors)
            if (scopeUsers && config.indexed_users) {
                queries.push({
                    indexName: 'users',
                    query: searchQuery,
                    params: {
                        hitsPerPage: 5,
                        highlightPreTag: '<mark>',
                        highlightPostTag: '</mark>',
                    }
                });
            }

            // Custom Event: Before Search
            const beforeSearchEvent = new CustomEvent('swift-search-algolia:before-search', {
                detail: { query: query, queries: queries },
                bubbles: true,
                cancelable: true
            });
            wrapper.dispatchEvent(beforeSearchEvent);
            wrapper.dispatchEvent(new CustomEvent('swift-search:before-search', {
                detail: { query: query, queries: queries },
                bubbles: true
            }));

            if (beforeSearchEvent.defaultPrevented) {
                if (loader) loader.style.display = 'none';
                return;
            }

            // Execute via Algolia Lite Client
            algoliaClient.search(queries)
                .then(response => {
                    renderHits(response, queries);
                })
                .catch(err => {
                    console.error('SwiftSearch: Algolia search failed', err);
                    if (hitsContainer) {
                        hitsContainer.innerHTML = '<div class="ss-no-results">Search currently unavailable. Please try again later.</div>';
                    }
                    if (loader) loader.style.display = 'none';
                });
        }

        function renderHits(response, queries) {
            if (resultsContainer) resultsContainer.style.display = 'flex';
            if (hitsContainer) hitsContainer.innerHTML = '';
            if (facetsContainer) {
                facetsContainer.innerHTML = '';
                facetsContainer.style.display = 'none';
            }

            const results = response && response.results ? response.results : [];
            const postsResult = results[0] || { hits: [], nbHits: 0 };

            // Render Facets
            if (postsResult && postsResult.facets && activeFacetsConfig.length > 0) {
                renderFacets(postsResult.facets);
            }

            const hasFacets = postsResult && postsResult.facets && Object.keys(postsResult.facets).length > 0;
            if (resultsContainer) {
                if (!hasFacets) {
                    resultsContainer.classList.add('ss-no-sidebar');
                } else {
                    resultsContainer.classList.remove('ss-no-sidebar');
                }
            }

            const totalFound = results.reduce((acc, r) => acc + (r.nbHits || 0), 0);

            if (totalFound === 0) {
                const queryVal = input.value.trim();
                const msg = queryVal ? `No results found for "<strong>${escapeHtml(queryVal)}</strong>"` : 'No results found.';
                if (hitsContainer) {
                    hitsContainer.innerHTML = `
                        <div class="ss-no-results">
                            <span class="dashicons dashicons-search"></span>
                            <p>${msg}</p>
                        </div>`;
                }
                logSearch(queryVal, 0);
                if (loader) loader.style.display = 'none';
                return;
            }

            const sections = {
                posts: document.createElement('div'),
                terms: document.createElement('div'),
                users: document.createElement('div'),
            };

            Object.keys(sections).forEach(k => {
                sections[k].className = `ss-section ss-section-${k}`;
                sections[k].style.display = 'none';
            });

            let hasHits = false;

            results.forEach((result, index) => {
                const queryMeta = queries[index];
                const indexName = queryMeta.indexName;
                const sectionType = indexName.includes('terms') ? 'terms' : (indexName.includes('users') ? 'users' : 'posts');
                const section = sections[sectionType];

                if (result.nbHits > 0 && result.hits && result.hits.length > 0) {
                    hasHits = true;
                    section.style.display = 'block';

                    // Client-side Pinning for posts
                    if (sectionType === 'posts' && config.pinned_items && Array.isArray(config.pinned_items) && config.pinned_items.length > 0) {
                        const pinnedMap = new Map();
                        config.pinned_items.forEach((item, pos) => {
                            pinnedMap.set(String(item.id), pos);
                        });

                        result.hits.forEach(hit => {
                            const hitId = String(hit.objectID || hit.id || hit.post_id);
                            if (pinnedMap.has(hitId)) {
                                hit.curated = true;
                                hit.pinnedPosition = pinnedMap.get(hitId);
                            }
                        });

                        result.hits.sort((a, b) => {
                            if (a.curated && b.curated) return a.pinnedPosition - b.pinnedPosition;
                            if (a.curated) return -1;
                            if (b.curated) return 1;
                            return 0;
                        });
                    }

                    // Section Header
                    let title = 'Items';
                    if (sectionType === 'posts') {
                        title = 'Results';
                    } else if (sectionType === 'terms') {
                        title = 'Categories';
                    } else if (sectionType === 'users') {
                        title = 'Authors';
                    }

                    const header = document.createElement('div');
                    header.className = 'ss-section-header';

                    if (layout === 'catalog' && sectionType === 'posts') {
                        header.innerHTML = `
                            <div class="ss-header-left">
                                <h3 class="ss-section-title">${title}</h3>
                                <span class="ss-section-count">${result.nbHits} found</span>
                            </div>
                            <div class="ss-sort-container">
                                <label for="ss-sort-by">Sort by:</label>
                                <select id="ss-sort-by" class="ss-sort-select">
                                    <option value="relevance" ${currentSort === 'relevance' ? 'selected' : ''}>Relevance</option>
                                    <option value="price_asc" ${currentSort === 'price_asc' ? 'selected' : ''}>Price: Low to High</option>
                                    <option value="price_desc" ${currentSort === 'price_desc' ? 'selected' : ''}>Price: High to Low</option>
                                    <option value="date_desc" ${currentSort === 'date_desc' ? 'selected' : ''}>Newest First</option>
                                    <option value="date_asc" ${currentSort === 'date_asc' ? 'selected' : ''}>Oldest First</option>
                                </select>
                            </div>
                        `;
                    } else {
                        header.innerHTML = `
                            <h3 class="ss-section-title">${title}</h3>
                            <span class="ss-section-count">${result.nbHits} found</span>
                        `;
                    }
                    section.appendChild(header);

                    if (layout === 'catalog' && sectionType === 'posts') {
                        const sortSelect = header.querySelector('#ss-sort-by');
                        if (sortSelect) {
                            sortSelect.addEventListener('change', (e) => {
                                currentSort = e.target.value;
                                currentPage = 1;
                                performSearch(input.value.trim());
                            });
                        }
                    }

                    const grid = document.createElement('div');
                    grid.className = 'ss-grid';
                    section.appendChild(grid);

                    result.hits.forEach((hit, hitIndex) => {
                        const card = document.createElement('div');
                        card.className = 'ss-card';
                        if (hit.curated) {
                            card.classList.add('ss-card-pinned');
                        }
                        card.style.animationDelay = `${hitIndex * 0.05}s`;

                        let html = '';
                        if (sectionType === 'posts') {
                            const isProduct = hit.post_type === 'product';
                            card.classList.add(isProduct ? 'ss-card-product' : 'ss-card-post');

                            const titleSnippet = highlightSnippet(hit, 'post_title') || escapeHtml(hit.post_title || '');
                            const excerptSnippet = hit.post_excerpt ? escapeHtml(hit.post_excerpt) : '';

                            html = `
                                <a href="${hit.permalink || '#'}" class="ss-card-link">
                                    ${showThumb ? `
                                    <div class="ss-card-image">
                                        ${hit.thumbnail_url ? `<img src="${hit.thumbnail_url}" alt="">` : '<div class="ss-placeholder"></div>'}
                                    </div>` : ''}
                                    <div class="ss-card-body">
                                        <h4 class="ss-card-title">${titleSnippet}</h4>
                                        ${(showPrice && isProduct && typeof hit.price !== 'undefined' && hit.price !== null) ? `<div class="ss-card-price">${formatPrice(hit.price)}</div>` : ''}
                                        ${(showExcerpt && excerptSnippet) ? `<p class="ss-card-excerpt">${excerptSnippet}</p>` : ''}
                                    </div>
                                </a>`;
                        } else {
                            const label = highlightSnippet(hit, sectionType === 'terms' ? 'name' : 'display_name') || escapeHtml(hit.name || hit.display_name || '');
                            const tagLabel = sectionType === 'terms' ? 'Category' : 'Author';

                            html = `
                                <a href="${hit.url || '#'}" class="ss-card-link ss-card-compact">
                                    <div class="ss-card-body">
                                        <h4 class="ss-card-title">${label}</h4>
                                        <span class="ss-card-tag">${tagLabel}</span>
                                    </div>
                                </a>`;
                        }

                        card.innerHTML = html;

                        const hitEvent = new CustomEvent('swift-search-algolia:render-hit', {
                            detail: { hit: hit, card: card, section: sectionType },
                            bubbles: true
                        });
                        wrapper.dispatchEvent(hitEvent);
                        wrapper.dispatchEvent(new CustomEvent('swift-search:render-hit', {
                            detail: { hit: hit, card: card, section: sectionType },
                            bubbles: true
                        }));

                        grid.appendChild(card);
                    });
                }
            });

            // Append sections
            Object.values(sections).forEach(s => {
                if (s.style.display !== 'none' && hitsContainer) {
                    hitsContainer.appendChild(s);
                }
            });

            if (!hasHits && hitsContainer) {
                hitsContainer.innerHTML = '<div class="ss-no-results">No results found.</div>';
            } else if (layout === 'catalog' && postsResult && postsResult.nbPages > 1) {
                renderPagination(postsResult.nbPages);
            }

            // Append "Search by Algolia" attribution badge if enabled
            if (config.showAlgoliaBadge !== false && hitsContainer) {
                renderAlgoliaBadge(hitsContainer);
            }

            logSearch(input.value.trim(), totalFound);

            // Dispatch Custom Event
            const renderedEvent = new CustomEvent('swift-search-algolia:results-rendered', {
                detail: { response: response, totalFound: totalFound },
                bubbles: true
            });
            wrapper.dispatchEvent(renderedEvent);
            wrapper.dispatchEvent(new CustomEvent('swift-search:results-rendered', {
                detail: { response: response, totalFound: totalFound },
                bubbles: true
            }));

            if (loader) loader.style.display = 'none';
        }

        function renderAlgoliaBadge(container) {
            const badge = document.createElement('div');
            badge.className = 'ss-algolia-attribution';
            badge.innerHTML = `
                <a href="https://www.algolia.com/?utm_source=swiftsearch-for-algolia&utm_medium=referral" target="_blank" rel="noopener noreferrer" title="SwiftSearch for Algolia" style="display: inline-flex; align-items: center; gap: 5px; text-decoration: none;">
                    <span style="font-size: 11px; color: var(--ss-text-muted); font-weight: 500;">Search by</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="#003DFF" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle;">
                        <path d="M12 0C5.445 0 .103 5.285.01 11.817c-.097 6.634 5.285 12.131 11.92 12.17a11.91 11.91 0 0 0 5.775-1.443.281.281 0 0 0 .052-.457l-1.122-.994a.79.79 0 0 0-.833-.14 9.693 9.693 0 0 1-3.923.77c-5.36-.067-9.692-4.527-9.607-9.888.084-5.293 4.417-9.573 9.73-9.573h9.73v17.296l-5.522-4.907a.407.407 0 0 0-.596.063 4.52 4.52 0 0 1-3.934 1.793 4.538 4.538 0 0 1-4.192-4.168 4.53 4.53 0 0 1 4.512-4.872 4.532 4.532 0 0 1 4.509 4.126c.018.205.11.397.265.533l1.438 1.275a.28.28 0 0 0 .462-.158 6.82 6.82 0 0 0 .099-1.725c-.232-3.376-2.966-6.092-6.345-6.3-3.873-.24-7.11 2.79-7.214 6.588-.1 3.7 2.933 6.892 6.634 6.974a6.75 6.75 0 0 0 4.136-1.294l7.212 6.394a.48.48 0 0 0 .797-.36V.456A.456.456 0 0 0 23.54 0Z"/>
                    </svg>
                    <span style="font-size: 13px; font-weight: 700; color: #003DFF; letter-spacing: -0.2px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">algolia</span>
                </a>
            `;
            container.appendChild(badge);
        }

        function renderPagination(totalPages) {
            const pagination = document.createElement('div');
            pagination.className = 'ss-pagination';

            const prevLink = document.createElement('a');
            prevLink.href = '#';
            prevLink.className = 'ss-page-link prev' + (currentPage === 1 ? ' disabled' : '');
            prevLink.innerHTML = '&laquo;';
            if (currentPage > 1) {
                prevLink.onclick = (e) => {
                    e.preventDefault();
                    currentPage--;
                    performSearch(input.value.trim());
                    wrapper.scrollIntoView({ behavior: 'smooth' });
                };
            }
            pagination.appendChild(prevLink);

            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, startPage + 4);
            let adjustedStart = startPage;
            if (endPage - startPage < 4) {
                adjustedStart = Math.max(1, endPage - 4);
            }

            for (let i = adjustedStart; i <= endPage; i++) {
                const pageLink = document.createElement('a');
                pageLink.href = '#';
                pageLink.className = 'ss-page-link' + (i === currentPage ? ' active' : '');
                pageLink.innerText = i;
                pageLink.onclick = (e) => {
                    e.preventDefault();
                    currentPage = i;
                    performSearch(input.value.trim());
                    wrapper.scrollIntoView({ behavior: 'smooth' });
                };
                pagination.appendChild(pageLink);
            }

            const nextLink = document.createElement('a');
            nextLink.href = '#';
            nextLink.className = 'ss-page-link next' + (currentPage === totalPages ? ' disabled' : '');
            nextLink.innerHTML = '&raquo;';
            if (currentPage < totalPages) {
                nextLink.onclick = (e) => {
                    e.preventDefault();
                    currentPage++;
                    performSearch(input.value.trim());
                    wrapper.scrollIntoView({ behavior: 'smooth' });
                };
            }
            pagination.appendChild(nextLink);

            hitsContainer.appendChild(pagination);
        }

        function renderFacets(facetsObj) {
            if (!facetsContainer) return;

            let hasVisibleFacets = false;
            facetsContainer.innerHTML = '';

            activeFacetsConfig.forEach(conf => {
                const fieldName = getFacetField(conf);
                const facetValues = facetsObj[fieldName];

                if (facetValues && typeof facetValues === 'object' && Object.keys(facetValues).length > 0) {
                    const group = document.createElement('div');
                    group.className = 'ss-facet-group';

                    const title = document.createElement('h5');
                    title.className = 'ss-facet-title';
                    title.innerText = conf.label || conf.source;
                    group.appendChild(title);

                    const list = document.createElement('ul');
                    list.className = 'ss-facet-list';

                    Object.entries(facetValues).forEach(([label, count]) => {
                        const li = document.createElement('li');
                        const isChecked = activeFilters[fieldName] && activeFilters[fieldName].includes(label);

                        li.innerHTML = `
                            <label>
                                <input type="checkbox" value="${escapeHtml(label)}" ${isChecked ? 'checked' : ''}>
                                ${escapeHtml(label)} <span class="ss-facet-count">(${count})</span>
                            </label>
                        `;

                        li.querySelector('input').addEventListener('change', (e) => {
                            toggleFilter(fieldName, label, e.target.checked);
                        });

                        list.appendChild(li);
                    });

                    group.appendChild(list);
                    facetsContainer.appendChild(group);
                    hasVisibleFacets = true;
                }
            });

            if (hasVisibleFacets) {
                facetsContainer.style.display = 'block';

                const hasFilters = Object.values(activeFilters).some(v => v && v.length > 0);
                if (hasFilters) {
                    const clearBtn = document.createElement('button');
                    clearBtn.className = 'ss-facet-clear-all';
                    clearBtn.innerText = 'Clear All Filters';
                    clearBtn.onclick = () => {
                        for (const k in activeFilters) activeFilters[k] = [];
                        currentPage = 1;
                        performSearch(input.value.trim());
                    };
                    facetsContainer.prepend(clearBtn);
                }
            }
        }

        function toggleFilter(field, value, checked) {
            if (!activeFilters[field]) activeFilters[field] = [];

            if (checked) {
                activeFilters[field].push(value);
            } else {
                activeFilters[field] = activeFilters[field].filter(v => v !== value);
            }

            currentPage = 1;
            performSearch(input.value.trim());
        }

        function highlightSnippet(hit, field) {
            if (hit && hit._highlightResult && hit._highlightResult[field] && hit._highlightResult[field].value) {
                return hit._highlightResult[field].value;
            }
            return '';
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function logSearch(query, hits) {
            clearTimeout(logTimer);
            if (!query || query === '*' || query.length < 2) return;

            logTimer = setTimeout(() => {
                const payload = { query: query, hits: hits };

                fetch(config.apiUrl + '/log', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                }).catch(() => {});
            }, 2000);
        }

        if (browseMode) {
            performSearch('*');
        }
    }

    const algoliaWrappers = document.querySelectorAll('.ss-algolia-wrapper, .ss-wrapper[data-engine="algolia"]');
    if (algoliaWrappers.length > 0) {
        algoliaWrappers.forEach(initSearchInstance);
    } else {
        document.querySelectorAll('.ss-wrapper:not([data-engine])').forEach(initSearchInstance);
    }
})();
