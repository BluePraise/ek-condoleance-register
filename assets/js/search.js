// Search functionality for condoleances using WordPress REST API
// TODO: Figure out Media handling for featured images, Correct comment counter and correct candle count
const searchField = document.querySelector(".js-search-field");
const searchList = document.querySelector(".js-search-list");
const condoleanceGrid = searchList?.querySelector(".condoleance-grid");
const resultsCounter = document.querySelector(".search-results-counter");

// Create loading indicator
const loadingIndicator = document.createElement("div");
loadingIndicator.className = "search-loading";
loadingIndicator.innerHTML = '<span class="spinner"></span> Zoeken...';
loadingIndicator.style.display = "none";

// Create no results message
const noResultsMessage = document.createElement("p");
noResultsMessage.className = "no-condoleances";
noResultsMessage.setAttribute("role", "status");
noResultsMessage.setAttribute("aria-live", "polite");

let debounceTimer;
let loadingTimer;
let currentRequest = null;
let isSearching = false;

// Store original items for restoration
const originalHTML = condoleanceGrid?.innerHTML || "";
const originalItems = [...document.querySelectorAll(".js-search-item")];
const totalItems = originalItems.length;

/**
 * Perform REST API search
 */
const performSearch = async function(searchTerm) {
    // Cancel previous request if still pending
    if (currentRequest) {
        currentRequest.abort();
    }

    // Clear any existing loading timer
    clearTimeout(loadingTimer);

    // Set loading state flag
    isSearching = true;

    // Only show loading indicator after 1 second
    loadingTimer = setTimeout(() => {
        if (isSearching) {
            loadingIndicator.style.display = "block";
            if (resultsCounter) {
                resultsCounter.textContent = "Zoeken...";
            }
        }
    }, 1000);

    // Create abort controller for this request
    const controller = new AbortController();
    currentRequest = controller;

    try {
        const restUrl = window.condoleanceRegister?.wpRestUrl || '/wp-json/wp/v2';
        const requestUrl = `${restUrl}/condoleren?search=${encodeURIComponent(searchTerm)}&per_page=100`;

        const response = await fetch(requestUrl, {
            signal: controller.signal,
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('REST API Error Response:', errorText);
            throw new Error(`Search request failed: ${response.status} ${response.statusText}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const responseText = await response.text();
            console.error('Non-JSON response received:', responseText);
            throw new Error('Server returned non-JSON response');
        }

        const posts = await response.json();
        const totalResults = parseInt(response.headers.get('X-WP-Total') || posts.length);

        // Fetch featured images separately for posts that have them
        await Promise.all(posts.map(async (post) => {
            if (post.featured_media && post.featured_media > 0) {
                try {
                    const mediaResponse = await fetch(`${restUrl}/media/${post.featured_media}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (mediaResponse.ok) {
                        const contentType = mediaResponse.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            const media = await mediaResponse.json();
                            post.featured_image_url = media.media_details?.sizes?.medium?.source_url || media.source_url;
                            post.featured_image_alt = media.alt_text || post.title.rendered;
                        }
                    }
                } catch (err) {
                    // Silently fail - will show placeholder instead
                }
            }
        }));

        // Clear loading timer and hide loading indicator
        clearTimeout(loadingTimer);
        loadingIndicator.style.display = "none";
        isSearching = false;

        // Render results
        renderSearchResults(posts, totalResults, searchTerm);

    } catch (error) {
        if (error.name === 'AbortError') {
            // Request was cancelled, do nothing
            return;
        }

        console.error('Search error:', error);
        clearTimeout(loadingTimer);
        loadingIndicator.style.display = "none";
        isSearching = false;

        if (resultsCounter) {
            resultsCounter.textContent = "Er is een fout opgetreden";
        }
    }
};

/**
 * Render search results
 */
const renderSearchResults = function(posts, totalResults, searchTerm) {
    // Clear existing content
    if (condoleanceGrid) {
        condoleanceGrid.innerHTML = "";
    }

    // Remove existing no results message
    const existingMessage = searchList?.querySelector(".no-condoleances");
    if (existingMessage) {
        existingMessage.remove();
    }

    if (posts.length === 0) {
        // No results found
        noResultsMessage.textContent = "Geen resultaten gevonden";
        searchList?.insertBefore(noResultsMessage, searchList.firstChild);
        if (resultsCounter) {
            resultsCounter.textContent = "";
        }
        return;
    }

    // Update counter
    if (resultsCounter) {
        if (totalResults > 100) {
            resultsCounter.textContent = `${posts.length} van ${totalResults}+ resultaten (eerste 100 getoond)`;
        } else {
            resultsCounter.textContent = `${posts.length} ${posts.length === 1 ? 'resultaat' : 'resultaten'}`;
        }
    }

    // Render each post
    posts.forEach(post => {
        const article = createCondoleanceCard(post);
        condoleanceGrid?.appendChild(article);
    });
};

