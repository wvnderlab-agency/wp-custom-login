<?php

/*
 * Plugin Name:     wvnderlab - Custom Login Url
 * Plugin URI:      https://github.com/wvnderlab-agency/wp-custom-login-url/
 * Description:     A simple WordPress plugin to customize the login URL for your WordPress site. This plugin allows you to change the default login URL from /wp-login.php to a custom URL of your choice, enhancing security and providing a more personalized experience for your users.
 * Author:          Wvnderlab Agency
 * Author URI:      https://wvnderlab.com
 * Text Domain:     wvnderlab-custom-login-url
 * Version:         0.1.0
 */

/*
 *  ################
 *  ##            ##    Copyright (c) 2026 Wvnderlab Agency
 *  ##
 *  ##   ##  ###  ##    ✉️ moin@wvnderlab.com
 *  ##    #### ####     🔗 https://wvnderlab.com
 *  #####  ##  ###
 */

declare( strict_types=1 );

namespace WvnderlabAgency\CustomLoginUrl;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the custom login slug.
 *
 * @return string
 */
function custom_login_slug(): string {
	$slug = defined( 'WVNDERLAB_CUSTOM_LOGIN_URL_SLUG' )
		? constant( 'WVNDERLAB_CUSTOM_LOGIN_URL_SLUG' )
		: 'dierck-login';

	/**
	 * Filter: The custom login slug.
	 *
	 * @param string $slug The custom login slug.
	 * @return string The filtered custom login slug.
	 */
	$slug = (string) apply_filters(
		'wvnderlab/custom-login-url/slug',
		$slug
	);

	return trim( $slug, '/' );
}

/**
 * Return the base path of the WordPress installation, relative to the site root.
 *
 * @return string
 */
function custom_admin_base_path(): string {
	$home_url  = home_url( '/' );
	$home_path = wp_parse_url( $home_url, PHP_URL_PATH );

	if ( ! is_string( $home_path ) || '/' === $home_path ) {
		return '';
	}

	return '/' . trim( $home_path, '/' );
}

/**
 * Return the current request path, relative to the WordPress base path.
 *
 * @return string
 */
function custom_admin_relative_path(): string {
	$path = custom_admin_request_path();
	$base = custom_admin_base_path();

	if (
		'' !== $base
		&& ( $base === $path || str_starts_with( $path, $base . '/' ) )
	) {
		$path = substr( $path, strlen( $base ) );
	}

	return '/' . trim( $path, '/' );
}

/**
 * Return the current request path, relative to the site root.
 *
 * @return string
 */
function custom_admin_request_path(): string {
	$request_uri = isset( $_SERVER['REQUEST_URI'] )
		? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) )
		: '/';

	$path = wp_parse_url( $request_uri, PHP_URL_PATH );

	if ( ! is_string( $path ) || '' === $path ) {
		return '/';
	}

	return '/' . trim( $path, '/' );
}

/**
 * Send a WordPress 404 response.
 *
 * @return void
 */
function custom_admin_send_404(): void {
	status_header( 404 );
	nocache_headers();

	global $wp_query;

	if ( $wp_query instanceof WP_Query ) {
		$wp_query->set_404();
	}

	$template = get_404_template();

	if ( $template && file_exists( $template ) ) {
		include $template;
	} else {
		echo '<!doctype html>';
		echo '<html lang="de">';
		echo '<head><meta charset="utf-8"><title>404 Not Found</title></head>';
		echo '<body><h1>404 Not Found</h1></body>';
		echo '</html>';
	}

	exit;
}

/**
 * Replace the default WordPress login URL with the custom login URL.
 *
 * @param string $url The URL to filter.
 *
 * @return string
 */
function replace_default_login_url( string $url ): string {
	$path = wp_parse_url( $url, PHP_URL_PATH );

	if ( ! is_string( $path ) || 'wp-login.php' !== wp_basename( $path ) ) {
		return $url;
	}

	$query    = wp_parse_url( $url, PHP_URL_QUERY );
	$fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );
	$scheme   = wp_parse_url( $url, PHP_URL_SCHEME );
	$url      = home_url( '/' . custom_login_slug() . '/' );

	if ( is_string( $scheme ) && '' !== $scheme ) {
		$url = set_url_scheme( $url, $scheme );
	}

	if ( is_string( $query ) && '' !== $query ) {
		$url .= '?' . $query;
	}

	if ( is_string( $fragment ) && '' !== $fragment ) {
		$url .= '#' . $fragment;
	}

	return $url;
}

/**
 * Filter WordPress URLs generated via site_url().
 *
 * @hooked filter site_url
 *
 * @param string $url The complete site URL.
 *
 * @return string
 */
