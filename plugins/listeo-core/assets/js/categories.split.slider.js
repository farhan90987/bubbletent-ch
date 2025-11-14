document.addEventListener("DOMContentLoaded", function () {
  // Category data with icons (SVG paths)
  const categories = sliderData.categories || [];

  const categorySlider = document.getElementById("categorySlider");
  const prevButton = document.querySelector(".nav-button.prev");
  const nextButton = document.querySelector(".nav-button.next");
  let currentIndex = 0;
  // If current category is set, find its index
  if (sliderData.currentCategory) {
    const currentCategory = categories.find(
      (cat) => cat.slug === sliderData.currentCategory
    );
    if (currentCategory) {
      currentIndex = categories.indexOf(currentCategory);
    }
  }
  

  // Create category items
  categories.forEach((category, index) => {
    const categoryItem = document.createElement("div");
    categoryItem.className = "category-item" + (index === 0 ? " active" : "");
    categoryItem.setAttribute("data-id", category.id);
    categoryItem.setAttribute("data-slug", category.slug);
    
    // Add data attribute for listing type if present
    if (category.type === 'listing_type') {
      categoryItem.setAttribute("data-type", "listing_type");
    }
    
    categoryItem.innerHTML = `
        <div class="icon-container">${category.icon}</div>
        <div class="category-name">${category.name}</div>
    `;
    // If current category matches, set it as active
    if (index === currentIndex) {
      categoryItem.classList.add("active");
    } else {
      categoryItem.classList.remove("active");
    }
    categoryItem.addEventListener("click", function (e) {
      // Prevent default behavior and stop propagation
      e.preventDefault();
      e.stopPropagation();

      // Remove active class from all items
      document.querySelectorAll(".category-item").forEach((item) => {
        item.classList.remove("active");
      });

      // Add active class to clicked item
      this.classList.add("active");
      
      const label = this.querySelector(".category-name");
      if (label) {
        const pageTitle = document.querySelector(".page-title");
        if (pageTitle) {
          pageTitle.textContent = label.textContent;
        }
      }

      const categoryId = this.getAttribute("data-id");
      const categorySlug = this.getAttribute("data-slug");

      // Check if this is a listing type (has data attribute 'data-type')
      const isListingType = this.getAttribute("data-type") === 'listing_type';
      
      // Check if this is the "All" option
      const isAllOption = categorySlug === 'all';
      
      // Check if this is a mixed taxonomy format (contains colon)
      const isMixedTaxonomy = categorySlug && categorySlug.includes(':');
      
      // Determine if this is primarily a listing types slider or categories slider
      const hasListingTypes = categories.some(cat => cat.type === 'listing_type');
      const shouldHandleAsListingType = isListingType || (isAllOption && hasListingTypes) || isMixedTaxonomy;

      if (shouldHandleAsListingType) {
        // Handle listing type selection, "All" option, or mixed taxonomy
        console.log('Taking listing types path for:', categorySlug, {isListingType, isAllOption, hasListingTypes, isMixedTaxonomy});
        let listingTypeSelect = document.getElementById("listing_type") || 
                               document.getElementById("_listing_type") ||
                               document.querySelector("select[name='_listing_type']") ||
                               document.querySelector("select[name='listing_type']");
        
        if (listingTypeSelect) {
          // Found a visible listing type field - use it
          // For "All" option, set empty value to show all types
          listingTypeSelect.value = isAllOption ? '' : categorySlug;
          
          // Trigger change event for listing type
          const event = new Event("change", { bubbles: true });
          listingTypeSelect.dispatchEvent(event);
          
          // Also refresh Bootstrap Select if it's being used
          if (typeof jQuery !== "undefined" && typeof jQuery.fn.selectpicker === "function") {
            jQuery(listingTypeSelect).selectpicker("refresh");
          }
        } else {
          // No visible listing type field - check if page has AJAX search capability
          const resultsContainer = document.querySelector('.listeo-listings') ||
                                 document.querySelector('.listings-container') ||
                                 document.querySelector('[data-results-container]') ||
                                 document.querySelector('.search-results');
          
          if (resultsContainer && typeof jQuery !== 'undefined') {
            // Page appears to support AJAX - try to trigger it
            const form = document.querySelector('#listeo_core-search-form');
            
            if (form) {
              let hiddenInput;
              let fieldName;
              
              // Determine the correct field name based on the type of selection
              if (isMixedTaxonomy) {
                // Mixed taxonomy format - use drilldown-listing-types
                fieldName = 'drilldown-listing-types[]';
                hiddenInput = form.querySelector('input[name="drilldown-listing-types[]"]');
              } else {
                // Regular listing type - use _listing_type
                fieldName = '_listing_type';
                hiddenInput = form.querySelector('input[name="_listing_type"]');
              }
           
              if (!hiddenInput) {
                hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = fieldName;
                form.appendChild(hiddenInput);
              }
              // make sure it's enabled even if an existing one had disabled="disabled"
              hiddenInput.disabled = false; // clears the DOM property
              hiddenInput.removeAttribute("disabled"); // extra safety if the attribute is set
              // For "All" option, set empty value to show all types
              hiddenInput.value = isAllOption ? '' : categorySlug;

              var target = jQuery("#listeo-listings-container");
              target.triggerHandler("update_results", [1, false]);
             
            } 
          } 
        }
        
        // Check for listing type drilldown
        if (document.getElementById("listeo-drilldown-listing-types")) {

          const drilldown = window.ListeoDrilldown["listeo-drilldown-listing-types"];

          if (drilldown) {
            if (isAllOption) {
              // For "All" option, reset the drilldown selection
              console.log('Resetting listing type drilldown for All option');
              if (typeof drilldown.reset === 'function') {
                drilldown.reset();
              } else if (typeof drilldown.selectListingType === 'function') {
                // If no reset method, try to select with empty value
                drilldown.selectListingType('');
              }
            } else if (categorySlug) {
              // For listing types, we want to select the type and show its categories
              drilldown.selectListingType(categorySlug);
            }
          }
        }
        
        // Add listener to remove active class when listing type changes
        if (listingTypeSelect) {
          listingTypeSelect.addEventListener("change", function () {
            // Remove `.active` class from all slider items
            const sliderItems = document.querySelectorAll(
              ".category-item.active"
            );
            sliderItems.forEach((item) => item.classList.remove("active"));
          });
        }
      } else {
        // Handle category selection (original logic)
        console.log('Taking categories path for:', categorySlug, {isListingType, isAllOption, hasListingTypes, isMixedTaxonomy});
        const select = document.getElementById("tax-listing_category");
        if (select) {
          // For "All" option, set empty value to show all categories
          select.value = isAllOption ? '' : categorySlug;

          // Refresh Bootstrap Select
          if (
            typeof bootstrap !== "undefined" &&
            typeof bootstrap.Select !== "undefined"
          ) {
            bootstrap.Select.refresh();
          } else if (
            typeof jQuery !== "undefined" &&
            typeof jQuery.fn.selectpicker === "function"
          ) {
            jQuery(select).selectpicker("refresh");
          }

          // Trigger change event
          const event = new Event("change", { bubbles: true });
          select.dispatchEvent(event);

          select.addEventListener("change", function () {
            // Remove `.active` class from all slider items
            const sliderItems = document.querySelectorAll(
              ".category-item.active"
            );
            sliderItems.forEach((item) => item.classList.remove("active"));
          });
        } else {
          // No visible category field - check if page has AJAX search capability
          const resultsContainer = document.querySelector('.listeo-listings') ||
                                 document.querySelector('.listings-container') ||
                                 document.querySelector('[data-results-container]') ||
                                 document.querySelector('.search-results');
          
          if (resultsContainer && typeof jQuery !== 'undefined') {
            // Page appears to support AJAX - try to trigger it
            const form = document.querySelector('#listeo_core-search-form');
            
            if (form) {
              let hiddenInput;
              let fieldName;
              
              // Determine the correct field name based on category format
              if (categorySlug && categorySlug.includes(':')) {
                // This is a mixed taxonomy format - use drilldown-listing-types
                fieldName = 'drilldown-listing-types[]';
                hiddenInput = form.querySelector('input[name="drilldown-listing-types[]"]');
              } else {
                // This is a regular category - use tax-listing_category
                fieldName = 'tax-listing_category';
                hiddenInput = form.querySelector('input[name="tax-listing_category"]');
              }
              
              if (!hiddenInput) {
                hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = fieldName;
                form.appendChild(hiddenInput);
              }
              
              // make sure it's enabled even if an existing one had disabled="disabled"
              hiddenInput.disabled = false; // clears the DOM property
              hiddenInput.removeAttribute("disabled"); // extra safety if the attribute is set
              // For "All" option, set empty value to show all categories
              hiddenInput.value = isAllOption ? '' : categorySlug;

              var target = jQuery("#listeo-listings-container");
              target.triggerHandler("update_results", [1, false]);
             
            } 
          } 
        }
        
        // Check for single taxonomy drilldowns - only for regular taxonomies, not mixed taxonomies
        if ((categorySlug && !categorySlug.includes(':')) || isAllOption) {
          // This is a regular taxonomy term (no prefix) or the "All" option, check for specific drilldowns
          // We need to determine which taxonomy this term belongs to
          // First, try common taxonomy patterns
          const possibleTaxonomies = ['listing_category', 'service_category', 'event_category', 'rental_category', 'classifieds_category', 'region', 'listing_feature'];

          for (const taxonomy of possibleTaxonomies) {
            const drilldownId = `listeo-drilldown-tax-${taxonomy}`;
            const drilldownElement = document.getElementById(drilldownId);

            if (drilldownElement && window.ListeoDrilldown && window.ListeoDrilldown[drilldownId]) {
              const drilldown = window.ListeoDrilldown[drilldownId];

              if (isAllOption) {
                // For "All" option, reset the drilldown selection
                console.log('Resetting category drilldown for All option:', drilldownId);
                if (typeof drilldown.reset === 'function') {
                  drilldown.reset();
                } else if (typeof drilldown.selectById === 'function') {
                  drilldown.selectById('');
                }
              } else if (categoryId) {
                // For regular taxonomy terms, use the term ID
                drilldown.selectById(categoryId);
              }

              // Continue to reset all drilldowns if this is "All" option
              // Don't break early for "All" to ensure all category drilldowns are reset
              if (!isAllOption) {
                break;
              }
            }
          }
        }
      }
    });

    // Add touch event handlers to prevent slider movement only for taps (not swipes)
    let touchStartTime = 0;
    let touchStartX = 0;
    let touchStartY = 0;

    categoryItem.addEventListener("touchstart", function (e) {
      touchStartTime = Date.now();
      touchStartX = e.touches[0].clientX;
      touchStartY = e.touches[0].clientY;
    });

    categoryItem.addEventListener("touchend", function (e) {
      const touchEndTime = Date.now();
      const touchDuration = touchEndTime - touchStartTime;
      const touchEndX = e.changedTouches[0].clientX;
      const touchEndY = e.changedTouches[0].clientY;

      const deltaX = Math.abs(touchEndX - touchStartX);
      const deltaY = Math.abs(touchEndY - touchStartY);

      // If it's a quick tap with minimal movement, treat as category selection
      if (touchDuration < 300 && deltaX < 10 && deltaY < 10) {
        e.stopPropagation();
        // The click event will handle the category selection
      }
      // Otherwise, let it bubble up for potential swipe handling
    });


    categorySlider.appendChild(categoryItem);
  });

  // Navigation functionality
  let currentPosition = 0;
  const itemWidth = 90; // Item width + margin

  // Calculate visible items based on container width
  function calculateVisibleItems() {
    const containerWidth = categorySlider.parentElement.clientWidth - 80; // Subtract padding
    return Math.floor(containerWidth / itemWidth);
  }

  let visibleItems = calculateVisibleItems();
  let maxPosition = Math.max(
    0,
    Math.ceil(categories.length / visibleItems) * visibleItems - visibleItems
  );

  function updateSliderPosition() {
    categorySlider.style.transform = `translateX(-${
      currentPosition * itemWidth
    }px)`;
    updateNavigationButtons();
  }

  function updateNavigationButtons() {
    // Hide prev button if at the beginning
    if (currentPosition <= 0) {
      prevButton.classList.add("hidden");
    } else {
      prevButton.classList.remove("hidden");
    }

    // Hide next button if at the end
    if (currentPosition + visibleItems >= categories.length) {
      nextButton.classList.add("hidden");
    } else {
      nextButton.classList.remove("hidden");
    }
  }

  prevButton.addEventListener("click", function () {
    if (currentPosition > 0) {
      // Move by the number of visible items, but not less than 0
      currentPosition = Math.max(0, currentPosition - visibleItems);
      updateSliderPosition();
    }
  });

  nextButton.addEventListener("click", function () {
    if (currentPosition + visibleItems < categories.length) {
      // Move by the number of visible items, but not beyond max
      currentPosition = Math.min(
        categories.length - visibleItems,
        currentPosition + visibleItems
      );
      updateSliderPosition();
    }
  });

  // Handle window resize
  window.addEventListener("resize", function () {
    visibleItems = calculateVisibleItems();
    // Recalculate maximum position based on new visible items count
    maxPosition = Math.max(
      0,
      Math.ceil(categories.length / visibleItems) * visibleItems - visibleItems
    );

    // Make sure current position is valid after resize
    currentPosition = Math.min(
      currentPosition,
      categories.length - visibleItems
    );
    updateSliderPosition();
  });

  // Initialize slider position and navigation buttons
  updateSliderPosition();

  // Touch support
  let startX = 0;
  let endX = 0;

  categorySlider.addEventListener("touchstart", function (e) {
    startX = e.touches[0].clientX;
  });

  categorySlider.addEventListener("touchmove", function (e) {
    endX = e.touches[0].clientX;
  });

  categorySlider.addEventListener("touchend", function (e) {
    const deltaX = endX - startX;

    // Swipe right
    if (deltaX > 50 && currentPosition > 0) {
      currentPosition = Math.max(0, currentPosition - visibleItems);
      updateSliderPosition();
    }

    // Swipe left
    if (deltaX < -50 && currentPosition + visibleItems < categories.length) {
      currentPosition = Math.min(
        categories.length - visibleItems,
        currentPosition + visibleItems
      );
      updateSliderPosition();
    }

    // Reset values
    startX = 0;
    endX = 0;
  });
});