/**
 * Create a condoleance card element from post data
 */
const createCondoleanceCard = function(post) {
    const article = document.createElement('article');
    article.id = `post-${post.id}`;
    article.className = 'condoleance-card post-' + post.id + ' condoleance type-condoleance status-publish hentry';

    // Extract metadata
    const birthDate = post.meta?.condoleance_birth_date || '';
    const deathDate = post.meta?.condoleance_death_date || '';
    const candlesData = post.meta?.condoleance_candles_data || {};
    const candleCount = candlesData.count || 0;

    // Build card HTML - matching render_condoleance_card() markup exactly
    let html = `
        <h3 class="obituary-name">
            <a href="${post.link}" class="js-search-item">${post.title.rendered}</a>
        </h3>
    `;

    // Obituary Dates
    if (birthDate || deathDate) {
        html += '<div class="card-dates">';
        if (birthDate && deathDate) {
            html += `<span class="date-range">${birthDate} - ${deathDate}</span>`;
        } else if (deathDate) {
            html += `<span class="death-date">Overleden: ${deathDate}</span>`;
        }
        html += '</div>';
    }

    // Excerpt
    if (post.excerpt?.rendered) {
        html += `<div class="card-excerpt">${post.excerpt.rendered}</div>`;
    }

    // Featured image
    if (post.featured_image_url) {
        const altText = post.featured_image_alt || post.title.rendered;
        html += `
            <a href="${post.link}" class="card-image-link">
                <img src="${post.featured_image_url}" class="card-image" alt="${altText}">
            </a>
        `;
    } else {
        html += `
            <div class="card-image-placeholder">
                <span class="placeholder-icon">🕊️</span>
            </div>
        `;
    }

    // Card Meta
    html += '<div class="card-meta">';

    // Candle count
    html += '<span class="candle-count"><span class="candle-icon">🕯️</span>';
    if (candleCount < 1) {
        html += 'Er zijn nog geen kaarsjes aangestoken';
    } else if (candleCount === 1) {
        html += 'Er is 1 kaarsje aangestoken';
    } else {
        html += `Er zijn ${candleCount} kaarsjes aangestoken`;
    }
    html += '</span>';

    // Comment count (hardcoded for now since API doesn't return it easily)
    html += '<span class="comment-count"><span class="comment-icon">💬</span>';
    html += 'Er zijn nog geen berichten geplaatst';
    html += '</span>';

    // Link button
    html += `<a href="${post.link}" class="card-link button">Condoleer</a>`;

    html += '</div>'; // Close card-meta

    article.innerHTML = html;
    return article;
};

/**
 * Restore original content
 */
const restoreOriginalContent = function() {
    if (condoleanceGrid) {
        condoleanceGrid.innerHTML = originalHTML;
    }

    // Remove no results message
    const existingMessage = searchList?.querySelector(".no-condoleances");
    if (existingMessage) {
        existingMessage.remove();
    }

    // Clear counter
    if (resultsCounter) {
        resultsCounter.textContent = "";
    }
};

// Initialize search functionality
if (searchField && searchList) {
    // Insert loading indicator
    searchField.parentNode?.insertBefore(loadingIndicator, searchField.nextSibling);

    searchField.addEventListener("input", function(e) {
        const searchTerm = e.target.value.trim();

        // Clear previous debounce timer
        clearTimeout(debounceTimer);

        // If empty, restore original content
        if (searchTerm.length === 0) {
            restoreOriginalContent();
            return;
        }

        // If less than 3 characters, wait
        if (searchTerm.length < 3) {
            if (resultsCounter) {
                resultsCounter.textContent = "Type minimaal 3 tekens om te zoeken";
            }
            return;
        }

        // Debounce the search
        debounceTimer = setTimeout(function() {
            performSearch(searchTerm);
        }, 300);
    });
}