function filter_site_url( string $url ): string {

	return replace_default_login_url( $url );
}

add_filter( 'site_url', __NAMESPACE__ . '\\filter_site_url', PHP_INT_MAX );

/**
 * Filter WordPress URLs generated via network_site_url().
 *
 * @hooked filter network_site_url
 *
 * @param string $url The complete network site URL.
 *
 * @return string
 */
function filter_network_site_url( string $url ): string {

	return replace_default_login_url( $url );
}

add_filter( 'network_site_url', __NAMESPACE__ . '\\filter_network_site_url', PHP_INT_MAX );

/**
 * Filter redirects that still reference the default WordPress login path.
 *
 * @hooked filter wp_redirect
 *
 * @param string $location The redirect location.
 *
 * @return string
 */
function filter_login_redirect( string $location ): string {
	$location_host = wp_parse_url( $location, PHP_URL_HOST );
	$home_host     = wp_parse_url( home_url( '/' ), PHP_URL_HOST );

	if ( is_string( $location_host ) && is_string( $home_host ) && 0 !== strcasecmp( $location_host, $home_host ) ) {
		return $location;
	}

	return replace_default_login_url( $location );
}

add_filter( 'wp_redirect', __NAMESPACE__ . '\\filter_login_redirect', PHP_INT_MAX );

/**
 * Handle the custom login URL and hide the default WordPress login page.
 *
 * @hooked action init
 *
 * @return void
 */
function hide_default_login_page(): void {
	$slug = custom_login_slug();
	$path = custom_admin_relative_path();

	// Load the WordPress login page for the custom login slug.
	if ( '/' . $slug === $path ) {
		// wp-login.php can reference these before they are conditionally assigned.
		global $user_login, $error;

        //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$user_login = '';
        //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$error = '';

		require_once ABSPATH . 'wp-login.php';

		exit;
	}

	// Hide the default WordPress login page for all requests.
	if ( '/wp-login.php' === $path ) {
		custom_admin_send_404();
	}

	// Logged-in users can access the admin area normally.
	if ( is_user_logged_in() ) {
		return;
	}

	// Allow public WordPress endpoints used by frontend requests.
	if ( '/wp-admin/admin-ajax.php' === $path || '/wp-admin/admin-post.php' === $path ) {
		return;
	}

	// Hide the WordPress admin area for logged-out users.
	if ( '/wp-admin' === $path || str_starts_with( $path, '/wp-admin/' ) ) {
		custom_admin_send_404();
	}
}

add_action( 'init', __NAMESPACE__ . '\\hide_default_login_page', PHP_INT_MIN );

/**
 * Get the URL of the custom logo if it exists.
 *
 * @return string|null
 */
function get_custom_logo_url(): ?string {
	$theme_support = get_theme_support( 'custom-logo' );

	if ( $theme_support ) {
		$custom_logo_id  = get_theme_mod( 'custom_logo' ) ?? null;
		$custom_logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );

		return $custom_logo_url ?? null;
	}

	return null;
}

/**
 * Customize the login logo on the WordPress login page.
 *
 * @hooked action login_head
 * @return void
 */
function custom_login_logo(): void {
	$custom_logo_url = get_custom_logo_url();
	$site_icon_url   = get_site_icon_url();

	/**
	 * Filter: The custom login logo background image. If no custom logo is set, the default background image is the site icon.
	 *
	 * @param string $image_url The URL of the login logo background image.
	 * @return string
	 */
	$background_image = (string) apply_filters(
		'wvnderlab/custom-login-url/login-logo-background-image',
		$custom_logo_url ?? $site_icon_url
	);

	/**
	 * Filter: The custom login logo border radius. If no custom logo is set, the default border radius is 8px.
	 *
	 * @param string $border_radius The border radius of the login logo.
	 * @return string
	 */
	$border_radius = (string) apply_filters(
		'wvnderlab/custom-login-url/login-logo-border-radius',
		$custom_logo_url ? '0' : '8px'
	);

	/**
	 * Filter: The custom login logo width. If no custom logo is set, the default width is 80px.
	 *
	 * @param string $width The width of the login logo.
	 * @return string
	 */
	$width = (string) apply_filters(
		'wvnderlab/custom-login-url/login-logo-width',
		$custom_logo_url ? 'auto' : '80px'
	);

	?>
	<style type="text/css">
		#login h1 a {
			width: <?php echo esc_attr( $width ); ?>;
			height: 80px;
			margin: 0 auto;
			border-radius: <?php echo esc_attr( $border_radius ); ?>;
			background-image: url('<?php echo esc_attr( $background_image ); ?>');
			background-size: contain;
		}
	</style>
	<?php
}

add_action( 'login_head', __NAMESPACE__ . '\\custom_login_logo' );