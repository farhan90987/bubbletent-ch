<?php 
namespace MWEW\Inc\Shortcodes;

class MW_Search_Form_Shortcode{
    public function __construct(){

        add_shortcode( 'mwew_search_form', array($this, 'output_search_form'));
    }

    public function output_search_form( $atts = array() ){
        $checkin  = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
	    $checkout = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';	
    ?>

    <form id="listing-search-form" action="#">
        <div class="row mwew-date-picker-wrap">
            <div class="form-group col-md-6">
                <label for="mwew-checkin-date"><?php echo __("Check in date", "mwew"); ?>:</label>
                <input type="text" name="check_in" class="form-control checkin-date" value="<?php echo $checkin; ?>" placeholder="<?php echo __("Arrival", 'mwew'); ?>" id="mwew-checkin-date">
            </div>
            <div class="form-group col-md-6">
                <label for="mwew-checkout-date"><?php echo __("Check out date", "mwew"); ?>:</label>
                <input type="text" name="check_out" class="form-control checkout-date" value="<?php echo $checkout; ?>" placeholder="<?php echo __("Departure", 'mwew'); ?>" id="mwew-checkout-date">
            </div>
        </div>

        <div class="form-group" style="display:none;">
            <span id="search-radius-value" style="font-weight: bolder;"></span>
            <label for="search-radius" class="form-label"><?php echo __("Radius search", "mwew"); ?></label>
            <input type="range" class="form-range" name="search_radius" id="search-radius" min="20" max="500">
        </div>

        <button type="submit" class="btn listing-search-btn" data-loading-text="<?php echo __("Processing...", "mwew"); ?>"><?php echo __("View available bubble tents", "mwew"); ?></button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rangeInput = document.getElementById('search-radius');
            const valueDisplay = document.getElementById('search-radius-value');
            const unit = '<?php echo __("km", "mwew"); ?>';

            valueDisplay.textContent = rangeInput.value + unit;

            rangeInput.addEventListener('input', function() {
                valueDisplay.textContent = this.value + unit;
            });

            //Form submissoin
            const form = document.getElementById('listing-search-form');
            if (!form) return;

            const button = form.querySelector('.listing-search-btn');
            const checkinInput = form.querySelector('.checkin-date');
            const checkoutInput = form.querySelector('.checkout-date');
            const loadingText = button.getAttribute("data-loading-text")
            
                console.log(loadingText)
           // const radiusInput = form.querySelector('#search-radius');

            const submitListingsForm = () => {
                const btnText = button.textContent;
                
                button.disabled = true;
                button.textContent = loadingText;
                document.body.classList.add('loading-listing');

                const params = new URLSearchParams(window.location.search);
                params.set('check_in', checkinInput.value);
                params.set('check_out', checkoutInput.value);
                const queryString = params.toString();
                const newUrl = `${window.location.pathname}?${queryString}`;
                window.history.pushState({}, '', newUrl);

                updateTopLevelLinksWithQuery(queryString);

                const formData = new FormData(form);
                formData.append('action', 'mwew_get_listings');
                formData.append('security', mwewPluginData.nonce);

                fetch(mwewPluginData.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const results = document.getElementById('listeo-listings-container');
                    results.classList.remove('loading');
                    results.innerHTML = data.html;

//                     const paginationContainer = document.querySelector('div.pagination-container');
//                     if (paginationContainer) {
//                         paginationContainer.innerHTML = data.pagination;
//                     }

                    if (typeof numericalRating === 'function') numericalRating();
                    if (typeof starRating === 'function') starRating();

                    const updateEvent = new CustomEvent('update_results_success');
                    results.dispatchEvent(updateEvent);

                    if (typeof listeo_core !== 'undefined' && listeo_core.map_provider === 'google') {
                        const map = document.getElementById('map');
                        if (map) {
                            // mainMap(); // Uncomment if needed
                        }
                    }

                    button.disabled = false;
                    button.textContent = btnText;
                    document.body.classList.remove('loading-listing'); 
                })
                .catch(error => {
                    console.error(error);
                    button.disabled = false;
                    button.textContent = btnText;
                    document.body.classList.remove('loading-listing'); 
                });
            };

            function updateTopLevelLinksWithQuery(queryString) {
                if (!queryString) return;

                document.querySelectorAll('.top-level-link').forEach(link => {
                    const url = new URL(link.href);
                    const params = new URLSearchParams(url.search);
                    const newParams = new URLSearchParams(queryString);

                    // Overwrite check_in and check_out in the link's URL
                    if (newParams.has('check_in')) {
                        params.set('check_in', newParams.get('check_in'));
                    }

                    if (newParams.has('check_out')) {
                        params.set('check_out', newParams.get('check_out'));
                    }

                    url.search = params.toString();
                    link.href = url.toString();
                });
            }




            // Trigger on form submit
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                submitListingsForm();
            });
            
            submitListingsForm();
            
            // Trigger on field changes
            ['input', 'change'].forEach(eventType => {
                if (checkinInput) checkinInput.addEventListener(eventType, submitListingsForm);
                if (checkoutInput) checkoutInput.addEventListener(eventType, submitListingsForm);
            });
            //if (radiusInput) radiusInput.addEventListener('input', submitListingsForm); // use input for range slider
        });


        document.addEventListener("DOMContentLoaded", function () {
            updateTopLevelLinksWithQuery(window.location.search.slice(1));
        });



    </script>

    <?php
 		$output = ob_get_clean();
 		echo $output;
	}
}