<?php
/**
 * Listeo Core Regions Importer
 *
 * Integrated regions importer functionality for Listeo Core
 * Safely replaces the standalone regions-importer plugin
 *
 * @package Listeo Core
 * @since 1.9.45
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Ensure is_plugin_active function is available
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

// Safety check: Don't load if standalone regions importer plugin is active
if (class_exists('Dynamic_Regions_Importer')) {
    add_action('admin_notices', function() {
        ?>
        <div class="notice notice-error">
            <p><strong>Listeo Core:</strong> <?php _e('Conflict detected! Please deactivate the standalone "Regions Importer" plugin as this functionality is now integrated into Listeo Core.', 'listeo_core'); ?></p>
        </div>
        <?php
    });
    return; // Stop loading this file
}

/**
 * Listeo Core Regions Importer Class
 */
class Listeo_Core_Regions_Importer
{
    // The URL to your secure proxy server script.
    const PROXY_API_URL = 'https://purethemes.net/import-regions';

    private $notice = '';
    private $notice_type = 'success';

    /**
     * The single instance of the class.
     *
     * @var self
     * @since  1.9.45
     */
    private static $_instance = null;

    /**
     * Allows for accessing single instance of class. Class should only be constructed once per call.
     *
     * @since  1.9.45
     * @static
     * @return self Main instance.
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor - only initialize if we're in admin and no conflicts
     */
    public function __construct()
    {
        // Only load in admin
        if (!is_admin()) {
            return;
        }

        // Double-check for conflicts - both class existence and plugin activation
        if (class_exists('Dynamic_Regions_Importer') || 
            (function_exists('is_plugin_active') && is_plugin_active('regions-importer/regions-import.php'))) {
            add_action('admin_notices', array($this, 'show_conflict_notice'));
            return;
        }

        add_action('admin_init', array($this, 'handle_import'));
        add_action('admin_notices', array($this, 'show_admin_notice'));
    }

    /**
     * Show conflict notice if standalone plugin is detected
     */
    public function show_conflict_notice()
    {
        ?>
        <div class="notice notice-error">
            <p><strong><?php _e('Listeo Core - Conflict Detected!', 'listeo_core'); ?></strong></p>
            <p><?php _e('The standalone "Regions Importer" plugin is still active. Please deactivate it as this functionality is now built into Listeo Core.', 'listeo_core'); ?></p>
            <p><?php _e('Go to Plugins → Installed Plugins and deactivate "Regions Importer" to use the integrated version.', 'listeo_core'); ?></p>
        </div>
        <?php
    }

