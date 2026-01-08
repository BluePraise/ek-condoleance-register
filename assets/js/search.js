// all the search items
const searchItems = [...document.querySelectorAll(".js-search-item")];
const searchList = document.querySelector(".js-search-list");
const searchField = document.querySelector(".js-search-field");
const noResultsMessage = document.createElement("p");
noResultsMessage.className = "no-condoleances";
noResultsMessage.setAttribute("role", "status");
noResultsMessage.setAttribute("aria-live", "polite");

// the values from the search
const filters = {
    searchText: ""
};

let debounceTimer;

const renderResults = function (searchItems, filters) {
    // filter through all the search items and match it with the values input
    const filteredItems = searchItems.filter(function (item) {
        return item.textContent
            .toLowerCase()
            .includes(filters.searchText.toLowerCase());
    });

    // Show/hide cards based on filter
    searchItems.forEach(function (item) {
        const card = item.closest(".condoleance-card");
        if (card) {
            if (filteredItems.includes(item)) {
                card.style.display = "";
            } else {
                card.style.display = "none";
            }
        }
    });

    // Remove existing no results message
    const existingMessage = searchList.querySelector(".no-condoleances");
    if (existingMessage) {
        existingMessage.remove();
    }

    // if no results are found show a message
    if (filteredItems.length === 0) {
        noResultsMessage.textContent = "☹️ Geen resultaten gevonden";
        searchList.insertBefore(noResultsMessage, searchList.firstChild);
    }
};


if (searchField) {
    // Add accessibility attributes
    searchField.setAttribute("aria-label", "Zoek in condoleances");

    searchField.addEventListener("input", function (e) {
        filters.searchText = e.target.value;

        // Clear previous debounce timer
        clearTimeout(debounceTimer);

        // if user types more than 2 characters a result will be shown
        if (e.target.value.length > 2) {
            debounceTimer = setTimeout(function() {
                renderResults(searchItems, filters);
            }, 300);
        }
        // if the input is 2 characters or less, show all results
        else {
            // Show all cards immediately
            searchItems.forEach(function (item) {
                const card = item.closest(".condoleance-card");
                if (card) {
                    card.style.display = "";
                }
            });
            // Remove no results message
            const existingMessage = searchList.querySelector(".no-condoleances");
            if (existingMessage) {
                existingMessage.remove();
            }
        }
    });
}