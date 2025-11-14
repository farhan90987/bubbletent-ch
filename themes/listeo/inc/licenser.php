<?php

class Listeo {
	
  public $plugin_file = __FILE__;
	
  public $responseObj;
	
  public $licenseMessage;
	
  public $showMessage = false;
	
  public $slug = "listeo";
  public $_token = "listeo";
	
  public $settings = array();


  function __construct() {

		add_action( 'admin_print_styles', [ $this, 'SetAdminStyle' ] );
		
    $licenseKey   = get_option("Listeo_lic_Key","");
    $liceEmail    = get_option( "Listeo_lic_email","");
		
    $templateDir  = get_template_directory(); //or dirname(__FILE__);
		
    // FIX: First check if we have a valid 180-day cache
    $cache_key = 'listeo_license_180_' . md5(site_url() . $licenseKey . $liceEmail);
    $cached_result = get_transient($cache_key);
    
    // If we have cached result, use it without making any API calls
    if ($cached_result !== false && is_array($cached_result) && !empty($licenseKey)) {
        if (isset($cached_result['is_valid']) && $cached_result['is_valid']) {
            // License is valid from cache
            $this->responseObj = isset($cached_result['response']) ? $cached_result['response'] : null;
            $this->licenseMessage = isset($cached_result['message']) ? $cached_result['message'] : '';
            
            add_action( 'admin_menu', [$this,'ActiveAdminMenu'],99999);
            add_action( 'admin_post_Listeo_el_deactivate_license', [ $this, 'action_deactivate_license' ] );
            add_action( 'admin_post_listeo_reset_license_data', [ $this, 'action_reset_license_data' ] );
            add_action( 'admin_post_listeo_deactivate_license', [ $this, 'action_deactivate_license_simple' ] );
            add_action( 'wp_ajax_listeo_deactivate_license_ajax', [ $this, 'ajax_deactivate_license' ] );
        } else {
            // License is invalid from cache
            $this->responseObj = isset($cached_result['response']) ? $cached_result['response'] : null;
            $this->licenseMessage = isset($cached_result['message']) ? $cached_result['message'] : '';
            $this->showMessage = !empty($this->licenseMessage);
            
            add_action( 'admin_post_Listeo_el_activate_license', [ $this, 'action_activate_license' ] );
            add_action( 'admin_post_listeo_reset_license_data', [ $this, 'action_reset_license_data' ] );
            add_action( 'admin_post_listeo_deactivate_license', [ $this, 'action_deactivate_license_simple' ] );
            add_action( 'wp_ajax_listeo_deactivate_license_ajax', [ $this, 'ajax_deactivate_license' ] );
            add_action( 'admin_menu', [$this,'InactiveMenu']);
        }
        return; // Exit early - no API call needed
    }
    
    // Only check license in very specific cases
    $should_check_license = false;
    
    // NEVER check during AJAX (except license activation)
    if (defined('DOING_AJAX') && DOING_AJAX) {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        // Only for license activation actions
        if ($action === 'Listeo_el_activate_license') {
            $should_check_license = true;
        }
    }
    // NEVER check during CRON
    elseif (defined('DOING_CRON') && DOING_CRON) {
        $should_check_license = false;
    }
    // NEVER check during AUTOSAVE
    elseif (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        $should_check_license = false;
    }
    // Only check on license page or when explicitly activating
    elseif (is_admin()) {
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        // Only on license activation page
        if ($page === 'listeo_license' || $page === 'listeo_settings') {
            // But still respect cache if it exists
            if ($cached_result === false && !empty($licenseKey)) {
                $should_check_license = true;
            }
        }
    }
    
    if($should_check_license && b472b0Base::CheckWPPlugin( $licenseKey, $liceEmail, $this->licenseMessage, $this->responseObj, $templateDir."/style.css")){
        // Cache the valid result for 180 days
        set_transient($cache_key, [
            'is_valid' => true,
            'response' => $this->responseObj,
            'message' => $this->licenseMessage,
            'time' => time()
        ], 180 * DAY_IN_SECONDS);
        
    	add_action( 'admin_menu', [$this,'ActiveAdminMenu'],99999);
			add_action( 'admin_post_Listeo_el_deactivate_license', [ $this, 'action_deactivate_license' ] );
			add_action( 'admin_post_listeo_reset_license_data', [ $this, 'action_reset_license_data' ] );
			add_action( 'admin_post_listeo_deactivate_license', [ $this, 'action_deactivate_license_simple' ] );
			add_action( 'wp_ajax_listeo_deactivate_license_ajax', [ $this, 'ajax_deactivate_license' ] );
			//$this->licenselMessage=$this->mess;
			//***Write you plugin's code here***

		} else {
			
      // Only show license activation UI if we actually checked and it failed
      if($should_check_license && !empty($licenseKey) && !empty($this->licenseMessage)){
				// Cache the invalid result for 30 days
				set_transient($cache_key, [
					'is_valid' => false,
					'response' => $this->responseObj,
					'message' => $this->licenseMessage,
					'time' => time()
				], 30 * DAY_IN_SECONDS);
				
				$this->showMessage=true;

			}
			
     // update_option("Listeo_lic_Key","") || add_option("Listeo_lic_Key","");
			
      add_action( 'admin_post_Listeo_el_activate_license', [ $this, 'action_activate_license' ] );
			add_action( 'admin_post_listeo_reset_license_data', [ $this, 'action_reset_license_data' ] );
			add_action( 'admin_post_listeo_deactivate_license', [ $this, 'action_deactivate_license_simple' ] );
			add_action( 'wp_ajax_listeo_deactivate_license_ajax', [ $this, 'ajax_deactivate_license' ] );
			
      add_action( 'admin_menu', [$this,'InactiveMenu']);
		}
  }