    /**
     * Render the regions importer page
     */
    public function render_import_page()
    {
        // Final safety check
        if (class_exists('Dynamic_Regions_Importer') && !defined('LISTEO_REGIONS_IMPORTER_INTEGRATED')) {
            echo '<div class="wrap"><div class="notice notice-error"><p>' . __('Cannot load regions importer due to plugin conflict. Please deactivate the standalone Regions Importer plugin.', 'listeo_core') . '</p></div></div>';
            return;
        }

        $countries_to_display = $this->get_countries_from_installed_languages();
        $current_locale = get_locale();
        ?>
        <div class="wrap">
            <h1><?php _e('Import Country Regions', 'listeo_core'); ?></h1>
            <div class="dri-import-box">
                <?php if (!empty($countries_to_display)) : ?>
                    <p><?php _e('Select a country and what you\'d like to import. The plugin will create the appropriate hierarchy for you.', 'listeo_core'); ?></p>
                    <form id="region-importer-form" method="post" action="">
                        <?php wp_nonce_field('listeo_import_regions_nonce', 'listeo_regions_nonce'); ?>
                        <input type="hidden" name="country_locale" id="country_locale" value="" />
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row"><label for="country_to_import"><?php _e('Select Country', 'listeo_core'); ?> <br> <span style="font-size: 13px; font-weight: 400; color: #888"><?php _e('Based on available WordPress languages.', 'listeo_core'); ?></span></label></th>
                                <td>
                                    <select id="country_to_import" name="country_to_import" style="min-width: 250px;">
                                        <?php foreach ($countries_to_display as $country) : 
                                            $is_selected = ($country['locale'] === $current_locale) ? 'selected="selected"' : '';
                                        ?>
                                            <option value="<?php echo esc_attr($country['name']); ?>" data-locale="<?php echo esc_attr($country['locale']); ?>" <?php echo $is_selected; ?>><?php echo esc_html($country['display']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <!-- Import Level Choice -->
                            <tr valign="top">
                                <th scope="row"><label><?php _e('Import Level', 'listeo_core'); ?></label></th>
                                <td>
                                    <fieldset>
                                        <label><input type="radio" name="import_level" value="regions_and_cities" checked="checked"> <span><?php _e('Regions + 5 cities for each', 'listeo_core'); ?></span></label>
                                        <label><input type="radio" name="import_level" value="regions_only"> <span><?php _e('Regions Only', 'listeo_core'); ?></span></label>
                                    </fieldset>
                                </td>
                            </tr>
                            <!-- Clean Up Option -->
                            <tr valign="top">
                                <th scope="row"><label><?php _e('Clean Up', 'listeo_core'); ?></label></th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="checkbox" name="remove_existing_regions" id="remove_existing_regions" value="1"> 
                                            <span><?php _e('Remove existing regions before importing', 'listeo_core'); ?></span>
                                        </label>
                                        <div id="remove-warning" class="dri-remove-warning" style="display: none;">
                                            <strong><?php _e('Warning:', 'listeo_core'); ?></strong> <?php _e('This will permanently delete all existing regions and cities from your site before importing new ones. This action cannot be undone.', 'listeo_core'); ?>
                                        </div>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button(__('Import Regions', 'listeo_core'), 'primary', 'listeo_import_regions'); ?>
                    </form>
                    <div id="importer-loading" style="display: none; border: none; background:rgb(237, 244, 255); border-radius: 5px;">
                        <div style="display: flex;">
                            <span class="spinner is-active"></span>
                            <p><strong><?php _e('Importing...', 'listeo_core'); ?></strong> <?php _e('This may take a few moments. Please don\'t close this window.', 'listeo_core'); ?></p>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                <?php else : ?>
                    <div class="notice notice-warning"><p><?php _e('No countries available for import. Please install additional languages on your WordPress site.', 'listeo_core'); ?></p></div>
                <?php endif; ?>
            </div>
        </div>

        <style>
            .dri-import-box { background: #fff; border: none; box-shadow: 0 2px 10px rgba(0, 0, 0, .08); padding: 20px 30px; margin-top: 15px; border-radius: 5px; max-width: 500px; }
            .dri-import-box p { font-size: 14px; }
            .dri-import-box .form-table th { padding-left: 0; }
            .dri-import-box .form-table td { padding-right: 0; }
            .dri-import-box fieldset { border: none; padding: 0; margin: 0; }
            .dri-import-box fieldset label { display: block; margin-bottom: 8px; }
            .dri-import-box fieldset input { margin-right: 5px; }
            .dri-remove-warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 8px 12px; border-radius: 4px; margin-top: 8px; font-size: 13px; }
            #importer-loading { display:none; margin-top: 15px; padding: 12px; border: 1px solid #c3c4c7; background-color: #fff; }
            #importer-loading .spinner { float: left; margin-right: 10px; }
            #importer-loading p { margin: 0; float: left; }
        </style>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('region-importer-form');
                const select = document.getElementById('country_to_import');
                const localeInput = document.getElementById('country_locale');
                const submitButton = document.getElementById('submit');
                const loadingDiv = document.getElementById('importer-loading');
                const removeCheckbox = document.getElementById('remove_existing_regions');
                const removeWarning = document.getElementById('remove-warning');
                
                if (select.options.length > 0) { 
                    localeInput.value = select.options[select.selectedIndex].getAttribute('data-locale'); 
                }
                
                select.addEventListener('change', function() { 
                    localeInput.value = this.options[this.selectedIndex].getAttribute('data-locale'); 
                });
                
                // Show/hide warning based on checkbox state
                removeCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        removeWarning.style.display = 'block';
                    } else {
                        removeWarning.style.display = 'none';
                    }
                });
                
                form.addEventListener('submit', function() { 
                    loadingDiv.style.display = 'block'; 
                    submitButton.value = '<?php _e('Importing...', 'listeo_core'); ?>'; 
                    submitButton.disabled = true; 
                });
            });
        </script>
        <?php
    }

    /**
     * Handle form submission and import process
     */
    public function handle_import()
    {
        if (isset($_POST['listeo_import_regions']) && 
            isset($_POST['listeo_regions_nonce']) && 
            wp_verify_nonce($_POST['listeo_regions_nonce'], 'listeo_import_regions_nonce') && 
            current_user_can('manage_options')) {
            
            $selected_country_en = sanitize_text_field($_POST['country_to_import']);
            $selected_locale = sanitize_text_field($_POST['country_locale']);
            $import_level = isset($_POST['import_level']) ? sanitize_text_field($_POST['import_level']) : 'regions_and_cities';
            $remove_existing = isset($_POST['remove_existing_regions']) && $_POST['remove_existing_regions'] === '1';
            
            // Remove existing regions if requested
            if ($remove_existing) {
                $this->remove_existing_regions();
            }
            
            $regions_data = $this->fetch_regions_from_proxy($selected_country_en, $selected_locale, $import_level);

            if (is_wp_error($regions_data) || empty($regions_data)) {
                $this->notice = is_wp_error($regions_data) ? 'Error: ' . $regions_data->get_error_message() : __('Error: The import service returned empty data.', 'listeo_core');
                $this->notice_type = 'error';
                return;
            }
            
            // Unwrapping logic only applies if we're expecting cities.
            if ($import_level === 'regions_and_cities' && count($regions_data) === 1) {
                $first_key = key($regions_data);
                if (strcasecmp($first_key, $selected_country_en) == 0) {
                    $regions_data = reset($regions_data);
                }
            }

            $this->import_regions($regions_data);
            
            if ($this->notice_type !== 'error') {
                $cleanup_message = $remove_existing ? __(' (existing regions were removed first)', 'listeo_core') : '';
                $this->notice = __('Regions and cities have been successfully imported!', 'listeo_core') . $cleanup_message;
            }
        }
    }

    /**
     * Remove all existing regions and their children
     */
    private function remove_existing_regions()
    {
        $terms = get_terms(array(
            'taxonomy' => 'region',
            'hide_empty' => false,
            'fields' => 'ids'
        ));

        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term_id) {
                wp_delete_term($term_id, 'region');
            }
        }
    }

    /**
     * Import regions data into WordPress taxonomy
     */
    private function import_regions($data)
    {
        if (empty($data) || !is_array($data)) {
            $this->notice = __('Import failed: The data from the AI was not in the expected array format.', 'listeo_core');
            $this->notice_type = 'error';
            return;
        }

        foreach ($data as $key => $value) {
            // CASE 1: Data is a simple array of region names (from 'regions_only')
            if (is_int($key) && is_string($value)) {
                $clean_region_name = trim(str_replace('_', ' ', $value));
                if (!empty($clean_region_name)) {
                    wp_insert_term(sanitize_text_field($clean_region_name), 'region'); // No parent
                }
            }
            // CASE 2: Data is an associative array of regions and cities
            else if (is_string($key) && is_array($value)) {
                $clean_region_name = trim(str_replace('_', ' ', $key));
                if (empty($clean_region_name)) { continue; }

                $state_term = wp_insert_term(sanitize_text_field($clean_region_name), 'region');
                if (is_wp_error($state_term)) { continue; }
                
                $state_term_id = $state_term['term_id'];
                $cities = $value;

                foreach ($cities as $city_name) {
                    if (is_string($city_name)) {
                        $clean_city_name = trim($city_name);
                        if (!empty($clean_city_name)) {
                            wp_insert_term(sanitize_text_field($clean_city_name), 'region', ['parent' => $state_term_id]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Fetch regions data from proxy API
     */
    private function fetch_regions_from_proxy($country, $locale, $import_level)
    {
        $body = json_encode(['country' => $country, 'locale' => $locale, 'import_level' => $import_level]);
        $response = wp_remote_post(self::PROXY_API_URL, [
            'method'  => 'POST',
            'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
            'body'    => $body,
            'timeout' => 90
        ]);

        if (is_wp_error($response)) { 
            return new WP_Error('proxy_request_failed', __('Request to the import service failed.', 'listeo_core')); 
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $error_body = json_decode(wp_remote_retrieve_body($response), true);
            $message = $error_body['error'] ?? __('An unknown error occurred.', 'listeo_core');
            return new WP_Error('proxy_error', __('Import service error: ', 'listeo_core') . esc_html($message) . ' (Code: ' . esc_html($response_code) . ').');
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (json_last_error() !== JSON_ERROR_NONE) { 
            return new WP_Error('json_decode_error', __('Invalid data received from the import service.', 'listeo_core')); 
        }
        
        return $data;
    }

    /**
     * Get countries based on all available WordPress languages
     */
    private function get_countries_from_installed_languages()
    {
        // Get all available WordPress locales (not just installed ones)
        $all_locales = $this->get_all_wordpress_locales();
        $current_locale = get_locale();
        
        if (empty($all_locales)) {
            // Fallback to installed languages if we can't get all locales
            $all_locales = get_available_languages();
            $all_locales[] = get_locale();
            $all_locales = array_unique($all_locales);
        }
        
        // Manual mapping of locales to countries - more reliable than Locale::getRegion()
        $locale_to_country = [
            // English speaking countries
            'en_US' => ['name' => 'United States', 'display' => 'United States'],
            'en_GB' => ['name' => 'United Kingdom', 'display' => 'United Kingdom'],
            'en_AU' => ['name' => 'Australia', 'display' => 'Australia'],
            'en_CA' => ['name' => 'Canada', 'display' => 'Canada'],
            'en_NZ' => ['name' => 'New Zealand', 'display' => 'New Zealand'],
            'en_ZA' => ['name' => 'South Africa', 'display' => 'South Africa'],
            
            // Spanish speaking countries
            'es_ES' => ['name' => 'Spain', 'display' => 'Spain'],
            'es_MX' => ['name' => 'Mexico', 'display' => 'Mexico'],
            'es_AR' => ['name' => 'Argentina', 'display' => 'Argentina'],
            'es_CL' => ['name' => 'Chile', 'display' => 'Chile'],
            'es_CO' => ['name' => 'Colombia', 'display' => 'Colombia'],
            'es_PE' => ['name' => 'Peru', 'display' => 'Peru'],
            'es_VE' => ['name' => 'Venezuela', 'display' => 'Venezuela'],
            'es_CR' => ['name' => 'Costa Rica', 'display' => 'Costa Rica'],
            'es_DO' => ['name' => 'Dominican Republic', 'display' => 'Dominican Republic'],
            'es_EC' => ['name' => 'Ecuador', 'display' => 'Ecuador'],
            'es_GT' => ['name' => 'Guatemala', 'display' => 'Guatemala'],
            'es_UY' => ['name' => 'Uruguay', 'display' => 'Uruguay'],
            'es_PR' => ['name' => 'Puerto Rico', 'display' => 'Puerto Rico'],
            
            // French speaking countries
            'fr_FR' => ['name' => 'France', 'display' => 'France'],
            'fr_CA' => ['name' => 'Canada', 'display' => 'Canada (French)'],
            'fr_BE' => ['name' => 'Belgium', 'display' => 'Belgium (French)'],
            
            // German speaking countries
            'de_DE' => ['name' => 'Germany', 'display' => 'Germany'],
            'de_DE_formal' => ['name' => 'Germany', 'display' => 'Germany (Formal)'],
            'de_AT' => ['name' => 'Austria', 'display' => 'Austria'],
            'de_CH' => ['name' => 'Switzerland', 'display' => 'Switzerland (German)'],
            'de_CH_informal' => ['name' => 'Switzerland', 'display' => 'Switzerland (German Informal)'],
            
            // Portuguese speaking countries
            'pt_PT' => ['name' => 'Portugal', 'display' => 'Portugal'],
            'pt_BR' => ['name' => 'Brazil', 'display' => 'Brazil'],
            'pt_AO' => ['name' => 'Angola', 'display' => 'Angola'],
            'pt_PT_ao90' => ['name' => 'Portugal', 'display' => 'Portugal (AO90)'],
            
            // Dutch speaking countries
            'nl_NL' => ['name' => 'Netherlands', 'display' => 'Netherlands'],
            'nl_BE' => ['name' => 'Belgium', 'display' => 'Belgium (Dutch)'],
            'nl_NL_formal' => ['name' => 'Netherlands', 'display' => 'Netherlands (Formal)'],
            
            // Asian countries
            'ja' => ['name' => 'Japan', 'display' => 'Japan'],
            'ko_KR' => ['name' => 'South Korea', 'display' => 'South Korea'],
            'zh_CN' => ['name' => 'China', 'display' => 'China'],
            'zh_TW' => ['name' => 'Taiwan', 'display' => 'Taiwan'],
            'zh_HK' => ['name' => 'Hong Kong', 'display' => 'Hong Kong'],
            'th' => ['name' => 'Thailand', 'display' => 'Thailand'],
            'vi' => ['name' => 'Vietnam', 'display' => 'Vietnam'],
            'id_ID' => ['name' => 'Indonesia', 'display' => 'Indonesia'],
            'ms_MY' => ['name' => 'Malaysia', 'display' => 'Malaysia'],
            'tl' => ['name' => 'Philippines', 'display' => 'Philippines'],
            'ceb' => ['name' => 'Philippines', 'display' => 'Philippines (Cebuano)'],
            'jv_ID' => ['name' => 'Indonesia', 'display' => 'Indonesia (Javanese)'],
            'my_MM' => ['name' => 'Myanmar', 'display' => 'Myanmar'],
            
            // South Asian countries
            'hi_IN' => ['name' => 'India', 'display' => 'India'],
            'bn_BD' => ['name' => 'Bangladesh', 'display' => 'Bangladesh'],
            'ur' => ['name' => 'Pakistan', 'display' => 'Pakistan'],
            'ta_IN' => ['name' => 'India', 'display' => 'India (Tamil)'],
            'ta_LK' => ['name' => 'Sri Lanka', 'display' => 'Sri Lanka (Tamil)'],
            'te' => ['name' => 'India', 'display' => 'India (Telugu)'],
            'ml_IN' => ['name' => 'India', 'display' => 'India (Malayalam)'],
            'kn' => ['name' => 'India', 'display' => 'India (Kannada)'],
            'gu' => ['name' => 'India', 'display' => 'India (Gujarati)'],
            'pa_IN' => ['name' => 'India', 'display' => 'India (Punjabi)'],
            'mr' => ['name' => 'India', 'display' => 'India (Marathi)'],
            'ne_NP' => ['name' => 'Nepal', 'display' => 'Nepal'],
            'si_LK' => ['name' => 'Sri Lanka', 'display' => 'Sri Lanka'],
            'as' => ['name' => 'India', 'display' => 'India (Assamese)'],
            
            // European countries
            'it_IT' => ['name' => 'Italy', 'display' => 'Italy'],
            'pl_PL' => ['name' => 'Poland', 'display' => 'Poland'],
            'cs_CZ' => ['name' => 'Czech Republic', 'display' => 'Czech Republic'],
            'sk_SK' => ['name' => 'Slovakia', 'display' => 'Slovakia'],
            'hu_HU' => ['name' => 'Hungary', 'display' => 'Hungary'],
            'ro_RO' => ['name' => 'Romania', 'display' => 'Romania'],
            'bg_BG' => ['name' => 'Bulgaria', 'display' => 'Bulgaria'],
            'hr' => ['name' => 'Croatia', 'display' => 'Croatia'],
            'sl_SI' => ['name' => 'Slovenia', 'display' => 'Slovenia'],
            'et' => ['name' => 'Estonia', 'display' => 'Estonia'],
            'lv' => ['name' => 'Latvia', 'display' => 'Latvia'],
            'lt_LT' => ['name' => 'Lithuania', 'display' => 'Lithuania'],
            'ru_RU' => ['name' => 'Russia', 'display' => 'Russia'],
            'uk' => ['name' => 'Ukraine', 'display' => 'Ukraine'],
            'bel' => ['name' => 'Belarus', 'display' => 'Belarus'],
            'bs_BA' => ['name' => 'Bosnia and Herzegovina', 'display' => 'Bosnia and Herzegovina'],
            'mk_MK' => ['name' => 'North Macedonia', 'display' => 'North Macedonia'],
            'sr_RS' => ['name' => 'Serbia', 'display' => 'Serbia'],
            'sq' => ['name' => 'Albania', 'display' => 'Albania'],
            'el' => ['name' => 'Greece', 'display' => 'Greece'],
            
            // Nordic countries
            'sv_SE' => ['name' => 'Sweden', 'display' => 'Sweden'],
            'da_DK' => ['name' => 'Denmark', 'display' => 'Denmark'],
            'nb_NO' => ['name' => 'Norway', 'display' => 'Norway'],
            'nn_NO' => ['name' => 'Norway', 'display' => 'Norway (Nynorsk)'],
            'fi' => ['name' => 'Finland', 'display' => 'Finland'],
            'is_IS' => ['name' => 'Iceland', 'display' => 'Iceland'],
            
            // Middle Eastern countries
            'ar' => ['name' => 'Saudi Arabia', 'display' => 'Saudi Arabia'],
            'ary' => ['name' => 'Morocco', 'display' => 'Morocco'],
            'he_IL' => ['name' => 'Israel', 'display' => 'Israel'],
            'tr_TR' => ['name' => 'Turkey', 'display' => 'Turkey'],
            'fa_IR' => ['name' => 'Iran', 'display' => 'Iran'],
            'fa_AF' => ['name' => 'Afghanistan', 'display' => 'Afghanistan'],
            'ps' => ['name' => 'Afghanistan', 'display' => 'Afghanistan (Pashto)'],
            'ckb' => ['name' => 'Iraq', 'display' => 'Iraq (Kurdish)'],
            'haz' => ['name' => 'Afghanistan', 'display' => 'Afghanistan (Hazaragi)'],
            'snd' => ['name' => 'Pakistan', 'display' => 'Pakistan (Sindhi)'],
            'skr' => ['name' => 'Pakistan', 'display' => 'Pakistan (Saraiki)'],
            
            // Central Asian countries
            'kk' => ['name' => 'Kazakhstan', 'display' => 'Kazakhstan'],
            'kir' => ['name' => 'Kyrgyzstan', 'display' => 'Kyrgyzstan'],
            'uz_UZ' => ['name' => 'Uzbekistan', 'display' => 'Uzbekistan'],
            'mn' => ['name' => 'Mongolia', 'display' => 'Mongolia'],
            'tt_RU' => ['name' => 'Russia', 'display' => 'Russia (Tatar)'],
            'sah' => ['name' => 'Russia', 'display' => 'Russia (Sakha)'],
            'ug_CN' => ['name' => 'China', 'display' => 'China (Uyghur)'],
            
            // Caucasus region
            'az' => ['name' => 'Azerbaijan', 'display' => 'Azerbaijan'],
            'azb' => ['name' => 'Iran', 'display' => 'Iran (South Azerbaijani)'],
            'ka_GE' => ['name' => 'Georgia', 'display' => 'Georgia'],
            'hy' => ['name' => 'Armenia', 'display' => 'Armenia'],
            
            // African countries
            'sw' => ['name' => 'Tanzania', 'display' => 'Tanzania'],
            'am' => ['name' => 'Ethiopia', 'display' => 'Ethiopia'],
            'af' => ['name' => 'South Africa', 'display' => 'South Africa (Afrikaans)'],
            'rhg' => ['name' => 'Bangladesh', 'display' => 'Bangladesh (Rohingya)'],
            
            // Celtic languages
            'cy' => ['name' => 'United Kingdom', 'display' => 'United Kingdom (Welsh)'],
            'gd' => ['name' => 'United Kingdom', 'display' => 'United Kingdom (Scottish Gaelic)'],
            'ga' => ['name' => 'Ireland', 'display' => 'Ireland'],
            
            // Regional European languages
            'eu' => ['name' => 'Spain', 'display' => 'Spain (Basque)'],
            'ca' => ['name' => 'Spain', 'display' => 'Spain (Catalan)'],
            'gl_ES' => ['name' => 'Spain', 'display' => 'Spain (Galician)'],
            'oci' => ['name' => 'France', 'display' => 'France (Occitan)'],
            'fur' => ['name' => 'Italy', 'display' => 'Italy (Friulian)'],
            'fy' => ['name' => 'Netherlands', 'display' => 'Netherlands (Frisian)'],
            'dsb' => ['name' => 'Germany', 'display' => 'Germany (Lower Sorbian)'],
            'hsb' => ['name' => 'Germany', 'display' => 'Germany (Upper Sorbian)'],
            'szl' => ['name' => 'Poland', 'display' => 'Poland (Silesian)'],
            'mt_MT' => ['name' => 'Malta', 'display' => 'Malta'],
            
            // Special regions and languages
            'bo' => ['name' => 'Tibet', 'display' => 'Tibet'],
            'dzo' => ['name' => 'Bhutan', 'display' => 'Bhutan'],
            'lo' => ['name' => 'Laos', 'display' => 'Laos'],
            'km' => ['name' => 'Cambodia', 'display' => 'Cambodia'],
            'kab' => ['name' => 'Algeria', 'display' => 'Algeria (Kabyle)'],
            'eo' => ['name' => 'International', 'display' => 'International (Esperanto)'],
            'arg' => ['name' => 'Spain', 'display' => 'Spain (Aragonese)'],
            'tah' => ['name' => 'French Polynesia', 'display' => 'French Polynesia']
        ];
        
        $countries = [];
        
        foreach ($all_locales as $locale) {
            if (isset($locale_to_country[$locale])) {
                $country_info = $locale_to_country[$locale];
                $key = $country_info['name'] . '_' . $locale; // Unique key to avoid duplicates but allow multiple locales per country
                
                $countries[$key] = [
                    'name' => $country_info['name'],
                    'locale' => $locale,
                    'display' => $country_info['display'] . ' (' . $locale . ')'
                ];
            }
        }
        
        // Sort by display name
        uasort($countries, function($a, $b) {
            return strcmp($a['display'], $b['display']);
        });
        
        return array_values($countries);
    }

    /**
     * Get all available WordPress locales
     */
    private function get_all_wordpress_locales()
    {
        // Try to get from transient cache first
        $cached_locales = get_transient('listeo_all_wp_locales');
        if ($cached_locales !== false) {
            return $cached_locales;
        }
        
        // Complete list of WordPress locales based on WordPress.org
        $wp_locales = [
            // English variants
            'en_US', 'en_GB', 'en_AU', 'en_CA', 'en_NZ', 'en_ZA',
            
            // Spanish variants
            'es_ES', 'es_MX', 'es_AR', 'es_CL', 'es_CO', 'es_PE', 'es_VE', 'es_CR', 'es_DO', 'es_EC', 'es_GT', 'es_UY', 'es_PR',
            
            // French variants
            'fr_FR', 'fr_CA', 'fr_BE',
            
            // German variants
            'de_DE', 'de_AT', 'de_CH', 'de_DE_formal', 'de_CH_informal',
            
            // Portuguese variants
            'pt_PT', 'pt_BR', 'pt_AO', 'pt_PT_ao90',
            
            // Dutch variants
            'nl_NL', 'nl_BE', 'nl_NL_formal',
            
            // Eastern European
            'pl_PL', 'cs_CZ', 'sk_SK', 'hu_HU', 'ro_RO', 'bg_BG', 'hr', 'sl_SI', 'et', 'lv', 'lt_LT',
            
            // Slavic languages
            'ru_RU', 'uk', 'bel', 'bs_BA', 'mk_MK', 'sr_RS',
            
            // Nordic languages
            'sv_SE', 'da_DK', 'nb_NO', 'nn_NO', 'fi', 'is_IS',
            
            // Asian languages - East Asia
            'ja', 'ko_KR', 'zh_CN', 'zh_TW', 'zh_HK',
            
            // Asian languages - Southeast Asia
            'th', 'vi', 'id_ID', 'ms_MY', 'tl', 'ceb', 'jv_ID',
            
            // Asian languages - South Asia
            'hi_IN', 'bn_BD', 'ur', 'ta_IN', 'ta_LK', 'te', 'ml_IN', 'kn', 'gu', 'pa_IN', 'mr', 'ne_NP', 'si_LK',
            
            // Asian languages - Central Asia
            'kk', 'kir', 'uz_UZ', 'mn', 'my_MM', 'ug_CN', 'tt_RU',
            
            // Middle Eastern and Persian
            'ar', 'ary', 'he_IL', 'tr_TR', 'fa_IR', 'fa_AF', 'ps', 'ckb', 'haz', 'snd', 'skr',
            
            // Caucasus region
            'az', 'azb', 'ka_GE', 'hy',
            
            // African languages
            'sw', 'am', 'af', 'rhg', 'sah',
            
            // Celtic and Regional European
            'cy', 'gd', 'eu', 'ca', 'gl_ES', 'oci', 'fur', 'fy', 'dsb', 'hsb', 'szl',
            
            // Other European
            'it_IT', 'el', 'sq', 'mt_MT',
            
            // Special scripts and regions
            'bo', 'dzo', 'lo', 'km', 'as', 'kab', 'eo', 'arg', 'tah'
        ];
        
        // Add installed languages to ensure they're included
        $installed = get_available_languages();
        $installed[] = get_locale();
        
        $all_locales = array_unique(array_merge($wp_locales, $installed));
        
        // Cache for 24 hours
        set_transient('listeo_all_wp_locales', $all_locales, DAY_IN_SECONDS);
        
        return $all_locales;
    }

    /**
     * Show admin notices
     */
    public function show_admin_notice()
    {
        if (!empty($this->notice)) {
            ?>
            <div class="notice notice-<?php echo esc_attr($this->notice_type); ?> is-dismissible">
                <p><?php echo wp_kses_post($this->notice); ?></p>
            </div>
            <?php
        }
    }
}

// Define constant to indicate integrated version is loaded
if (!defined('LISTEO_REGIONS_IMPORTER_INTEGRATED')) {
    define('LISTEO_REGIONS_IMPORTER_INTEGRATED', true);
}
