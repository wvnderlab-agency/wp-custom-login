# wp-custom-login-url

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php)
![WordPress](https://img.shields.io/badge/WordPress-MU--Plugin-21759B?logo=wordpress)
[![WP Coding Standards](https://github.com/wvnderlab-agency/wp-custom-login-url/actions/workflows/wp-coding-standards.yml/badge.svg)](https://github.com/wvnderlab-agency/wp-custom-login-url/actions/workflows/wp-coding-standards.yml)

- [Installation](#installation)
- [Usage](#usage)
- [Development](#development)

## Installation

### Via Composer

```shell
composer require wvnderlab-agency/wp-custom-login-url
```

### Via FTP

1. Download the repository zip file.
2. Unzip the file.
3. Upload the unzipped folder to the `/wp-content/muplugins` or `/wp-content/plugins/` directory on your server.
4. Navigate to the 'Plugins' section in your WordPress admin dashboard.
5. Find 'Disable posts' in the list and click 'Activate'.

### Via WordPress Admin Dashboard

1. Download the repository zip file.
2. Navigate to the 'Plugins' section in your WordPress admin dashboard.
3. Click 'Add New' and then 'Upload Plugin'.
4. Choose the downloaded zip file and click 'Install Now'.
5. After installation, click 'Activate Plugin'.

## Usage

### Config

### WVNDERLAB_CUSTOM_LOGIN_URL_SLUG *(Default: null)*

This constant allows you to set a custom login URL slug. If not defined, the default slug 'admin-login' will be used.

```php
// define the custom login URL slug
define( 'WVNDERLAB_CUSTOM_LOGIN_URL_SLUG', 'custom-login' );
```

### Filter Hooks

#### wvnderlab/custom-login-url/slug *(Default: 'admin-login')*

This filter allows you to change the slug of the custom login URL.

```php
// change the slug to 'custom-login'
add_filter( 'wvnderlab/custom-login-url/slug', fn( string $slug ) => 'custom-login' );
```


#### wvnderlab/custom-login-url/login-logo-background-image *(Default: 'custom-logo-url'|'site-icon-url')*

This filter allows you to change the background image of the login logo. By default, it uses the custom logo URL if available, otherwise it falls back to the site icon URL.

```php
// change the login logo background image to a custom URL
add_filter( 'wvnderlab/custom-login-url/login-logo-background-image', fn( string $background_image ) => 'https://example.com/path/to/custom-logo.png' );
```

#### wvnderlab/custom-login-url/login-logo-border-radius *(Default: '0'|'8px')*

This filter allows you to change the border radius of the login logo. By default, it is set to '0', but you can change it to any valid CSS border-radius value.

```php
// change the login logo border radius to '12px'
add_filter( 'wvnderlab/custom-login-url/login-logo-border-radius', fn( string $border_radius ) => '12px' );
``` 

#### wvnderlab/custom-login-url/login-logo-logo-width *(Default: 'auto'|'8px')*

This filter allows you to change the width of the login logo. By default, it is set to 'auto', but you can change it to any valid CSS width value.

```php
// change the login logo width to '150px'
add_filter( 'wvnderlab/custom-login-url/login-logo-logo-width', fn( string $width ) => '150px' );
```


## Development

### Install Dependencies

```shell
composer install
```

### Analyse Code-Quality with WP-Coding-Standards

```shell
composer analyze
```

### Refactor Code along WP-Coding-Standards

```shell
composer refactor
```