	function SetAdminStyle() {
		  
      wp_register_style( "ListeoLic", get_theme_file_uri("/css/admin.css"),10);
		  wp_enqueue_style( "ListeoLic" );
		  
		  // Load modern admin CSS for license page
		  $plugin_css_path = plugins_url('/listeo-core/assets/css/listeo-modern-admin.css');
		  if (file_exists(WP_PLUGIN_DIR . '/listeo-core/assets/css/listeo-modern-admin.css')) {
		      wp_register_style( "ListeoModernAdmin", $plugin_css_path, array(), '1.0.0');
		      wp_enqueue_style( "ListeoModernAdmin" );
		  }

	}
	
  function ActiveAdminMenu(){
		 
		//add_menu_page (  "Listeo", "Listeo", "activate_plugins", $this->slug, [$this,"Activated"], " dashicons-star-filled ");
		//add_submenu_page(  $this->slug, "Listeo License", "License Info", "activate_plugins",  $this->slug."_license", [$this,"Activated"] );
    add_submenu_page('listeo_settings', 'License', 'License', 'manage_options', $this->slug."_license",  array( $this, 'Activated' ) ); 
	}

	function InactiveMenu() {
		  //add_menu_page( "Listeo", "Listeo", 'activate_plugins', $this->slug,  [$this,"LicenseForm"], " dashicons-star-filled " );
	   add_submenu_page('listeo_settings', 'License', 'License', 'manage_options', $this->slug."_license",  array( $this, 'LicenseForm' ) ); 	
	}
	
