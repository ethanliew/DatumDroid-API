<?php
/**
 * @package DatumDroid_API
 * @subpackage Request
 */

/**
 * This script takes the search keywords and returns appropriate results in JSON
 * format.
 *
 * @todo Refine search query
 * @todo Refine results
 */

/**
 * To add a new service:
 *
 *  1. Add service.[name].php in DD_DIR_INC like service.twitter.php
 *  2. Add [name] => [class_name] in $dd_services
 *  3. Done
 */

// Include custom configuration
if ( file_exists( 'dd-config.php' ) )
	require_once( 'dd-config.php' );

/** Debug *********************************************************************/

// Debug
if ( ! defined( 'DD_DEBUG' ) )
	define( 'DD_DEBUG', isset( $_REQUEST['debug'] ) ? true : false );

// Report all errors if debugging, else none
error_reporting( E_ALL );

ini_set( 'display_errors', true == DD_DEBUG ? 1 : 0 );

/** Definitions ***************************************************************/

/* Directories */

// Root Path
if ( ! defined( 'DD_DIR'     ) )
	define( 'DD_DIR',          dirname( __FILE__ )        . '/' );

// Includes Directory
if ( ! defined( 'DD_DIR_INC' ) )
	define( 'DD_DIR_INC',      DD_DIR     . 'dd-includes' . '/' );

/* Misc */

// Version
if ( ! defined( 'DD_VER'        ) )
	define( 'DD_VER',        '1.0'                                             );

// Name
if ( ! defined( 'DD_NAME'       ) )
	define( 'DD_NAME',       'DatumDroid'                                      );

// User Agent
if ( !defined( 'DD_USER_AGENT' ) )
	define( 'DD_USER_AGENT', DD_NAME . 'API/' . DD_VER . ':api@datumdroid.com' );

/* URIs */

// Url Path to this API
if ( ! defined( 'DD_URI' ) )
	define( 'DD_URI', 'http://api.datumdroid.com/' . DD_VER . '/' );

/** Actions *******************************************************************/

// Set the default timezone to India/Delhi (+5.5)
date_default_timezone_set( 'Asia/Calcutta' );

/** Variables *****************************************************************/

// Query
$dd_query = !empty( $_REQUEST['q'] ) ? trim( $_REQUEST['q'] ) : '';

// Set max results
$dd_per_page = !empty( $_REQUEST['per_page'] ) ? intval( $_REQUEST['per_page'] ) : 10;
$dd_per_page = ( $dd_per_page < 1 || $dd_per_page > 50 ) ? 10 : $dd_per_page;

// Page number
$dd_page = !empty( $_REQUEST['page'] ) ? intval( $_REQUEST['page'] ) : 1;
$dd_page = $dd_page < 1 ? 1 : $dd_page;

// Language
$dd_lang = !empty( $_REQUEST['lang'] ) ? $_REQUEST['lang'] : 'en';

// Supported services
if ( ! isset( $dd_services ) )
	$dd_services = array(
		'feedzilla' => 'Feedzilla',
		'gimages'   => 'Google_Images',
		'gstocks'   => 'Google_Stocks',
		'gweather'  => 'Google_Weather',
		'guardian'  => 'Guardian',
		'twitter'   => 'Twitter',
		'youtube'   => 'YouTube'
	);

// API Keys
if ( ! isset( $dd_api_keys ) )
	$dd_api_keys = array();

// Results array, which would be outputted as json encoded later
$dd_results = array();

/** Include required files ****************************************************/

// Core functions
require_once( DD_DIR_INC . 'functions.core.php' );

// Service wrapper class
require_once( DD_DIR_INC . 'class.service.php'  );

/** Search as required & print ************************************************/

// Check for services
if ( empty( $dd_services ) )
	dd_output( array( 'responseDetails' => "no services found", 'responseStatus' => 400 ) );

// Are we just returning supported services?
if ( !empty( $_REQUEST['supported_services'] ) )
	dd_output( $dd_services );

// Check for search keywords
if ( ! dd_get_query() )
	dd_output( array( 'responseDetails' => "missing query parameter 'q'", 'responseStatus' => 400 ) );

// Get the services in the $dd_services array and check if they are required
// If yes, add their results to the results array
foreach ( (array) $dd_services as $service => $service_name ) {
	if ( ( !empty( $_REQUEST['all'] ) && $_REQUEST['all'] == 1 ) || isset( $_REQUEST[$service] ) ) {
		// Require the search results fetcher file
		dd_load_service( $service );

		$service_class = 'DD_Service_' . $service_name;

		$search = new $service_class( ( empty( $_REQUEST[$service] ) || ( $_REQUEST[$service] == 1 && isset( $_REQUEST['per_page'] ) ) ) ? array() : array( 'per_page' => $_REQUEST[$service] ) );

		$dd_results[$service] = $search->search();
	}
}

dd_output( $dd_results );

// Gaut.am was here

?>
