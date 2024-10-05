<?php
/**
 * Plugin Name:       Wow Test Poke Block
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Version:           0.1.0
 * Author:            Sergei Konovalov
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wow-test
 *
 * @package Wow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function wow_wow_test_block_init() {
	register_block_type( __DIR__ . '/build' );
}

add_action( 'init', 'wow_wow_test_block_init' );

// Регистрирует маршрут
add_action( 'rest_api_init', function () {

	register_rest_route( 'wow/v1', '/type', array(
		'methods'             => 'GET',
		'callback'            => 'get_pokes',
		'permission_callback' => '__return_true'
	) );
} );

// Обрабатывает запрос и получает все типы покемонов
function get_pokes( WP_REST_Request $request ) {

	$url              = 'https://pokeapi.co/api/v2/type/';
	$response         = wp_remote_get( $url );
	$response_code    = wp_remote_retrieve_response_code( $response );
	$response_message = wp_remote_retrieve_response_message( $response );
	$response_body    = json_decode( wp_remote_retrieve_body( $response ) );

	if ( 200 != $response_code && ! empty( $response_message ) ) {
		return new WP_Error( $response_code, $response_message );
	} elseif ( 200 != $response_code ) {
		return new WP_Error( $response_code, 'Unknown Error' );
	} elseif ( ! $response_body ) {
		return new WP_Error( 'nodata', 'No poke data' );
	} else {
		return $response_body;
	}
}

// Получает html для вывода на в фронте
function get_poke_info( WP_REST_Request $request ) {
	$front_default = 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/';
	$url           = $request->get_param( 'url' );
	$result        = '';
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		return new WP_Error( "Error. Not valid url" );
	}
	$response         = wp_remote_get( $url );
	$response_code    = wp_remote_retrieve_response_code( $response );
	$response_message = wp_remote_retrieve_response_message( $response );
	$response_body    = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( $response_code, $response_message );
	}

	if ( $response_body->pokemon ) {
		foreach ( $response_body->pokemon as $pokemon_data ) {
			$name    = $pokemon_data->pokemon->name;
			$url     = $pokemon_data->pokemon->url;
			$url_arr = explode( '/', rtrim( $url, '/' ) );
			$img_id  = count( $url_arr ) > 0 ? end( $url_arr ) : 0;

			$result .= '<div class="poke"><h3>' . $name . '</h3><img src="' . $front_default . $img_id . '.png" alt=""/></div>';
		}
	}

	return $result;
}

//добавление маршрута
add_action( 'rest_api_init', function () {

	register_rest_route( 'wow/v1', '/poke_info', array(
		'methods'             => 'POST',
		'callback'            => 'get_poke_info',
		'permission_callback' => '__return_true',
		'args'                => [
			'url' => array(
				'type'     => 'string', // значение параметра должно быть строкой
				'required' => true,     // параметр обязательный
			),
		]
	) );
} );