  function action_activate_license(){

		check_admin_referer( 'el-license' );
		
    $licenseKey=!empty($_POST['el_license_key'])?sanitize_text_field($_POST['el_license_key']):"";
		$licenseEmail=!empty($_POST['el_license_email'])?sanitize_email($_POST['el_license_email']):"";
		
		// Prevent duplicate submissions
		$submission_key = 'listeo_license_submission_' . md5($licenseKey . $licenseEmail . get_current_user_id());
		if (get_transient($submission_key)) {
			wp_safe_redirect(admin_url( 'admin.php?page=listeo_license&message=processing'));
			exit;
		}
		
		// Set submission lock for 60 seconds
		set_transient($submission_key, true, 60);
		
    // Store original values to check if activation succeeded
    update_option("Listeo_lic_Key",$licenseKey);
		update_option("Listeo_lic_email",$licenseEmail);
		update_option('_site_transient_update_themes','');
		
    // Clear the 180-day cache for this license
    $cache_key_180 = 'listeo_license_180_' . md5(site_url() . $licenseKey . $licenseEmail);
    delete_transient($cache_key_180);
    
    // Clear any cached responses for this license
    $transient_key = 'listeo_license_valid_' . md5($licenseKey . $licenseEmail);
    delete_transient($transient_key);
    
    // Clear any cached API requests
    $server_host = "http://purethe.me/wp-json/licensor/";
    $url = rtrim($server_host, '/') . "/" . ltrim('product/active/2', '/');
    $api_transient_key = 'listeo_api_request_' . md5($url . $licenseKey);
    delete_transient($api_transient_key);
    
    // Clear all related transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_listeo_license_%' OR option_name LIKE '_transient_listeo_api_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_listeo_license_%' OR option_name LIKE '_transient_timeout_listeo_api_%'");
    
    // Add debugging
    update_option('listeo_last_license_attempt', array(
        'key' => substr($licenseKey, 0, 5) . '...' . substr($licenseKey, -5),
        'email' => $licenseEmail,
        'time' => current_time('mysql'),
    ));
    
    // Clear the submission lock
    delete_transient($submission_key);
    
    wp_safe_redirect(admin_url( 'admin.php?page=listeo_license'));
	}


	function action_deactivate_license() {
	
  	check_admin_referer( 'el-license' );
	
  	$message="";
	
  	if(b472b0Base::RemoveLicenseKey(__FILE__,$message)){
			 update_option("Listeo_lic_Key","") || add_option("Listeo_lic_Key","");
			 update_option('_site_transient_update_themes','');
			 
			 // Clear all license-related transients
			 $this->clear_license_transients();
		}
    	wp_safe_redirect(admin_url( 'admin.php?page=listeo_license'));
    }
    
    private function clear_license_transients() {
		global $wpdb;
		
		// Clear all license-related transients
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_listeo_license_%' OR option_name LIKE '_transient_listeo_api_%'");
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_listeo_license_%' OR option_name LIKE '_transient_timeout_listeo_api_%'");
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_listeo_debug_%'");
		
		// Clear the current 180-day cache
		$licenseKey = get_option("Listeo_lic_Key", "");
		$liceEmail = get_option("Listeo_lic_email", "");
		if (!empty($licenseKey) && !empty($liceEmail)) {
			$cache_key = 'listeo_license_180_' . md5(site_url() . $licenseKey . $liceEmail);
			delete_transient($cache_key);
		}
		
		// Also clear potential old 180-day caches (in case license key changed)
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_listeo_license_180_%'");
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_listeo_license_180_%'");
		
		// Clear API request locks
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_listeo_api_lock_%'");
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_listeo_api_lock_%'");
		
		// Clear license check locks
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_listeo_license_check_lock_%'");
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_listeo_license_check_lock_%'");
    }
    
    function action_reset_license_data() {
		check_admin_referer( 'listeo_reset_license_nonce_action', 'listeo_reset_license_nonce' );
		
		if (!current_user_can('manage_options')) {
			wp_die('You do not have sufficient permissions to access this page.');
		}
		
		// Clear all license data
		delete_option('Listeo_lic_Key');
		delete_option('Listeo_lic_email');
		delete_option('listeo_activation_date');
		delete_option('listeo_license_key_activated');
		delete_option('listeo_last_license_attempt');
		delete_option('listeo_offline_activation');
		delete_option('listeo_proxy_validation');
		delete_option('listeo_last_proxy_validation');
		
		// Clear all license-related transients
		$this->clear_license_transients();
		
		// Use the base class cleanup method too
		$message = "";
		b472b0Base::RemoveLicenseKey(__FILE__, $message);
		
		// Clear theme update transients
		delete_option('_site_transient_update_themes');
		delete_option('_site_transient_update_plugins');
		
		wp_safe_redirect(admin_url( 'admin.php?page=listeo_license&reset=success'));
	}
	
	/**
     * Simple license deactivation - removes license data locally 
     */
    function action_deactivate_license_simple() {
        check_admin_referer( 'listeo_deactivate_license_nonce_action', 'listeo_deactivate_license_nonce' );
        
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }
        
