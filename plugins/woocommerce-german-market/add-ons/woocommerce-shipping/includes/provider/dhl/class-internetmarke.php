<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

use baltpeter\Internetmarke\PublicGalleryItem;
use DateTime;
use DateTimeZone;
use Exception;
use League\ISO3166\ISO3166;
use MarketPress\GermanMarket\Shipping\Helper;
use SoapClient;
use SoapFault;
use SoapHeader;
use StdClass;
use WC_Order;
use function DeepCopy\deep_copy;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Internetmarke {

	/**
	 * Wallet Top Up Url.
	 * @var string
	 */
	const WALLET_TOP_UP_URL = 'https://portokasse.deutschepost.de/portokasse/marketplace/enter-app-payment';

	/**
	 * Portokasse Url.
	 * @var string
	 */
	const PORTOKASSE_URL = 'https://portokasse.deutschepost.de/portokasse/#!/';

	/**
	 * Wsdl Url.
	 * @var string
	 */
	const WSDL_URL = 'https://internetmarke.deutschepost.de/OneClickForAppV3/OneClickForAppServiceV3?wsdl';

	/**
	 * Signup Url.
	 * @var string
	 */
	const SIGNUP_URL = 'https://portokasse.deutschepost.de/portokasse/#!/register/';

	/**
	 * Kilotarif PDF.
	 * @var string
	 */
	const KILOTARIF_PDF = 'https://www.deutschepost.de/content/dam/dpag/images/E_e/Einlieferungslisten/Downloads/dp-el-warenpost-zum-kilotarif-internat-02-2018.pdf';

	/**
	 * @var string
	 *
	 * const PRODWS_WSDL_URL = 'https://prodws.deutschepost.de:8443/ProdWSProvider_1_1/prodws?wsdl';
	 */
	const PRODWS_WSDL_URL = 'https://prodws.deutschepost.de/ProdWSProvider_1_1/prodws?wsdl';

	/**
	 * Tracking Url.
	 * @var string
	 */
	const TRACKING_LINK = '<a target="_blank" href="https://www.deutschepost.de/sendung/simpleQueryResult.html?form.sendungsnummer=%s&form.einlieferungsdatum_tag=%d&form.einlieferungsdatum_monat=%d&form.einlieferungsdatum_jahr=%d">%s</a>';

	/**
	 * @var string
	 */
	const ALL = 'ALL';

	/**
	 * @var string
	 */
	const DOMESTIC = 'DE';

	/**
	 * @var string
	 */
	const EUROPEAN_UNION = 'EU';

	/**
	 * @var string
	 */
	const REST_OF_WORLD = 'ROW';

	/**
	 * Internetmarke Partner Credentials.
	 */
	const API_PARTNER_ID         = 'AMHGE';
	const API_PARTNER_SECRET_KEY = '8Y0KcBVBhDPfiyqqxMGo0W48XliogrT1';
	const API_PARTNER_KEYPHASE   = '1';

	/**
	 * Product Catalog Credentials.
	 */
	const PRODWS_MANDANTID = 'MARKETPRESS';
	const PRODWS_USERNAME  = 'marketpress';
	const PRODWS_PASSWORD  = 'D&6%bk?db1';

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $id;

	/**
	 * Singleton.
	 *
	 * @acces protected
	 * @static
	 *
	 * @var self
	 */
	public static $instance;

	/**
	 * @var string
	 */
	private string $api_username;

	/**
	 * @var string
	 */
	private string $api_password;

	/**
	 * @var object
	 */
	public $api = null;

	/**
	 * @var object
	 */
	public $wss;

	/**
	 * @var string
	 */
	public string $authToken;

	/**
	 * @var int
	 */
	public int $walletBalance;

	/**
	 * @var int is 0 or 1
	 */
	public int $showTermsAndConditions;

	/**
	 * @var array
	 */
	public array $internetmarke_products;

	/**
	 * @var array
	 */
	public array $internetmarke_product_services;

	/**
	 * @var array
	 */
	public array $internetmarke_page_formats = array();

	/**
	 * Singleton getInstance.
	 *
	 * @static
	 *
	 * @param string $provider_id
	 *
	 * @return self
	 * @throws SoapFault
	 */
	public static function get_instance( string $provider_id ) : self {

		return ( null !== self::$instance ) ? self::$instance : self::$instance = new self( $provider_id );
	}

	/**
	 * Constructor.
	 *
	 * @param string $provider_id
	 *
	 * @throws SoapFault
	 */
	public function __construct( string $provider_id ) {

		$this->id = $provider_id;

		$this->api_username = Shipping_Provider::$options->get_option( 'internetmarke_portokasse_email' );
		$this->api_password = Shipping_Provider::$options->get_option( 'internetmarke_portokasse_password' );

		if ( ( isset( $_POST[ 'wgm_dhl_internetmarke_portokasse_email' ] ) && ( '' != $_POST[ 'wgm_dhl_internetmarke_portokasse_email' ] ) ) ||
		     ( isset( $_POST[ 'wgm_dhl_internetmarke_portokasse_password' ] ) && ( '' != $_POST[ 'wgm_dhl_internetmarke_portokasse_password' ] ) )
		) {
			// Delete transients if user or password changed.

			if ( ( $this->api_username !== trim( $_POST[ 'wgm_dhl_internetmarke_portokasse_email' ] )  ) || ( $this->api_password !== trim( $_POST[ 'wgm_dhl_internetmarke_portokasse_password' ] ) ) ) {
				delete_transient( 'wgm_shipping_dhl_internetmarke_login_locked' );
				delete_transient( 'wgm_shipping_dhl_internetmarke_login_attempt' );
			}

			$this->api_username = $_POST[ 'wgm_dhl_internetmarke_portokasse_email' ];
			$this->api_password = $_POST[ 'wgm_dhl_internetmarke_portokasse_password' ];
		}

		$this->internetmarke_page_formats = Shipping_Provider::$options->get_option( 'internetmarke_page_formats', array() );
		$this->walletBalance              = 0;

		if ( ( '' === $this->api_username ) || ( '' === $this->api_password ) ||
		     ( '' === self::API_PARTNER_ID ) || ( '' === self::API_PARTNER_SECRET_KEY ) ||
		     ( '' === self::PRODWS_MANDANTID ) || ( '' === self::PRODWS_USERNAME ) || ( '' === self::PRODWS_PASSWORD ) ) {
			// Unschedule cron if a credential is missing.
			self::unschedule_cron();
		} else {
			// Schedule cron if we have all credentials.
			self::schedule_cron();
		}

		self::maybe_create_tables();

		add_action( 'german_market_internetmarke_update_product_list', array( $this, 'update_products_services_cron' ) );

		if ( empty( $this->internetmarke_products ) || empty( $this->internetmarke_product_services ) ) {
			$this->load_products();
		}

		if ( 0 == count( $this->internetmarke_page_formats ) || apply_filters( 'woocommerce_wgm_dhl_internetmarke_update_page_formats', false ) ) {
			$this->internetmarke_page_formats = $this->retrieve_page_formats();
			Shipping_Provider::$options->update_option( 'internetmarke_page_formats', $this->internetmarke_page_formats );
		}
	}

	/**
	 * Check if the API should be initialized on current admin page.
	 * We need a workaround at this point because the function 'get_current_screen' is not available yet.
	 *
	 * @static
	 *
	 * @return bool
	 */
	public static function is_internetmarke_option_page() : bool {
		global $pagenow;

		// Check for German Market menu area.

		if ( 'admin.php' === $pagenow ) {
			$params = $_GET;
			if (
				isset( $params[ 'page' ] ) && ( 'german-market' === $params[ 'page' ] ) &&
				isset( $params[ 'sub_tab' ] ) && ( 'internetmarke' === $params[ 'sub_tab' ] )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the API should be initialized on current order page.
	 * We need a workaround at this point because the function 'get_current_screen' is not available yet.
	 *
	 * @static
	 *
	 * @return bool
	 */
	public static function is_edit_order_page() : bool {
		global $pagenow;

		// Check for order edit page.

		if ( 'post.php' === $pagenow ) {
			$params = $_GET;
			if ( ! empty( $params[ 'post' ] ) && ( isset( $params[ 'action' ] ) && ( 'edit' === $params[ 'action' ] ) ) ) {
				$post = get_post( $params[ 'post' ] );
				if ( $post->post_type === 'shop_order' ) {
					return true;
				}
			}
		}

		// Check for HPOS order edit page.

		else if ( 'admin.php' === $pagenow ) {
			$params = $_GET;
			if ( ! empty( $params[ 'page' ] ) && ( 'wc-orders' === $params[ 'page' ] ) && isset( $params[ 'action' ] ) && ( 'edit' === $params[ 'action'] ) && ! empty( $params[ 'id' ] ) ) {
				$order = wc_get_order( $params[ 'id' ] );
				if ( $order ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Try to establish an API connection.
	 *
	 * @access private
	 *
	 * @return mixed
	 */
	private function connect() {
		global $pagenow;

		// Check if connection is already set.

		if ( null !== $this->api ) {
			return $this->api;
		}

		// Check if API credentials are all set.

		if ( ( '' === $this->api_username ) || ( '' === $this->api_password ) ||
		     ( '' === self::API_PARTNER_ID ) || ( '' === self::API_PARTNER_SECRET_KEY ) ||
		     ( '' === self::PRODWS_MANDANTID ) || ( '' === self::PRODWS_USERNAME ) || ( '' === self::PRODWS_PASSWORD ) ) {
			return null;
		}

		// Check if connection can be established.

		if ( 'locked' === get_transient( 'wgm_shipping_dhl_internetmarke_login_locked' ) ) {
			return null;
		}

		// Connect to API.

		$this->api = $this->init_Api_Client();
		$userdata  = $this->authenticateUser( $this->api_username, $this->api_password );

		if ( isset( $userdata->userToken ) && ( '' != $userdata->userToken ) ) {
			$this->authToken = $userdata->userToken;
		}
		if ( isset( $userdata->walletBalance ) && ( '' != $userdata->walletBalance ) ) {
			$this->walletBalance = $userdata->walletBalance;
		}
		if ( isset( $userdata->showTermsAndConditions ) && ( '' != $userdata->showTermsAndConditions ) ) {
			$this->showTermsAndConditions = $userdata->showTermsAndConditions;
		}

		$this->init_WSS();

		return $this->api;
	}

	/**
	 * Test API connection for indicator in backend.
	 *
	 * @return int
	 */
	public function test_connection() : int {

		if ( null === $this->api ) {

			if ( 'locked' === get_transient( 'wgm_shipping_dhl_internetmarke_login_locked' ) ) {
				return 401;
			}

			if ( ! self::is_internetmarke_option_page() && ! self::is_edit_order_page() ) {
				return 401;
			}

			$this->connect();
		}

		if ( empty( $this->api_username ) || empty( $this->api_password ) ) {
			return 100;
		}

		if ( ( isset( $_POST[ 'wgm_dhl_internetmarke_portokasse_email' ] ) && ( '' == $_POST[ 'wgm_dhl_internetmarke_portokasse_email' ] ) ) ||
		     ( isset( $_POST[ 'wgm_dhl_internetmarke_portokasse_password' ] ) && ( '' == $_POST[ 'wgm_dhl_internetmarke_portokasse_password' ] ) )
		) {
			return 100;
		}

		if ( ! empty( $this->authToken ) ) {
			return 200;
		}

		return 401;
	}

	/**
	 * Schedules the Internetmarke Cron job.
	 *
	 * @return void
	 */
	public static function schedule_cron() {

		$args = array( false );
		if ( ! wp_next_scheduled('german_market_internetmarke_update_product_list', $args ) ) {
			wp_schedule_event( time(), 'daily', 'german_market_internetmarke_update_product_list', $args );
		}
	}

	/**
	 * Unschedules the Internetmarke Cron job.
	 */
	public static function unschedule_cron() {

		$args = array( false );
		wp_unschedule_event(
			wp_next_scheduled('german_market_internetmarke_update_product_list', $args ),
			'german_market_internetmarke_update_product_list'
		);
	}

	/**
	 * Create a new Soap Client.
	 *
	 * @return SoapClient client object
	 * @throws SoapFault
	 */
	public function init_Api_Client() {

		$client = false;
		$handle = curl_init(self::WSDL_URL );

		curl_setopt($handle,  CURLOPT_RETURNTRANSFER, true );

		$response = curl_exec($handle);
		$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE );
		if ( $httpCode != 0 && $httpCode == 200 ) {
			$client = new SoapClient( self::WSDL_URL, array(
				'features'   => SOAP_SINGLE_ELEMENT_ARRAYS,
				'exceptions' => false,
				'encoding'   => 'utf-8',
			) );
			$client->__setSoapHeaders( $this->soapHeaderArray() );
		}

		curl_close( $handle );

		return $client;
	}

	/**
	 * Create a connection to the ProdWS service.
	 *
	 * @return void
	 */
	public function init_WSS() {

		$this->wss = new SoapClient( self::PRODWS_WSDL_URL );
		$this->wss->__setSoapHeaders( array(
			new Internetmarke_Wss_Header( self::PRODWS_USERNAME, self::PRODWS_PASSWORD )
		) );
	}

	/**
	 * Generate the Soap Header Array.
	 *
	 * @return array An array of SOAP headers to authenticate the request with the Internetmarke server. Valid for four minutes from `REQUEST_TIMESTAMP`
	 */
	public function soapHeaderArray() : array {

		//date_default_timezone_set( 'Europe/Berlin' ); // The DPAG server requires a date from the German timezone

		$germany_timezone = new DateTimeZone( 'Europe/Berlin' );
		$timestring       = wp_date( 'dmY-His', null, $germany_timezone );

		return array(
			new SoapHeader('https://internetmarke.deutschepost.de', 'PARTNER_ID', self::API_PARTNER_ID ),
			new SoapHeader('https://internetmarke.deutschepost.de', 'REQUEST_TIMESTAMP', $timestring ),
			new SoapHeader('https://internetmarke.deutschepost.de', 'KEY_PHASE', self::API_PARTNER_KEYPHASE ),
			new SoapHeader('https://internetmarke.deutschepost.de', 'PARTNER_SIGNATURE', $this->calculate_signature( $timestring ) ),
			new SoapHeader('https://internetmarke.deutschepost.de', 'SIGNATURE_ALGORITHM', 'sha-512' ),
		);
	}

	/**
	 * Calculating Partner Signature.
	 *
	 * @access protected
	 *
	 * @param string $timestring time string sent in soap header array before
	 *
	 * @return string The signature to be appended to the request header ( 8 Chars long )
	 */
	protected function calculate_signature( string $timestring ) : string {

		return hash( 'sha512', self::API_PARTNER_ID . '::' . $timestring . '::' . self::API_PARTNER_KEYPHASE . '::' . self::API_PARTNER_SECRET_KEY );
	}

	/**
	 * Used to authenticate a user on the system. Returns a token and some information about the user.
	 *
	 * @param string $username The user's email address
	 * @param string $password The user's (plaintext) password
	 *
	 * @return object|false An object holding:
	 *     - a token used as authentication for other methods
	 *     - the user's wallet balance
	 *     - whether the user accepted the Terms & Conditions
	 *     - an (optional) information text
	 */
	public function authenticateUser( string $username, string $password ) {

		$client = false;

		if ( $this->api ) {
			try {
				$client = $this->api->__soapCall( 'authenticateUser', array(
					'AuthenticateUserRequest' => array(
						'username' => $username,
						'password' => $password
					)
				) );
				if ( ! empty( $client->detail ) && ( ! empty( $client->detail->AuthenticateUserException ) ) ) {
					if ( 'unkownUser' === $client->detail->AuthenticateUserException->id ) {
						/**
						 * Possible reasons are
						 * - unknown user
						 * - wrong password
						 * - Portokasse was canceled
						 */
						$login_attempt = get_transient( 'wgm_shipping_dhl_internetmarke_login_attempt' );
						if ( ! empty( $login_attempt ) ) {
							$login_attempt++;
						} else {
							$login_attempt = 1;
						}
						if ( $login_attempt >= 3 ) {
							set_transient( 'wgm_shipping_dhl_internetmarke_login_locked', 'locked', HOUR_IN_SECONDS );
							delete_transient( 'wgm_shipping_dhl_internetmarke_login_attempt' );
						} else {
							set_transient( 'wgm_shipping_dhl_internetmarke_login_attempt', $login_attempt, DAY_IN_SECONDS );
						}
					} else
						if ( 'invalidUser' === $client->detail->AuthenticateUserException->id ) {
							/**
							 * Possible reasons are
							 * - the user's Portokasse has been blocked
							 * - the user's Portokasse has been temporarily blocked
							 * - the user account (e-mail address) is blacklisted
							 */
							set_transient( 'wgm_shipping_dhl_internetmarke_login_locked', 'locked', DAY_IN_SECONDS );
						}
				} else {
					delete_transient( 'wgm_shipping_dhl_internetmarke_login_attempt' );
				}
			} catch ( SoapFault $e ) {
				return false;
			}
		}

		return $client;
	}

	/**
	 * Try to load products from the Database.
	 *
	 * @return void
	 */
	public function load_products() {
		global $wpdb;

		$this->internetmarke_products         = $wpdb->get_results( "SELECT * FROM " . self::get_products_table() );
		$this->internetmarke_product_services = $wpdb->get_results( "SELECT * FROM " . self::get_products_services_table() );

		if ( empty( $this->internetmarke_products ) || empty( $this->internetmarke_product_services ) ) {
			$this->update_products_services();
		}
	}

	/**
	 * Method for scheduled cron.
	 *
	 * @return void
	 */
	public function update_products_services_cron() {

		try {
			$this->update_products_services();
		} catch ( Exception $e ) {}
	}

	/**
	 * Update products and services from the API.
	 *
	 * @return void
	 */
	public function update_products_services() {
		global $wpdb;

		if ( null === $this->api ) {
			$this->connect();
		}

		if ( $this->wss ) {

			$product_list = $this->wss->__soapCall( 'getProductList', array(
				'getProductListRequest' => array(
					'mandantID'         => self::PRODWS_MANDANTID,
					'dedicatedProducts' => true,
					'responseMode'      => 0,
				)
			) );

			// Clearing tables.
			$wpdb->query( "TRUNCATE TABLE " . self::get_products_table() );
			$wpdb->query( "TRUNCATE TABLE " . self::get_products_services_table() );

			$products = array(
				'sales'      => $product_list->Response->salesProductList->SalesProduct,
				'additional' => $product_list->Response->additionalProductList->AdditionalProduct,
				'basic'      => $product_list->Response->basicProductList->BasicProduct
			);

			$products_with_additional_service = array();

			foreach( $products as $product_type => $inner_products ) {

				foreach( $inner_products as $product ) {

					$extended_identifier = $product->extendedIdentifier;
					$extern_identifier   = property_exists( $extended_identifier, 'externIdentifier' ) ? $extended_identifier->externIdentifier : new stdClass();

					// skip product if it has no id
					if ( ! property_exists( $extern_identifier, 'id' ) || empty( $extern_identifier->id ) ) {
						continue;
					}

					$to_insert = array(
						'product_im_id'            => $extended_identifier->{'ProdWS-ID'},
						'product_code'             => property_exists( $extern_identifier, 'id' ) ? $extern_identifier->id : $extended_identifier->{'ProdWS-ID'},
						'product_name'             => property_exists( $extern_identifier, 'name' ) ? $extern_identifier->name : $extended_identifier->name,
						'product_category'         => '',
						'product_type'             => $product_type,
						'product_annotation'       => property_exists( $extended_identifier, 'annotation' ) ? $extended_identifier->annotation : '',
						'product_description'      => property_exists( $extended_identifier, 'description' ) ? $extended_identifier->description : '',
						'product_destination'      => $extended_identifier->destination,
						'product_price'            => property_exists( $product->priceDefinition, 'price' ) ? $this->euros_to_cents( $product->priceDefinition->price->calculatedGrossPrice->value ) : $this->euros_to_cents( $product->priceDefinition->grossPrice->value ),
						'product_information_text' => property_exists( $product, 'stampTypeList' ) ? $this->get_information_text( (array) $product->stampTypeList->stampType ) : '',
					);

					$product_slug     = $this->sanitize_product_slug( $to_insert[ 'product_name' ] );
					$product_category = $this->sanitize_product_category( $product_slug, $to_insert[ 'product_destination' ] );

					$to_insert[ 'product_slug' ]     = $product_slug;
					$to_insert[ 'product_category' ] = $product_category;

					if ( property_exists( $product, 'dimensionList' ) ) {

						$dimensions = $product->dimensionList;

						$to_insert = array_merge( $to_insert, $this->get_dimensions( $dimensions, 'width' ) );
						$to_insert = array_merge( $to_insert, $this->get_dimensions( $dimensions, 'height' ) );
						$to_insert = array_merge( $to_insert, $this->get_dimensions( $dimensions, 'length' ) );
					}

					if ( property_exists( $product, 'weight' ) ) {

						$to_insert = array_merge( $to_insert, $this->get_dimensions( $product, 'weight' ) );
					}

					$to_insert = array_map( 'wc_clean', $to_insert );

					/**
					 * Skip product if this is an additional service
					 */
					if ( $this->is_additional_service( $to_insert[ 'product_slug' ] ) ) {

						$products_with_additional_service[] = $to_insert;
						continue;
					}

					$wpdb->insert( self::get_products_table(), $to_insert );
				}
			}

			foreach ( $products_with_additional_service as $product_to_insert ) {

				$product_base_slug = $this->get_product_base_slug( $product_to_insert[ 'product_slug' ] );
				$parent_product    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . self::get_products_table() . " WHERE product_slug = %s", $product_base_slug ) );
				$service_slugs     = $this->get_product_service_slugs( $product_to_insert[ 'product_slug' ] );

				if ( ! empty( $parent_product ) && ! empty( $service_slugs ) ) {
					$product_to_insert[ 'product_parent_id' ]     = $parent_product->product_id;
					$product_to_insert[ 'product_service_count' ] = sizeof( $service_slugs );
				}

				$wpdb->insert( self::get_products_table(), $product_to_insert );
				$product_id = $wpdb->insert_id;

				if ( ! empty( $parent_product ) && ! empty( $service_slugs ) ) {
					foreach( $service_slugs as $service_slug ) {
						$service_insert = array(
							'product_service_product_id'        => $product_id,
							'product_service_product_parent_id' => $parent_product->product_id,
							'product_service_slug'              => $service_slug,
						);

						$wpdb->insert( self::get_products_services_table(), $service_insert );
					}
				}
			}

			$this->internetmarke_products         = $wpdb->get_results( "SELECT * FROM " . self::get_products_table() );
			$this->internetmarke_product_services = $wpdb->get_results( "SELECT * FROM " . self::get_products_services_table() );
		}
	}

	/**
	 * Create database tables for products and services.
	 *
	 * @return void
	 */
	private static function maybe_create_tables() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// $wpdb->query( "DROP TABLE " . self::get_products_table() );
		// $wpdb->query( "DROP TABLE " . self::get_products_services_table() );

		dbDelta("
CREATE TABLE " . self::get_products_table() . " (
    product_id bigint(20) NOT NULL AUTO_INCREMENT,
    product_im_id INT(16) NOT NULL,
    product_code int(16) NOT NULL,
    product_name varchar(255) NOT NULL,
    product_category varchar(255) NOT NULL,
    product_type varchar(255) NOT NULL,
    product_annotation varchar(1024) NOT NULL,
    product_description varchar(255),
    product_destination varchar(256) NOT NULL,
    product_price int(8) NOT NULL,
    product_information_text varchar(512) NOT NULL,
    product_width_min int(8) NULL,
    product_width_max int(8) NULL,
    product_width_unit varchar(8) NULL,
    product_height_min int(8) NULL,
    product_height_max int(8) NULL,
    product_height_unit varchar(8) NULL,
    product_length_min int(8) NULL,
    product_length_max int(8) NULL,
    product_length_unit varchar(8) NULL,
    product_weight_min int(8) NULL,
    product_weight_max int(8) NULL,
    product_weight_unit varchar(8) NULL,
    product_slug varchar(255) NOT NULL,
    product_parent_id int(16),
    product_service_count int(8),
    PRIMARY KEY (product_id),
    INDEX idx (product_code, product_slug)
) ENGINE=InnoDB $collate;
");

		dbDelta("
CREATE TABLE " . self::get_products_services_table() . " (
	product_service_id bigint(20) NOT NULL AUTO_INCREMENT,
    product_service_product_id int(8) NOT NULL,
    product_service_product_parent_id int(16) NOT NULL,
    product_service_slug varchar(255) NOT NULL,
    PRIMARY KEY (product_service_id)
) ENGINE=InnoDB $collate;
COMMIT;
");
	}

	/**
	 * Returns the database table name for products.
	 *
	 * @static
	 *
	 * @return string
	 */
	private static function get_products_table() : string {
		global $wpdb;

		return $wpdb->prefix . 'wgm_dhl_internetmarke_products';
	}

	/**
	 * Returns the database table name for product services.
	 *
	 * @static
	 *
	 * @return string
	 */
	private static function get_products_services_table() : string {
		global $wpdb;

		return $wpdb->prefix . 'wgm_dhl_internetmarke_products_services';
	}

	/**
	 * Returns all formatted data of a specific product.
	 *
	 * @param int $internetmarke_product_id
	 *
	 * @return array
	 */
	public function get_product_data( int $internetmarke_product_id ) : array {

		$product = is_numeric( $internetmarke_product_id ) ? $this->get_product_data_by_code( $internetmarke_product_id ) : $internetmarke_product_id;

		$formatted = array(
			'product_title_formatted'            => '',
			'product_price_formatted'            => '',
			'product_description_formatted'      => '',
			'product_information_text_formatted' => '',
			'product_dimensions_formatted'       => '',
		);

		if ( ! $product || ! isset( $product[ 'product_id' ] ) ) {
			return $formatted;
		}

		$dimensions       = array();
		$formatted_length = $this->format_dimensions( $product, 'length' );
		$formatted_width  = $this->format_dimensions( $product, 'width' );
		$formatted_height = $this->format_dimensions( $product, 'height' );
		$formatted_weight = $this->format_dimensions( $product, 'weight' );

		if ( ! empty( $formatted_length ) ) {
			$dimensions[] = sprintf( '<b>' . __( 'Length', 'woocommerce-german-market' ) . ':</b> %s', $formatted_length );
		}

		if ( ! empty( $formatted_width ) ) {
			$dimensions[] = sprintf( '<b>' . __( 'Width', 'woocommerce-german-market' ) . ':</b> %s', $formatted_width );
		}

		if ( ! empty( $formatted_height ) ) {
			$dimensions[] = sprintf( '<b>' . __( 'Height', 'woocommerce-german-market' ) . ':</b> %s', $formatted_height );
		}

		if ( ! empty( $formatted_weight ) ) {
			$dimensions[] = sprintf( '<b>' . __( 'Weight', 'woocommerce-german-market' ) . ':</b> %s', $formatted_weight );
		}

		$formatted = array_merge( (array) $product, array(
			'product_title_formatted'             => ucwords( str_replace( array( 'integral', 'Ä' ), array( '', 'ä' ), strtolower( $product[ 'product_name' ] ) ) ),
			'product_price_formatted'             => wc_price( $product[ 'product_price' ] / 100, array( 'currency' => 'EUR' ) ),
			'product_description_formatted'       => ! empty( $product[ 'product_annotation' ] ) ? $product[ 'product_annotation' ] : $product[ 'product_description' ],
			'product_information_text_formatted'  => preg_replace( '/([bis]+\s[\d]+\s[g|EUR]+)/i', '<b>$1</b>', $product[ 'product_information_text' ] ),
			'product_dimensions_formatted'        => implode( '<br/>', $dimensions ),
		) );

		return $formatted;
	}

	/**
	 * Returns the product array.
	 *
	 * @param int $internal_product_id
	 *
	 * @return array product array
	 */
	public function get_product_data_by_internal_product_id( int $internal_product_id ) : array {

		if ( $internal_product_id && ( 0 < count( $this->internetmarke_products ) ) ) {
			foreach ( $this->internetmarke_products as $product ) {
				$product = json_decode( json_encode( $product ), true );
				if ( $internal_product_id == $product[ 'product_id' ] ) {
					return $product;
				}
			}
		};
	}

	/**
	 * Returns the product array.
	 *
	 * @param int $internetmarke_product_id
	 *
	 * @return array product array
	 */
	public function get_product_data_by_code( int $internetmarke_product_id ) : array {

		if ( $internetmarke_product_id && ( 0 < count( $this->internetmarke_products ) ) ) {
			foreach ( $this->internetmarke_products as $product ) {
				$product = json_decode( json_encode( $product ), true );
				if ( $internetmarke_product_id == $product[ 'product_im_id' ] ) {
					return $product;
				}
			}
		};
	}

	/**
	 * Gets the dimension attributes of from product object.
	 *
	 * @acces public
	 *
	 * @param array  $product
	 * @param string $type
	 *
	 * @return string
	 */
	public function format_dimensions( array $product, string $type = 'length' ) : string {

		$dimension = '';

		if ( ! empty( $product[ 'product_' . $type . '_min' ] ) ) {
			$dimension .= $product[ 'product_' . $type . '_min' ];

			if ( ! empty( $product[ 'product_' . $type . '_max' ] ) ) {
				$dimension .= '-' . $product[ 'product_' . $type . '_max' ];
			}
		} else
			if ( 0 == $product[ 'product_' . $type . '_min' ] ) {
				$dimension = sprintf( __( 'up to %s', 'woocommerce-german-market' ), $product[ 'product_' . $type . '_max' ] );
			}

		if ( ! empty( $dimension ) ) {
			$dimension .= ' ' . $product[ 'product_' . $type . '_unit' ];
		}

		return $dimension;
	}

	/**
	 * Returns an array of default available products.
	 *
	 * @access protected
	 *
	 * @return string[]
	 */
	protected function get_default_available_products(): array {

		return array(
			'11',    // Kompaktbrief
			'21',    // Großbrief
			'31',    // Maxibrief
			'282',   // Bücher- und Warensendung 500
			'290',   // Bücher- und Warensendung 1000
			'10001', // Standardbrief International GK
			'10011', // Kompaktbrief International GK
			'10051', // Großbrief International GK
			'10071', // Maxibrief International bis 1.000g GK
			'10091', // Maxibrief International bis 2.000g GK
		);
	}

	/**
	 * Returns the service title.
	 *
	 * @param string $service
	 * @param int    $service_product_id
	 *
	 * @return mixed|string
	 */
	public function get_additional_service_title( $service, $service_product_id ) {

		$services             = $this->get_additional_services();
		$service_product      = $this->get_product_data( $service_product_id );
		$service_product_name = mb_strtolower( $service_product[ 'product_name' ] );
		$services_identifiers = $this->get_additional_service_identifiers();
		$additional_services  = array();

		foreach ( $services_identifiers as $identifier_string => $identifier ) {
			if ( false !== strpos( $service_product_name, $identifier_string ) ) {
				if ( 'ESCH' === $identifier ) {
					if ( in_array( 'ESEW', $additional_services ) || in_array( 'ESEH', $additional_services ) ) {
						continue;
					}
				}
				$additional_services[] = $identifier;
			}
		}

		$service_title = array();

		foreach ( $additional_services as $service_key ) {
			if ( array_key_exists( $service_key, $services ) ) {
				$service_title[] = $services[ $service_key ];
			}
		}

		return implode( ' + ', $service_title );
	}

	/**
	 * Returns additional services identifiers.
	 *
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_additional_service_identifiers() : array {

		return array(
			'+ einschreiben einwurf'       => 'ESEW',
			'+ einschreiben + einwurf'     => 'ESEW',
			'+ einschreiben + eigenhändig' => 'ESEH',
			'+ einschreiben'               => 'ESCH',
			'+ zusatzentgelt mbf'          => 'ZMBF',
			'+ prio'                       => 'PRIO',
			'unterschrift'                 => 'USFT',
			'tracked'                      => 'TRCK',
		);
	}

	/**
	 * Gets additional services.
	 *
	 * @return array
	 */
	public function get_additional_services() : array {

		return array(
			'PRIO' => __( 'PRIO', 'woocommerce-german-market' ),
			'ESEW' => __( 'Einschreiben (Einwurf)', 'woocommerce-german-market' ),
			'ESCH' => __( 'Einschreiben', 'woocommerce-german-market' ),
			'ESEH' => __( 'Einschreiben (Eigenhändig)', 'woocommerce-german-market' ),
			'AS16' => __( 'Alterssichtprüfung 16', 'woocommerce-german-market' ),
			'AS18' => __( 'Alterssichtprüfung 18', 'woocommerce-german-market' ),
			'ZMBF' => __( 'Zusatzentgelt MBf', 'woocommerce-german-market' ),
			'USFT' => __( 'Unterschrift', 'woocommerce-german-market' ),
			'TRCK' => __( 'Tracked', 'woocommerce-german-market' ),
		);
	}

	/**
	 * @acces public
	 *
	 * @param $slug
	 *
	 * @return bool
	 */
	public function is_additional_service( $slug ) : bool {

		$service_slug = $this->get_product_service_slugs( $slug );

		return ! empty( $service_slug );
	}

	/**
	 * @param $slug
	 *
	 * @return array|string|string[]|null
	 */
	public function get_product_base_slug( $slug ) {

		$additional_services = $this->get_additional_service_identifiers();
		$slug                = str_replace( 'integral', '', $slug );

		foreach( array_keys( $additional_services ) as $identifier ) {
			$slug = str_replace( $identifier, ' ', $slug );
		}

		return $this->sanitize_product_slug( $slug );
	}

	/**
	 * @param $slug
	 *
	 * @return array
	 */
	protected function get_product_service_slugs( $slug ) : array {

		$service_slugs    = array();
		$has_einschreiben = false;

		foreach( $this->get_additional_service_identifiers() as $identifier => $service ) {
			if ( false !== strpos( $slug, $identifier ) ) {
				if ( false !== strpos( $identifier, 'einschreiben' ) ) {
					if ( ! $has_einschreiben ) {
						$has_einschreiben = true;
						$service_slugs[]  = $service;
					}
				} else
					if ( false !== strpos( $slug, $identifier ) ) {
						$service_slugs[] = $service;
					}
			}
		}

		return array_unique( $service_slugs );
	}

	/**
	 * Gets the product category.
	 *
	 * @param string $product_slug
	 * @param string $product_destination
	 *
	 * @return string
	 */
	public function sanitize_product_category( string $product_slug, string $product_destination ) : string {

		/*
		National
		-- Brief / Postkarte
		-- Presse
		International
		-- Brief / Postkarte
		-- Presse
		 */

		$category = '';

		// Presse
		if ( false !== strpos( $product_slug, 'presse' ) ||
		     false !== strpos( $product_slug, 'streifbandzeitung' ) ||
		     false !== strpos( $product_slug, 'bücher- und warensendung' )
		) {
			$category = 'presse' . ( 'international' === $product_destination ? '-international' : '' );
		} else
			// Brief / Postkarte
			if ( false !== strpos( $product_slug, 'postkarte' ) ||
			     false !== strpos( $product_slug, 'standardbrief' ) ||
			     false !== strpos( $product_slug, 'kompaktbrief' ) ||
			     false !== strpos( $product_slug, 'großbrief' ) ||
			     false !== strpos( $product_slug, 'maxibrief' )
			) {
				$category = 'brief-postkarte' . ( 'international' === $product_destination ? '-international' : '' );
			} else
				// Brief Kilo-Tarif
				if ( false !== strpos( $product_slug, 'brief kilotarif' )
				) {
					$category = 'brief-kilotarif' . ( 'international' === $product_destination ? '-international' : '' );
				}

		return $category;
	}

	/**
	 * Gets the product slug.
	 *
	 * @param string $product_name
	 *
	 * @return array|string|string[]|null
	 */
	public function sanitize_product_slug( string $product_name ) {

		$product_name = trim( mb_strtolower( $product_name ) );

		// Remove duplicate whitespaces
		return preg_replace( '/\s+/', ' ', $product_name );
	}

	/**
	 * Getting dimensions from
	 *
	 * @param $dimensions
	 * @param string $type
	 *
	 * @return null[]
	 */
	public function get_dimensions( $dimensions, string $type = 'width' ) : array {

		$data = array(
			'product_' . $type . '_min'  => null,
			'product_' . $type . '_max'  => null,
			'product_' . $type . '_unit' => null,
		);

		if ( property_exists( $dimensions, $type ) ) {
			$d = $dimensions->{ $type };

			$data[ 'product_' . $type . '_min' ]  = property_exists( $d, 'minValue' ) ? $d->minValue : null;
			$data[ 'product_' . $type . '_max' ]  = property_exists( $d, 'maxValue' ) ? $d->maxValue : null;
			$data[ 'product_' . $type . '_unit' ] = property_exists( $d, 'unit' ) ? $d->unit : null;
		}

		return $data;
	}

	/**
	 * Function to extract the information text from object.
	 *
	 * @param array $stamp_type
	 *
	 * @return mixed
	 */
	public function get_information_text( array $stamp_type ) {
		$information_text = '';

		foreach ( $stamp_type as $stamp ) {
			if  ( isset( $stamp->name ) && 'Internetmarke' == $stamp->name ) {
				foreach ( $stamp->propertyList as $properties ) {
					foreach ( $properties as $property ) {
						if ( 'InformationText' == $property->name ) {
							$information_text = $property->propertyValue->alphanumericValue->fixValue;
						}
					}
				}
			}
		}

		return $information_text;
	}

	/**
	 * Generate the Internetmarke Wizard HTML into footer area when we are in an DHL order.
	 *
	 * @Hook admin_footer
	 *
	 * @return void
	 */
	public function generate_internetmarke_wizard() {
		global $post, $pagenow;

		if ( ( isset( $post ) && ! empty( $post->ID ) ) || ( ( 'admin.php' === $pagenow ) && ! empty( $_GET[ 'page' ] ) && ( 'wc-orders' === $_GET[ 'page' ] ) && isset( $_GET[ 'action' ] ) && ( 'edit' === $_GET[ 'action'] ) && ! empty( $_GET[ 'id' ] ) ) ) {

			$order_id = ( ! empty( $post ) && property_exists( $post, 'ID' ) ) ? $post->ID : null;

			if ( empty( $order_id ) ) {
				if ( isset( $_GET[ 'id' ] ) ) {
					$order_id = $_GET[ 'id' ];
				}
			}

			$order            = wc_get_order( $order_id );
			$sender_address   = array();
			$shipping_address = array();

			// generate sender address
			if ( '' !== Shipping_Provider::$options->get_option( 'shipping_shop_address_company', '' ) ) {
				$sender_address[] = Shipping_Provider::$options->get_option( 'shipping_shop_address_company' );
			} else {
				$sender_address[] = Shipping_Provider::$options->get_option( 'shipping_shop_address_name', '' );
			}
			$sender_address[] = Shipping_Provider::$options->get_option( 'shipping_shop_address_street', '' ) . ' ' . Shipping_Provider::$options->get_option( 'shipping_shop_address_house_no', '' );
			$sender_address[] = Shipping_Provider::$options->get_option( 'shipping_shop_address_country', '' ) . '-' . Shipping_Provider::$options->get_option( 'shipping_shop_address_zip_code' ) . ' ' . Shipping_Provider::$options->get_option( 'shipping_shop_address_city', '' );

			if ( is_object( $order ) ) {

				// just set if company is not empty
				if ( '' !== $order->get_shipping_company() ) {
					$shipping_address[] = $order->get_shipping_company();
				}

				$shipping_address[] = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
				$shipping_address[] = $order->get_shipping_address_1();

				// just set if apartment / suite field is not empty
				if ( '' !== $order->get_shipping_address_2() ) {
					$shipping_address[] = $order->get_shipping_address_2();
				}

				$shipping_address[] = $order->get_shipping_country() . '-' . $order->get_shipping_postcode() . ' ' . $order->get_shipping_city();
			}

			?>
			<div id="internetmarke_wizard">
				<div id="wizard-wrapper">
					<!-- Internetmarke Wizard Form -->
					<form name="internetmarke-wizard">
						<input type="hidden" name="wc_order_id" id="wc_order_id" value="<?php echo $order_id ?>" />
						<div id="internetmarke_checkout_wrapper">
							<!-- Title & Close Button -->
							<div id="internetmarke_checkout_wrapper_header">
								<b><?php echo __( 'Internetmarke - Wizard', 'woocommerce-german-market' ); ?></b>
								<a href="#" class="close" role="button" aria-label="<?php echo __( 'Close Wizard', 'woocommerce-german-market' ); ?>" title="<?php echo __( 'Close Wizard', 'woocommerce-german-market' ); ?>">x</a>
							</div>
							<div id="internetmarke_checkout_wrapper_inner">
								<!-- Step 1 -->
								<div id="internetmarke_checkout_step_1">
									<div class="header">
										<div class="categories clearfix">
											<div class="national col-6">
												<h3><?php echo __( 'National', 'woocommerce-german-market' ); ?></h3>
												<ul>
													<li id="brief-postkarte" tabindex="1"><?php echo __( 'Letter Mail', 'woocommerce-german-market' ); ?></li>
													<li id="presse" tabindex="2"><?php echo __( 'Media', 'woocommerce-german-market' ); ?></li>
												</ul>
											</div>
											<div class="national col-6 last">
												<h3><?php echo __( 'International (Business)', 'woocommerce-german-market' ); ?></h3>
												<ul>
													<li id="brief-postkarte-international" tabindex="1"><?php echo __( 'Letter Mail', 'woocommerce-german-market' ); ?></li>
												</ul>
											</div>
										</div>
									</div>
									<div class="content">
										<div class="products col-4">
											<div class="product-list-wrapper">
												<h3><?php echo __( 'Shipping product', 'woocommerce-german-market' ); ?> </h3>
												<ul class="product-list"><!-- --></ul>
											</div>
											<div class="voucher-layout-wrapper">
												<h3><?php echo __( 'Voucher Layout', 'woocommerce-german-market' ); ?></h3>
												<ul class="voucher-layout">
													<li><input role="checkbox" type="radio" name="voucherLayout" value="AddressZone" checked="checked"> <?php echo __( 'Stamp for Reading Zone', 'woocommerce-german-market' ); ?></li>
													<li><input role="checkbox" type="radio" name="voucherLayout" value="FrankingZone"> <?php echo __( 'Stamp for Franking Area', 'woocommerce-german-market' ); ?></li>
												</ul>
											</div>
										</div>
										<div class="additionals col-4">
											<div class="services">
												<h3><?php echo __( 'Available additional services', 'woocommerce-german-market' ); ?> </h3>
												<ul><!-- --></ul>
											</div>
										</div>
										<div class="preview col-4 last">
											<h3><?php echo __( 'Preview', 'woocommerce-german-market' ); ?></h3>
											<div class="label">
												<div class="sender-address">
													<?php echo implode( ', ', $sender_address ); ?>
												</div>
												<div class="shipping-address">
													<?php echo implode( '<br/>', $shipping_address ); ?>
												</div>
											</div>
											<div class="total">
												<p>&nbsp;</p>
												<b><?php echo __( 'Total', 'woocommerce-german-market' ); ?></b>
											</div>
										</div>
									</div>
									<div class="description">
										<div class="dimensions col-4">
											<h3><?php echo __( 'Allowed Dimensions', 'woocommerce-german-market' ); ?></h3>
											<div><!-- --></div>
										</div>
										<div class="information-text col-8 last">
											<h3><?php echo __( 'Description', 'woocommerce-german-market' ); ?> <span class="product-title"><!-- --></span></h3>
											<div><!-- --></div>
										</div>
									</div>
									<div class="footer">
										<div class="col-6">
											&nbsp;
										</div>
										<div class="col-6 last">
											<button class="button-primary add-to-cart"><?php echo __( 'Add to Cart', 'woocommerce-german-market' ); ?></button>
											<button class="button-primary next disabled"><?php echo __( 'Next step', 'woocommerce-german-market' ); ?></button>
										</div>
									</div>
								</div>
								<!-- Step 2 -->
								<div id="internetmarke_checkout_step_2">
									<div class="header">
										<!-- -->
									</div>
									<div class="content">
										<div class="portokasse col-4">
											<h3><?php echo __( 'Your Portokasse', 'woocommerce-german-market' ); ?> </h3>
											<ul>
												<li class="current">
													<div class="portokasse-title">
														<input type="radio" name="portokasse" value="<?php echo Shipping_Provider::$options->get_option( 'internetmarke_portokasse_email', '' ); ?>" checked="checked" /> <?php echo Shipping_Provider::$options->get_option( 'internetmarke_portokasse_email', '' ); ?>
													</div>
													<div class="portokasse-balance">
														<?php echo __( 'Balance', 'woocommerce-german-market'); ?><span class="value"><?php echo $this->format_price( $this->walletBalance / 100 ); ?></span>
													</div>
												</li>
											</ul>
										</div>
										<div class="product-summary col-4">
											<h3><?php echo __( 'Products & Quantity', 'woocommerce-german-market' ); ?> </h3>
											<ul>
												<li class="empty-cart"><?php echo __( 'Your cart is empty.', 'woocommerce-german-market' ); ?></li>
											</ul>
											<button class="button add-product"><?php echo __( '+ Add product', 'woocommerce-german-market' ); ?></button>
											<button class="button update-cart"><?php echo __( 'Update Cart', 'woocommerce-german-market' ); ?></button>
										</div>
										<div class="product-totals col-4 last">
											<h3><?php echo __( 'Cart', 'woocommerce-german-market' ); ?> </h3>
											<ul>
												<li class="empty-cart">
													<?php echo __( 'Your cart is empty.', 'woocommerce-german-market' ); ?>
												</li>
												<li class="totals">
													<?php echo __( 'Total', 'woocommerce-german-market' ); ?> <span class="price"><!-- --></span>
												</li>
											</ul>
										</div>
									</div>
									<div class="description">
										<small><?php echo __( 'Please note that all and any changes made will only be applied to the resulting shipment and its respective shipping label. The original order/product data will not be modified.', 'woocommerce-german-market' ); ?></small>
									</div>
									<div class="footer">
										<div class="col-6">
											<button class="button-primary prev"><?php echo __( 'Previous step', 'woocommerce-german-market' ); ?></button>
										</div>
										<div class="col-6 last">
											<button class="button-primary order"><?php echo __( 'Place order', 'woocommerce-german-market' ); ?></button>
										</div>
									</div>
								</div>
								<!-- Step 3 - Thankyou page -->
								<div id="internetmarke_checkout_step_3">
									<div class="header">
										&nbsp;
									</div>
									<div class="content">
										<div class="col-12 last">
											<div class="wrapper-success">
												<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
													<circle class="path circle" fill="none" stroke="#73AF55" stroke-width="6" stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1"/>
													<polyline class="path check" fill="none" stroke="#73AF55" stroke-width="6" stroke-linecap="round" stroke-miterlimit="10" points="100.2,40.2 51.5,88.8 29.8,67.5 "/>
												</svg>
												<p class="info-success"><?php echo __( 'The Internetmarke label(s) have been created successfully.', 'woocommerce-german-market'); ?></p>
												<p class="info-additional"><?php echo __( 'Please click the button below to download the label(s).<br>You can also download it later using the button within the order after reload the page.', 'woocommerce-german-market'); ?></p>
												<p class="info-download-button"><a href="#" class="button-primary" target="_blank"><?php echo __( 'Download Label', 'woocommerce-german-market'); ?></a></p>
											</div>
											<div class="wrapper-error">
												<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
													<circle class="path circle" fill="none" stroke="#D06079" stroke-width="6" stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1"/>
													<line class="path line" fill="none" stroke="#D06079" stroke-width="6" stroke-linecap="round" stroke-miterlimit="10" x1="34.4" y1="37.9" x2="95.8" y2="92.3"/>
													<line class="path line" fill="none" stroke="#D06079" stroke-width="6" stroke-linecap="round" stroke-miterlimit="10" x1="95.8" y1="38" x2="34.4" y2="92.2"/>
												</svg>
												<p class="info-error"><?php echo __( 'Oh no, an error occurred.', 'woocommerce-german-market'); ?></p>
												<p class="info-additional"><!-- --></p>
											</div>
										</div>
									</div>
								</div>
							</div> <!-- /internetmarke_checkout_wrapper_inner -->
						</div> <!-- /internetmarke_checkout_wrapper -->
					</form>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Processing AJAX checkout request.
	 *
	 * @Hook wp_ajax_woocommerce_internetmarke_process_checkout
	 *
	 * @return string json format, die()
	 * @throws SoapFault
	 */
	public function process_checkout() : string {

		check_ajax_referer( 'internetmarke_checkout_nonce', 'nonce' );

		$wc_order_id  = $_REQUEST[ 'order_id' ];
		$order_data   = $_REQUEST[ 'order' ];
		$order        = wc_get_order( $wc_order_id );
		$positions    = array();
		$total        = 0;
		$response     = array();

		if ( null === $this->api ) {
			$this->connect();
		}

		if ( is_null( $this->internetmarke_products ) || is_null( $this->internetmarke_product_services ) ) {
			$this->load_products();
		}

		if ( is_object( $order ) && method_exists( $order, 'get_shipping_address_1' ) ) {

			$im_order_id            = $this->createShopOrderId( $this->authToken );
			$im_stamp_output_format = Shipping_Provider::$options->get_option( 'internetmarke_stamp_result_format', 'pdf' );
			$im_page_format         = Shipping_Provider::$options->get_option( 'internetmarke_page_format', '' );

			foreach ( $order_data as $item ) {

				$product_id       = (int) $item[ 'product_im_id' ];
				$product_data     = $this->get_product_data_by_internal_product_id( $product_id );
				$product_code     = (int) $product_data[ 'product_code' ];
				$product_layout   = $item[ 'voucher_layout' ];
				$product_price    = (int) preg_replace( '/[^\d]/', '', $item[ 'product_price' ] ); // price in eurocents
				$product_quantity = (int) $item[ 'quantity' ];
				$address          = array();

				// Sender
				if ( '' !== Shipping_Provider::$options->get_option( 'shipping_shop_address_company', '' ) ) {
					$address[ 'sender' ][ 'name' ] = array(
						'companyName' => array(
							'company'    => Shipping_Provider::$options->get_option( 'shipping_shop_address' ),
							'personName' => array(
								'firstname' => Helper::get_person_name_part( Shipping_Provider::$options->get_option( 'shipping_shop_address_name', '' ), 'firstname' ),
								'lastname'  => Helper::get_person_name_part( Shipping_Provider::$options->get_option( 'shipping_shop_address_name', '' ), 'lastname' ),
							)
						)
					);
				} else {
					$address[ 'sender' ][ 'name' ] = array(
						'personName' => array(
							'firstname' => Helper::get_person_name_part( Shipping_Provider::$options->get_option( 'shipping_shop_address_name', '' ), 'firstname' ),
							'lastname'  => Helper::get_person_name_part( Shipping_Provider::$options->get_option( 'shipping_shop_address_name', '' ), 'lastname' ),
						)
					);
				}
				$address[ 'sender' ][ 'address' ] = array(
					'street'  => Shipping_Provider::$options->get_option( 'shipping_shop_address_street', '' ),
					'houseNo' => Shipping_Provider::$options->get_option( 'shipping_shop_address_house_no', '' ),
					'zip'     => Shipping_Provider::$options->get_option( 'shipping_shop_address_zip_code', '' ),
					'city'    => Shipping_Provider::$options->get_option( 'shipping_shop_address_city', '' ),
				);
				// Converting shipper country code to 3-alpha-iso code.
				$country_data                                 = ( new ISO3166() )->alpha2( Shipping_Provider::$options->get_option( 'shipping_shop_address_country', 'DE' ) );
				$address[ 'sender' ][ 'address' ][ 'country'] = $country_data[ 'alpha3' ];


				// Receiver
				if ( '' !== $order->get_shipping_company() ) {
					$address[ 'receiver' ][ 'name' ] = array(
						'companyName' => array(
							'company' => $order->get_shipping_company(),
							'personName' => array(
								'firstname' => $order->get_shipping_first_name(),
								'lastname'  => $order->get_shipping_last_name()
							)
						)
					);
				} else {
					$address[ 'receiver' ][ 'name' ] = array(
						'personName' => array(
							'firstname' => $order->get_shipping_first_name(),
							'lastname'  => $order->get_shipping_last_name()
						)
					);
				}
				$address[ 'receiver' ][ 'address' ] = array(
					'street'  => Helper::split_street( $order->get_shipping_address_1(), 'street' ),
					'houseNo' => Helper::split_street( $order->get_shipping_address_1(), 'house_no' ),
					'zip'     => $order->get_shipping_postcode(),
					'city'    => $order->get_shipping_city(),
				);
				// Converting shipper country code to 3-alpha-iso code.
				$country_data                                    = ( new ISO3166() )->alpha2( $order->get_shipping_country() );
				$address[ 'receiver' ][ 'address' ][ 'country' ] = $country_data[ 'alpha3' ];

				// adding each quantity as a single product
				for ( $i = 0; $i < $product_quantity; $i++ ) {
					$total += $product_price;
					$positions[] = array(
						'productCode'   => $product_code,
						//'imageId'      => 1335564250, // for custom image (FrankingZone)
						'voucherLayout' => $product_layout,
						'address'       => array(
							'sender'        => deep_copy( $address[ 'sender' ] ),
							'receiver'      => deep_copy( $address[ 'receiver' ] ),
						),
						'position'      => array(
							'labelX'        => 1,
							'labelY'        => 1,
							'page'          => 1,
						),
					);
				}
			}

			if ( 'png' === $im_stamp_output_format ) {
				$result = $this->checkoutShoppingCartPng( $this->authToken, $positions, $total, $im_order_id );
			} else
				if ( 'pdf' === $im_stamp_output_format ) {
					$result = $this->checkoutShoppingCartPdf( $this->authToken, $im_page_format, $positions, $total, $im_order_id );
				}

			if ( isset( $result->link ) && '' != $result->link ) {

				$download_url = $result->link;

				// downloading file from Deutsche Post server using PHP Curl extension.
				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $download_url );
				curl_setopt( $ch, CURLOPT_VERBOSE, true );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
				curl_setopt( $ch, CURLOPT_HEADER, false );
				$data = curl_exec( $ch );
				curl_close( $ch );

				if ( $data ) {
					$order->update_meta_data( '_wgm_internetmarke_output_type', $im_stamp_output_format );
					$order->update_meta_data( '_wgm_internetmarke_binary_label_data', base64_encode( $data ) );
					$order->save();
				}

				// Updateing the Wallet Balance
				$this->walletBalance = $result->walletBallance;

				$response = array(
					'result_code' => 'success',
					'im_order_id' => $im_order_id,
					'link'        => $this->create_label_download_link( $wc_order_id ),
					'balance'     => $this->walletBalance,
					'result'      => $result,
				);

			} else
				if ( isset( $result->faultcode ) && isset( $result->faultstring ) ) {

					if ( isset( $result->detail->ShoppingCartValidationException->errors->id ) ) {
						$api_error_code = $result->detail->ShoppingCartValidationException->errors->id;
						$api_error_msg  = $this->return_api_error_message( $result->detail->ShoppingCartValidationException->errors->id );
					} else {
						$api_error_code = $result->faultcode;
						$api_error_msg  = $result->faultstring;
					}

					$response = array(
						'result_code'    => 'error',
						'api_error_code' => $api_error_code,
						'api_error_msg'  => $api_error_msg,
						'result'         => $result,
					);
				}

		} else {
			$response[] = array( 'result_code' => 'error. woocommerce order not found.' );
		}

		echo json_encode( $response );
		die();
	}

	/**
	 * Generates a download link for the stored Internetmarke labels.
	 *
	 * @access public
	 *
	 * @param int $order_id
	 *
	 * @return string download url
	 */
	public function create_label_download_link( int $order_id ) : string {

		$url = admin_url( 'admin-ajax.php?action=woocommerce_dhl_shipping_internetmarke_label_download&order_id=' . $order_id );

		return add_query_arg( '_wpnonce', wp_create_nonce( 'wp-wc-internetmarke-label-download' ), $url );
	}

	/**
	 * download frontend
	 *
	 * @Hook wp_ajax_woocommerce_dhl_shipping_internetmarke_label_download
	 *
	 * @static
	 *
	 * @return void die()
	 */
	public static function download_label() {

		check_admin_referer( 'wp-wc-internetmarke-label-download' );

		// init
		$order_id   = $_REQUEST[ 'order_id' ];
		$order      = wc_get_order( $order_id );
		$stamp_type = $order->get_meta( '_wgm_internetmarke_output_type', true );
		$stamp_data = base64_decode( $order->get_meta( '_wgm_internetmarke_binary_label_data', true ) );

		if ( '' != $stamp_type && '' != $stamp_data ) {

			switch ( $stamp_type ) {
				case 'png':
					// output will be a ZIP file
					header( 'Content-Type: application/zip' );
					header( 'Content-Transfer-Encoding: Binary' );
					header( 'Content-Length: ' . strlen( $stamp_data ) );
					header( 'Content-Disposition: attachment; filename="dhl_shipping_internetmarke_label_order_' . $order_id . '.zip"' );
					break;
				case 'pdf':
					// output as PDF
					header( 'Content-Description: File Transfer' );
					header( 'Content-Type: application/pdf' );
					header( 'Content-Disposition: attachment; filename="dhl_shipping_internetmarke_label_order_' . $order_id . '.pdf"' );
					header( 'Content-Transfer-Encoding: binary' );
					header( 'Connection: Keep-Alive' );
					header( 'Expires: 0' );
					header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
					header( 'Pragma: public' );
					break;
			}

			print( $stamp_data );
		}

		die();
	}

	/**
	 * Returns a user-friendly API error message.
	 *
	 * @param string $api_error_code
	 *
	 * @return string
	 */
	public function return_api_error_message( string $api_error_code ) : string {

		$msg = '';
		if ( '' != $api_error_code ) {
			switch ( strtolower( $api_error_code ) ) {
				case 'invaliduser':
					$msg = __( 'The user account is not available or a wrong usertoken was submitted.', 'woocommerce-german-market' );
					break;
				case 'invalidproductcode':
					$msg = __( 'The chosen product is not available in the current PPL or it\'s not authorized for the Portokasse.', 'woocommerce-german-market' );
					break;
				case 'walletbalancenotenough':
					$msg = __( 'The available amount of the Portokasse is lower than the cart amount.', 'woocommerce-german-market' );
					break;
				case 'walletnotavailable':
					$msg = __( 'There are no Portkasse accounts available at the moment.', 'woocommerce-german-market' );
					break;
				case 'invalidmotive':
					$msg = __( 'Invalid motif ID given.', 'woocommerce-german-market' );
					break;
				case 'invalidorderpositioncount':
					$msg = __( 'The numbers of products in the cart is greater than the maximum limit of cart.', 'woocommerce-german-market' );
					break;
				case 'invalidtotalamount':
					$msg = __( 'The given total amount of cart doesnt fit to the amount calculated by the server.', 'woocommerce-german-market' );
					break;
				case 'invalidshoporderid':
					$msg = __( 'The submitted Shop Order ID is not valid.', 'woocommerce-german-market' );
					break;
			}

			$msg .= ' (Error-Code: ' . $api_error_code . ')';
		}

		return $msg;
	}

	/**
	 * Loading product catalog from API.
	 *
	 * @Hook wp_ajax_woocommerce_internetmarke_load_products_and_services_by_category
	 *
	 * @return string json format, die()
	 */
	public function load_products_and_services_by_category() : string {

		check_ajax_referer( 'internetmarke_checkout_nonce', 'nonce' );

		$default_products = $this->get_default_available_products();
		$category_slug    = $_REQUEST[ 'category_slug' ];
		$response         = array();

		foreach ( $this->internetmarke_products as $product ) {
			$product = json_decode( json_encode( $product ), true );

			if ( in_array( $product[ 'product_code' ], $default_products ) && ( $category_slug == $product[ 'product_category' ] ) ) {
				$formatted_data        = $this->get_product_data( $product[ 'product_im_id' ] );
				$product               = json_decode( json_encode( $product ), true );
				$product[ 'services' ] = $this->get_additional_product_services( $product[ 'product_id' ] );
				$response[]            = array_merge( $formatted_data, $product );
			}
		}

		// sorting products array by product_price
		$price = array_column( $response, 'product_price' );
		array_multisort($price, SORT_ASC, $response );

		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Returns the additional services for a product.
	 *
	 * @access protected
	 *
	 * @param int $product_id internal product id
	 *
	 * @returns array
	 */
	protected function get_additional_product_services( int $product_id ) : array {

		$services      = array();
		$product_codes = array();

		if ( $product_id && ( count( $this->internetmarke_product_services ) > 0 ) ) {

			foreach ( $this->internetmarke_product_services as $service ) {
				$service = json_decode( json_encode( $service ), true );
				if ( $product_id == $service[ 'product_service_product_parent_id' ] ) {
					$service_product = $this->get_service_product_by_id( $service[ 'product_service_product_id' ] );
					if ( ! in_array( $service_product[ 'product_code' ], $product_codes ) ) {
						$product_codes[] = $service_product[ 'product_code' ];
						$services[]      = array(
							'service'              => $service,
							'service_slug'         => $service[ 'product_service_slug' ],
							'service_title'        => $this->get_additional_service_title( $service[ 'product_service_slug' ], $service_product[ 'product_im_id' ] ),
							'service_product'      => $this->get_product_data( $service_product[ 'product_im_id' ] ),
							'service_price'        => $service_product[ 'product_price' ] / 100,
							'service_weight'       => $service_product[ 'product_weight_max' ],
						);
					}
				}
			}

			// sorting service products array by weight followed by price
			$weight = array_column( $services, 'service_weight' );
			$price  = array_column( $services, 'service_price' );
			array_multisort($weight, SORT_ASC, $price, SORT_ASC, $services );
		}

		if ( 0 == count( $services ) ) {
			$services[] = array(
				'no_service_available' => '1',
				'information_text'     => __( 'No additional services available.', 'woocommerce-german-market' ),
			);
		}

		return $services;
	}

	/**
	 * Returns available service slugs for a certain (parent) product.
	 *
	 * In case additional services chosen are supplied, only those services (e.g. Zusatzentgelt MBf which is only available if EINSCHREIBEN has been selected)
	 * are added which are compatible with the current selection.
	 *
	 * @param int   $parent_id
	 * @param array $services
	 *
	 * @return string[]
	 */
	public function get_services_for_product( int $parent_id, array $services = array( 'ESCH' ) ) : array {
		global $wpdb;

		$query = "SELECT * FROM " . self::get_products_table();
		$count = 1;

		if ( empty( $services ) ) {
			$query .= " INNER JOIN " . self::get_products_services_table() . " S{$count} ON " . self::get_products_table() . ".product_id = S{$count}.product_service_product_id";
		} else {
			foreach( $services as $service ) {
				$count++;

				$query .= $wpdb->prepare( " INNER JOIN " . self::get_products_services_table() . " S{$count} ON " . self::get_products_table() . ".product_id = S{$count}.product_service_product_id AND S{$count}.product_service_slug = %s", $service );
			}
		}

		$query .= $wpdb->prepare(" WHERE " . self::get_products_table() . ".product_parent_id = %d", $parent_id );

		if ( empty( $services ) ) {
			$query .= $wpdb->prepare(" AND " . self::get_products_table() . ".product_service_count = %d", 1 );
		}

		$results            = $wpdb->get_results( $query );
		$available_services = array();

		if ( ! empty( $results ) ) {
			foreach( $results as $result ) {
				$product_id       = $result->product_id;
				$product_services = $wpdb->get_results( $wpdb->prepare( "SELECT product_service_slug FROM " . self::update_products_services() . " WHERE " . self::get_products_services_table() . ".product_service_product_id = %d", $product_id ) );

				if ( ! empty( $product_services ) ) {
					foreach( $product_services as $product_service ) {
						$service_slug = $product_service->product_service_slug;

						if ( ! in_array( $service_slug, $available_services ) ) {
							$available_services[] = $service_slug;
						}
					}
				}
			}
		}

		return $available_services;
	}

	/**
	 * Returns the service product.
	 *
	 * @access protected
	 *
	 * @param $product_id
	 *
	 * @return array|void
	 */
	protected function get_service_product_by_id( $product_id ) {

		if ( $product_id && count( $this->internetmarke_products ) > 0 ) {
			foreach ( $this->internetmarke_products as $product ) {
				$product = json_decode( json_encode( $product ), true );
				if ( $product_id == $product[ 'product_id' ] ) return $product;
			}
		}
	}

	/**
	 * Loading image preview.
	 *
	 * @Hook wp_ajax_woocommerce_internetmarke_load_image_preview
	 *
	 * @return string
	 */
	public function load_image_preview() : string {

		check_ajax_referer( 'internetmarke_checkout_nonce', 'nonce' );

		$product_im_id  = (int) $_POST[ 'product_im_id' ];
		$product        = $this->get_product_data_by_code( $product_im_id );
		$voucher_layout = $_POST[ 'voucher_layout' ];

		if ( $product && '' != $voucher_layout ) {

			$product_code = $product[ 'product_code' ];
			$preview_url  = $this->retrievePreviewVoucherPng( $product_code, $voucher_layout );

			echo json_encode( array( 'link' => $preview_url ) );
		}

		wp_die();
	}

	/**
	 * Fetch a list of all possible page formats
	 *
	 * @return array
	 * @throws SoapFault
	 */
	public function retrieve_page_formats() : array {

		$array_result = array();

		if ( ! empty( get_transient( 'wgm_shipping_dhl_internetmarke_page_formats' ) ) ) {
			return get_transient( 'wgm_shipping_dhl_internetmarke_page_formats' );
		}

		$this->connect();

		if ( empty( $this->api ) ) {
			return $array_result;
		}

		$result = $this->api->__soapCall( 'retrievePageFormats', array() );

		if ( ! empty( $result->pageFormat ) ) {
			foreach( $result->pageFormat as $item ) {
				$array_result[] = json_decode( json_encode( $item ), true );;
			}
		}

		if ( ! empty( $array_result ) ) {
			set_transient( 'wgm_shipping_dhl_internetmarke_page_formats', $array_result, DAY_IN_SECONDS );
		}

		return $array_result;
	}

	/**
	 * Generate a unique order number (if your system doesn't generate its own)
	 *
	 * @access public
	 *
	 * @param string $auth_token string A token to authenticate the user (gotten from `authenticateUser`)
	 *
	 * @return string Next available shop order ID
	 */
	public function createShopOrderId( string $auth_token ) : string {

		if ( null === $this->api ) {
			$this->connect();
		}

		$result = $this->api->__soapCall( 'createShopOrderId', array(
			'CreateShopOrderIdRequest' => array(
				'userToken' => $auth_token
			)
		));

		return $result->shopOrderId;
	}

	/**
	 * Fetch a hierarchical structure of image categories and the images in those categories
	 *
	 * @access public
	 *
	 * @return PublicGalleryItem[]
	 */
	public function retrievePublicGallery() : array {

		$array_result = array();

		if ( null === $this->api ) {
			$this->connect();
		}

		$result = $this->api->__soapCall( 'retrievePublicGallery', array() );

		foreach( $result->items as $item ) {
			$array_result[] = PublicGalleryItem::fromStdObject( $item );
		}

		return $array_result;
	}

	/**
	 * Fetch the user's private image gallery
	 *
	 * @access public
	 *
	 * @param string $auth_token Token to authenticate the user (gotten from `authenticateUser`)
	 *
	 * @return array The user's images (empty if there are none)
	 */
	public function retrievePrivateGallery( string $auth_token ) : array {

		if ( null === $this->api ) {
			$this->connect();
		}

		return $this->api->__soapCall( 'retrievePrivateGallery', array(
			'RetrievePublicGalleryRequest' => array(
				'userToken' => $auth_token
			)
		));
	}

	/**
	 * Get a link to a preview of a stamp in PDF format
	 *
	 * @access public
	 *
	 * @param int    $product_code   A product code for the type of stamp (a list of products is only available via the separate ProdWS service)
	 * @param string $voucher_layout The layout of the stamp (possible values: 'FrankingZone' and 'AddressZone')
	 * @param int    $page_format_id ID of the page layout to be used (gotten from `retrieve_page_formats`)
	 * @param null   $image_id       An image ID to include in the stamp (optional, gotten from `retrievePublicGallery` or `retrievePrivateGallery`)
	 *
	 * @return string A link to the preview stamp in PDF format
	 */
	public function retrievePreviewVoucherPdf( int $product_code, string $voucher_layout, int $page_format_id, $image_id = null ) : string {

		if ( null === $this->api ) {
			$this->connect();
		}

		$result = $this->api->__soapCall( 'retrievePreviewVoucherPDF', array(
			'RetrievePreviewVoucherPDFRequest' => array(
				'productCode'   => $product_code,
				'imageID'       => $image_id,
				'voucherLayout' => $voucher_layout,
				'pageFormatId'  => $page_format_id
			)
		));

		return $result->link;
	}

	/**
	 * Get a link to a preview of a stamp in PNG format
	 *
	 * @access public
	 *
	 * @param int    $product_code   A product code for the type of stamp (a list of products is only available via the separate ProdWS service)
	 * @param string $voucher_layout string The layout of the stamp (possible values: 'FrankingZone' and 'AddressZone')
	 * @param null   $image_id       An image ID to include in the stamp (optional, gotten from `retrievePublicGallery` or `retrievePrivateGallery`)
	 *
	 * @return string A link to the preview stamp in PNG format
	 */
	public function retrievePreviewVoucherPng( int $product_code, string $voucher_layout, $image_id = null ) : string {

		if ( null === $this->api ) {
			$this->connect();
		}

		$result = $this->api->__soapCall( 'retrievePreviewVoucherPNG', array(
			'RetrievePreviewVoucherPNGRequest' => array(
				'productCode'   => $product_code,
				'imageID'       => $image_id,
				'voucherLayout' => $voucher_layout
			)
		));

		return $result->link;
	}

	/**
	 * Create a stamp in PDF format (costs actual money, debited from the Portokasse account)
	 *
	 * @access public
	 *
	 * @param string    $auth_token           Token to authenticate the user (gotten from `authenticateUser`)
	 * @param int       $page_format_id       ID of the page layout to be used (gotten from `retrieve_page_formats`)
	 * @param array     $positions            An array of items to be ordered
	 * @param int       $total                The total value of the shopping cart in eurocents (this is actually checked by the server and has to be correct)
	 * @param null      $shop_order_id
	 * @param null      $ppl_id               Parameter is no longer evaluated and will not be used in a future service version
	 * @param bool|null $create_manifest      Whether to create a posting receipt
	 * @param int|null  $create_shipping_list Type of shipping list to be created (0: No shipping list, 1: Shipping list without addresses, 2: Shipping list with addresses)
	 *
	 * @return stdClass An object containing:
	 *     - a link to the PDF version of the stamp
	 *     - a link to the shipping list (if requested)
	 *     - the user's wallet balance after the order
	 *     - the order ID
	 *     - the voucher ID
	 *     - the tracking ID (if applicable)
	 */
	public function checkoutShoppingCartPdf( string $auth_token, int $page_format_id, array $positions, int $total, $shop_order_id = null, $ppl_id = null, bool $create_manifest = null, int $create_shipping_list = null ) : stdClass {

		if ( null === $this->api ) {
			$this->connect();
		}

		$result = $this->api->__soapCall( 'checkoutShoppingCartPDF', array(
			'CheckoutShoppingCartPDFRequest' => array(
				'userToken'          => $auth_token,
				'shopOrderId'        => $shop_order_id,
				'pageFormatId'       => $page_format_id,
				'ppl'                => $ppl_id,
				'positions'          => $positions,
				'total'              => $total,
				'createManifest'     => $create_manifest,
				'createShippingList' => $create_shipping_list
			)
		));

		return $result;
	}

	/**
	 * Create a stamp in PNG format (costs actual money, debited from the Portokasse account)
	 *
	 * @access public
	 *
	 * @param string    $auth_token           Token to authenticate the user (gotten from `authenticateUser`)
	 * @param array     $positions            An array of items to be ordered
	 * @param int       $total                The total value of the shopping cart in eurocents (this is actually checked by the server and has to be correct)
	 * @param null      $shop_order_id
	 * @param null      $ppl_id               Parameter is no longer evaluated and will not be used in a future service version
	 * @param null|bool $create_manifest      Whether to create a posting receipt
	 * @param null|int  $create_shipping_list Type of shipping list to be created (0: No shipping list, 1: Shipping list without addresses, 2: Shipping list with addresses)
	 *
	 * @return stdClass An object containing:
	 *     - a link to the PNG version of the stamp
	 *     - a link to the shipping list (if requested)
	 *     - the user's wallet balance after the order
	 *     - the order ID
	 *     - the voucher ID
	 *     - the tracking ID (if applicable)
	 */
	public function checkoutShoppingCartPng( string $auth_token, array $positions, int $total, $shop_order_id = null, $ppl_id = null, $create_manifest = null, $create_shipping_list = null ) : stdClass {

		if ( null === $this->api ) {
			$this->connect();
		}

		return $this->api->__soapCall( 'checkoutShoppingCartPNG', array(
			'CheckoutShoppingCartPNGRequest' => array(
				'userToken'          => $auth_token,
				'shopOrderId'        => $shop_order_id,
				'ppl'                => $ppl_id,
				'positions'          => $positions,
				'total'              => $total,
				'createManifest'     => $create_manifest,
				'createShippingList' => $create_shipping_list
			)
		));
	}

	/**
	 * Fetch a previous order (from `checkoutShoppingCartPdf` or `checkoutShoppingCartPng`)
	 *
	 * @access public
	 *
	 * @param string $auth_token    Token to authenticate the user (gotten from `authenticateUser`)
	 * @param int    $shop_order_id The order ID of the order to be fetched
	 *
	 * @return stdClass Same as for the corresponding call to `checkoutShoppingCart(Pdf|Png)`
	 */
	public function retrieveOrder( string $auth_token, int $shop_order_id ) : stdClass {

		if ( null === $this->api ) {
			$this->connect();
		}

		return $this->api->__soapCall( 'retrieveOrder', array(
			'RetrieveOrderRequest' => array(
				'userToken'   => $auth_token,
				'shopOrderId' => $shop_order_id
			)
		));
	}

	/**
	 * Formatting price.
	 *
	 * @access public
	 *
	 * @param float  $amount
	 * @param string $currencySymbol
	 *
	 * @return string
	 */
	public function format_price( float $amount, string $currencySymbol = '€' ) : string {

		if ( function_exists( 'wc_price' ) ) {
			$price = wc_price( $amount, array( 'currency' => 'EUR' ) );
		} else {
			$price = number_format( (float) $amount, 2, ',', '') . ' ' . $currencySymbol;
		}

		return $price;
	}

	/**
	 * Formatting weight.
	 *
	 * @access public
	 *
	 * @param float $amount
	 *
	 * @return string
	 */
	public function format_weight( float $amount ) : string {

		if ( function_exists('wc_format_weight' ) ) {
			$weight = wc_format_weight( $amount );
		} else {
			$weight = sprintf('%s%s', $amount, 'g' );
		}

		return $weight;
	}

	/**
	 * Converting euro value to cents because Deutsche Post using values in cents.
	 *
	 * @access public
	 *
	 * @param float $amount
	 *
	 * @return float
	 */
	public function euros_to_cents( float $amount ) : float {

		return round( $amount * 100 );
	}

	/**
	 * Converting weight.
	 *
	 * @access public
	 *
	 * @param string $from
	 * @param string $to
	 * @param float  $weight
	 * @param int    $quantity
	 * @param int    $precision
	 *
	 * @return float
	 */
	public function convert_weight( string $from, string $to, float $weight, int $quantity = 1, int $precision = 2 ) : float {

		switch ( $from ) {
			case 'kg':
				switch ( $to ) {
					case 'kg':
						$factor = 1; // 1 kg = 1 kg
						break;
					case 'lbs':
						$factor = 2.204623; // 1 kg = 2.204623 lbs
						break;
					case 'oz':
						$factor = 35.27396; // 1 kg = 35.27396 oz
						break;
					case 'g':
						$factor = 1000; // 1 kg = 1000 g
						break;
					default:
						$factor = 1;
						break;
				}
				break;
			case 'lbs':
				switch ( $to ) {
					case 'kg':
						$factor = 0.4535924; // 1 lbs = 0.4535924 kg
						break;
					case 'lbs':
						$factor = 1; // 1 lbs = 1 lbs
						break;
					case 'oz':
						$factor = 16; // 1 lbs = 16 oz
						break;
					case 'g':
						$factor = 453.5924; // 1 lbs = 453.5924 g
						break;
					default:
						$factor = 1;
						break;
				}
				break;
			case 'oz':
				switch ( $to ) {
					case 'kg':
						$factor = 0.02834952; // 0.02834952 oz = 1 kg
						break;
					case 'lbs':
						$factor = 0.0625; // 1 oz = 0.0625 lbs
						break;
					case 'oz':
						$factor = 1; // 1 oz = 1 oz
						break;
					case 'g':
						$factor = 28.34952; // 1 oz = 28.34952 g
						break;
					default:
						$factor = 1;
						break;
				}
				break;
			case 'g':
				switch ( $to ) {
					case 'kg':
						$factor = 0.001; // 0.001 g = 1 kg
						break;
					case 'lbs':
						$factor = 0.002204623; // 1 g = 0.002204623 lbs
						break;
					case 'oz':
						$factor = 0.03527396; // 1 g = 0.03527396 oz
						break;
					case 'g':
						$factor = 1; // 1 g = 1 g
						break;
					default:
						$factor = 1;
						break;
				}
				break;
			default:
				$factor = 1;
				break;
		}

		// Calculation
		$weight = ( floatval( $weight ) * $factor ) * intval( $quantity );

		return round( $weight, $precision );
	}

	/**
	 * Returns EU countries.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_eu_countries() : array {

		return array(
			'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK',
			'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE',
			'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL',
			'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
		);
	}

	/**
	 * @var bool|string $shippingRegion ShippingRegion Enum value
	 *
	 * @return array
	 */
	function get_harmonized_labels( $shippingRegion = false ) : array {

		$euLabels = array(
			13143, // Warenpost International XS (EU/USt.)
			13144, // Warenpost International S (EU/USt.)
			13145, // Warenpost International M (EU/USt.)
			13146, // Warenpost International L (EU/USt.)
			13147, // Warenpost International XS Tracked (EU/USt.)
			13148, // Warenpost International S Tracked (EU/USt.)
			13149, // Warenpost International M Tracked (EU/USt.)
			13150, // Warenpost International L Tracked (EU/USt.)
			13159, // Warenpost Int. KT (EU/USt.) für Internetmarke
			13160, // Warenpost Int. KT Tracked (EU/USt.) für Internetmarke
			13167, // Warenpost International XS Unterschrift (EU/USt.)
			13168, // Warenpost International S Unterschrift (EU/USt.)
			13169, // Warenpost International M Unterschrift (EU/USt.)
			13170, // Warenpost International L Unterschrift (EU/USt.)
			13175, // Warenpost Int. KT Unterschrift (EU/USt.) für Internetmarke
		);

		$nonEuLabels = array(
			13135, // Warenpost International XS
			13136, // Warenpost International S
			13137, // Warenpost International M
			13138, // Warenpost International L
			13139, // Warenpost International XS Tracked
			13140, // Warenpost International S Tracked
			13141, // Warenpost International M Tracked
			13142, // Warenpost International L Tracked
			13163, // Warenpost International XS Unterschrift
			13164, // Warenpost International S Unterschrift
			13165, // Warenpost International M Unterschrift
			13166, // Warenpost International L Unterschrift
			14110, // Warenpost Int. KT (Non EU) für Internetmarke
			14111, // Warenpost Int. KT Tracked (Non EU) für Internetmarke
			14112, // Warenpost Int. KT Unterschrift (Non EU) für Internetmarke
		);

		if ( false === $shippingRegion ) {
			return array_merge( $euLabels, $nonEuLabels );
		}

		switch ( $shippingRegion ) {
			case self::EUROPEAN_UNION:
				return $euLabels;
			case self::REST_OF_WORLD:
				return $nonEuLabels;
			default:
				return array();
		}
	}

	/**
	 * @access public
	 *
	 * @param int $shipmentId
	 *
	 * @return string
	 */
	public function format_shipment_number( int $shipmentId ) : string {

		return sprintf('#%s', str_pad( $shipmentId, 8,'0', STR_PAD_LEFT ) );
	}

	/**
	 * Convert to voucher number.
	 *
	 * @access public
	 *
	 * @param string $voucherId
	 *
	 * @return int
	 */
	public function convert_to_voucher_no( string $voucherId ) : int {

		return hexdec( substr( $voucherId, 10, 9 ) );
	}

	/**
	 * @access public
	 *
	 * @param string $region
	 *
	 * @return string|void
	 */
	public function get_shipping_region_name( string $region ) {

		$region = strtoupper( $region );
		$area = null;

		switch ( $region ) {
			case 'DE':
				$area = WC()->countries->countries[ 'DE' ];
				break;
			case 'EU':
				$area = __('European union', 'woocommerce-german-market');
				break;
			case 'ROW':
				$area = __('Rest of world', 'woocommerce-german-market');
				break;
		}

		return $area;
	}

	/**
	 * Converts dashes to camel case with first capital letter.
	 *
	 * @param string $input
	 * @param string $separator
	 *
	 * @return string|string[]
	 */
	public function camelize( string $input, string $separator = '_' ) {

		return str_replace( $separator, '', ucwords( $input, $separator ) );
	}

	/**
	 * Getting tracking link.
	 *
	 * @param string $trackingNumber
	 * @param string $dateTime
	 *
	 * @return string
	 */
	public function get_tracking_link( string $trackingNumber, string $dateTime ) : string {

		try {
			$date = new DateTime( $dateTime );
		} catch ( Exception $e ) {
			return '';
		}

		return sprintf(
			self::TRACKING_LINK,
			$trackingNumber,
			$date->format( 'd' ),
			$date->format( 'm' ),
			$date->format( 'Y' ),
			$trackingNumber
		);
	}

	/**
	 * adds a small download button to the admin page for orders
	 *
	 * @hook woocommerce_admin_order_actions
	 *
	 * @param array    $actions
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function backend_icon_download( array $actions, WC_Order $order ) : array {

		if ( apply_filters( 'wgm_' . $this->id . '_shipping_backend_admin_icon_download_return', false, $order ) ) {
			return $actions;
		}

		if ( ! Helper::check_order_for_shipping_provider_methods( $order, $this->id ) ) {
			return $actions;
		}

		$has_internetmarke = $order->get_meta( '_wgm_internetmarke_binary_label_data' );

		if ( ! empty( $has_internetmarke ) ) {
			$create_pdf = array(
				'url'    => $this->create_label_download_link( $order->get_id() ),
				'name'   => __( 'Download Internetmarke', 'woocommerce-german-market' ),
				'action' => 'woocommerce_dhl_shipping_internetmarke_label_download',
			);
			$actions[ $this->id . '_internetmarke_label' ] = $create_pdf;
		}

		return $actions;
	}

}