        // Remove license data from database
        delete_option('Listeo_lic_Key');
        delete_option('Listeo_lic_email');
        delete_option('listeo_license_key_activated');
        delete_option('listeo_offline_activation');
        delete_option('listeo_proxy_validation');
        delete_option('listeo_last_proxy_validation');
        delete_option('listeo_activation_date');
        delete_option('listeo_last_license_attempt');
        
        // Clear license-related transients 
        $this->clear_license_transients();
        
        // Also call the base class cleanup
        $message = "";
        b472b0Base::RemoveLicenseKey(__FILE__, $message);
        
        wp_safe_redirect(admin_url( 'themes.php?page=listeo_license&deactivated=success'));
        exit;
    }
    
    /**
     * AJAX handler for license deactivation
     */
    function ajax_deactivate_license() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'listeo_deactivate_license_ajax')) {
            wp_send_json_error('Security verification failed');
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        try {
            // Remove license data from database
            delete_option('Listeo_lic_Key');
            delete_option('Listeo_lic_email');
            delete_option('listeo_license_key_activated');
            delete_option('listeo_offline_activation');
            delete_option('listeo_proxy_validation');
            delete_option('listeo_last_proxy_validation');
            delete_option('listeo_activation_date');
            delete_option('listeo_last_license_attempt');
            
            // Clear license-related transients 
            $this->clear_license_transients();
            
            // Also call the base class cleanup
            $message = "";
            b472b0Base::RemoveLicenseKey(__FILE__, $message);
            
            wp_send_json_success('License deactivated successfully');
            
        } catch (Exception $e) {
            wp_send_json_error('Failed to deactivate license: ' . $e->getMessage());
        }
    }
	
  function Activated(){
        
        $tab = '';
        if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
            $tab .= $_GET['tab'];
        }

        // Build modern license status page
        ob_start(); ?>
        
        <div class="listeo-license-modern">
            <div class="listeo-license-container">
                <div class="listeo-license-header">
                    <h1 class="listeo-license-title">License Information</h1>
                    <p class="listeo-license-subtitle">Your Listeo theme license is active and ready to use</p>
                </div>

                <?php
                // Show success message if license was reset
                if (isset($_GET['reset']) && $_GET['reset'] == 'success') { ?>
                    <div class="listeo-license-notification listeo-license-success">
                        <strong>✅ License data has been successfully reset!</strong> You can now test the setup wizard or activate a new license.
                    </div>
                <?php }
                
                // Show success message if license was deactivated
                if (isset($_GET['deactivated']) && $_GET['deactivated'] == 'success') { ?>
                    <div class="listeo-license-notification listeo-license-success">
                        <strong>✅ License has been deactivated!</strong> The license has been removed from this site and can now be used on another domain if needed.
                    </div>
                <?php } ?>

                <div class="listeo-license-card">
                    <div class="listeo-license-icon-container">
                        <div class="listeo-license-icon"></div>
                    </div>
                    
                    <h2 class="listeo-license-card-title">License Status</h2>
                    <p class="listeo-license-card-subtitle">Your license details and current status</p>

                    <ul class="listeo-license-status-list">
                        <li class="listeo-license-status-item">
                            <span class="listeo-license-status-label">Status</span>
                            <span class="listeo-license-status-value">
                                <?php if ( $this->responseObj->is_valid ) : ?>
                                    <span class="listeo-license-valid">Valid</span>
                                    <?php if ( get_option('listeo_offline_activation') === 'yes' ) : ?>
                                        <span style="color: #FF8C00; font-weight: normal; margin-left: 5px;">(Offline Activation)</span>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span class="listeo-license-invalid">Invalid</span>
                                <?php endif; ?>
                            </span>
                        </li>

                        <li class="listeo-license-status-item">
                            <span class="listeo-license-status-label">License Type</span>
                            <span class="listeo-license-status-value">
                                <a href="https://themeforest.net/licenses/standard" target="_blank">
                                    <?php echo str_replace(' (Offline Activation)', '', $this->responseObj->license_title); ?>
                                </a>
                            </span>
                        </li>

                        <?php
                        $manual_activation = apply_filters('listeo_license_check', false);
                        $offline_activation = get_option('listeo_offline_activation') === 'yes';
                        
                        // Only show support info if not offline activation and not manual activation
                        if (!$manual_activation && !$offline_activation) {
                            $today = date("Y-m-d H:i:s"); 
                            if($this->responseObj->support_end > $today ) { ?>
                                <li class="listeo-license-status-item">
                                    <span class="listeo-license-status-label">Support Ends on</span>
                                    <span class="listeo-license-status-value">
                                        <?php echo $this->responseObj->support_end; ?>
                                        <a target="_blank" style="color: #28a745; font-weight: 600; margin-left: 8px;" 
                                           href="https://themeforest.net/item/listeo-job-board-wordpress-theme/23239259/support/contact">Need Support?</a>
                                    </span>
                                </li>
                            <?php } else { ?>
                                <li class="listeo-license-status-item">
                                    <span class="listeo-license-status-label">Support Expired on</span>
                                    <span class="listeo-license-status-value">
                                        <?php echo $this->responseObj->support_end; ?>
                                        <?php if(!empty($this->responseObj->support_renew_link)){ ?>
                                            <br>
                                            <a target="_blank" class="listeo-license-renew-link" 
                                               href="<?php echo $this->responseObj->support_renew_link; ?>">Renew Support</a>
                                        <?php } ?>
                                    </span>
                                </li>
                            <?php } 
                        } ?>

                        <li class="listeo-license-status-item">
                            <span class="listeo-license-status-label">Your License Key</span>
                            <span class="listeo-license-status-value" style="font-family: monospace;">
                                <?php echo esc_attr( substr($this->responseObj->license_key,0,9)."XXXXXXXX-XXXXXXXX".substr($this->responseObj->license_key,-9) ); ?>
                            </span>
                        </li>
                    </ul>

                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="Listeo_el_deactivate_license"/>
                        <input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
                        <?php wp_nonce_field( 'el-license' ); ?>

                        <div style="margin-bottom: 16px;">
                            <?php 
                            if (!$manual_activation) {
                              $link = "https://purethemes.net/license/?purchase=" .  esc_attr( $this->responseObj->license_key );
                            } else {
                              $link = "https://purethemes.net/license/";
                            } ?>
                            <a href="<?php echo $link; ?>" target="_blank" 
                               class="listeo-license-action-btn listeo-license-transfer-btn">
                                Transfer License to Another Domain
                            </a>
                        </div>

                        <button type="button" id="deactivate-license-btn" 
                                class="listeo-license-action-btn listeo-license-deactivate-btn"
                                data-nonce="<?php echo wp_create_nonce('listeo_deactivate_license_ajax'); ?>">
                            Deactivate License
                        </button>
                        <span id="deactivate-status" style="margin-left: 10px;"></span>
                    </form>
                </div>
            </div>
        </div>

        <?php
        // Log proxy validation info to console if available
        $last_proxy = get_option('listeo_last_proxy_validation');
        if ( get_option('listeo_proxy_validation') === 'yes' && $last_proxy && !empty($last_proxy['success'])) : ?>
            <script>
                console.log('✅ Listeo License: Validated via proxy server');
                console.log('Proxy used: <?php echo esc_js($last_proxy['proxy']); ?>');
                console.log('Validation time: <?php echo esc_js($last_proxy['time']); ?>');
            </script>
        <?php endif; ?>
        
        <!-- AJAX Deactivation Script -->
        <script>
        jQuery(document).ready(function($) {
            $("#deactivate-license-btn").click(function() {
                if (!confirm("Are you sure you want to deactivate the license?\\n\\nThis will remove all license data from this site and allow you to activate it elsewhere if needed.")) {
                    return;
                }
                
                var $button = $(this);
                var $status = $("#deactivate-status");
                
                $button.prop("disabled", true).text("Deactivating...");
                $status.html("");
                
                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: "listeo_deactivate_license_ajax",
                        nonce: $button.data("nonce")
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.html("<span style=\"color: #28a745;\">✅ License deactivated successfully!</span>");
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            $status.html("<span style=\"color: #dc3545;\">❌ Error: " + response.data + "</span>");
                            $button.prop("disabled", false).text("Deactivate License");
                        }
                    },
                    error: function() {
                        $status.html("<span style=\"color: #dc3545;\">❌ Connection error occurred</span>");
                        $button.prop("disabled", false).text("Deactivate License");
                    }
                });
            });
        });
        </script>

        <?php
        echo ob_get_clean();
    
	}
	
	function LicenseForm() {
        
        $tab = '';
        if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
            $tab .= $_GET['tab'];
        }

        // Build modern license activation page
        ob_start(); ?>
        
        <div class="listeo-license-modern">
            <div class="listeo-license-container">
                <div class="listeo-license-header">
                    <h1 class="listeo-license-title">License Activation</h1>
                    <p class="listeo-license-subtitle">Activate your Listeo theme license to unlock all features</p>
                </div>

                <div class="listeo-license-card">
                    <div class="listeo-license-icon-container">
                        <div class="listeo-license-icon"></div>
                    </div>
                    
                    <h2 class="listeo-license-card-title">Activate Your License</h2>
                    <p class="listeo-license-card-subtitle">Enter your purchase details to activate your license</p>

                    <div class="listeo-license-info-box">
                        <div class="listeo-license-info-icon"></div>
                        <div class="listeo-license-info-text">
                            <div class="listeo-license-info-title">Single Site License:</div>
                            This license can only be used on one finished website. You can deactivate and move it to another domain at any time.
                        </div>
                    </div>

                    <?php
                    // Show error messages if any
                    if(!empty($this->showMessage) && !empty($this->licenseMessage)){ ?>
                        <div class="listeo-license-notification">
                           <?php 
                            if($this->licenseMessage == 'You license key has been waiting for manual approval, Please contact with license author'){
                              echo 'Provided license key is already assigned to other domain. Deactivate it for that domain or purchase new license. If you want to activate it on dev/staging environment, please contact us about it via Support Tab on ThemeForest.';
                            } else {
                              echo $this->licenseMessage;     
                            }
                          ?>
                        </div>
                    <?php }  ?>

                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="Listeo_el_activate_license"/>
                        <input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
                        
                        <div class="listeo-license-form-group">
                            <label class="listeo-license-form-label">
                                <div class="listeo-license-key-icon"></div>
                                Purchase Code
                            </label>
                            <input type="text" class="listeo-license-form-input" name="el_license_key" 
                                   placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" required="required">
                            <p class="listeo-license-help-text">Find your purchase code in your ThemeForest account</p>
                        </div>

                        <div class="listeo-license-form-group">
                            <label class="listeo-license-form-label">
                                <div class="listeo-license-email-icon"></div>
                                ThemeForest Email Address
                            </label>
                            <?php $purchaseEmail = get_option( "Listeo_lic_email", get_bloginfo( 'admin_email' )); ?>
                            <input type="email" class="listeo-license-form-input" name="el_license_email" 
                                   value="<?php echo esc_attr($purchaseEmail); ?>" placeholder="your-email@example.com" required="required">
                            <p class="listeo-license-help-text">The email address associated with your ThemeForest account</p>
                        </div>

                        <?php wp_nonce_field( 'el-license' ); ?>

                        <button type="submit" class="listeo-license-activate-btn">
                            <div class="listeo-license-btn-icon"></div>
                            Activate License
                        </button>
                    </form>

                    <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" 
                       target="_blank" class="listeo-license-help-link">
                        <div class="listeo-license-help-link-icon"></div>
                        Need help finding your purchase code?
                    </a>
                </div>
            </div>
        </div>

        <?php
        // Log proxy validation attempt info to console
        $last_proxy = get_option('listeo_last_proxy_validation');
        if ($last_proxy && !$last_proxy['success']) : ?>
            <script>
                console.warn('⚠️ Listeo License: Proxy validation attempted but failed');
                console.log('Last attempt: <?php echo esc_js($last_proxy['time']); ?>');
                console.log('Proxy tried: <?php echo esc_js($last_proxy['proxy']); ?>');
            </script>
        <?php endif; ?>

        <?php
        echo ob_get_clean();
		?>


        
		<?php
	}
}

new Listeo();