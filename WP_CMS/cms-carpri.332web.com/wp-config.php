<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'chasercb750_wp2' );

/** Database username */
define( 'DB_USER', 'chasercb750_wp2' );

/** Database password */
define( 'DB_PASSWORD', '78195090Cb' );

/** Database hostname */
define( 'DB_HOST', 'mysql8004.xserver.jp' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '$K~olBDixA9Dp[mEoQy>1bt5U^G[F9=6LFt{_B7x%_?,F9uw.{[A]`W[$m$-HV}e' );
define( 'SECURE_AUTH_KEY',  '$VA4_@3q:GOD~CodtT8.EXsLDZa80@<;ON>Ty[RybrlybE-fPY7#k!8h(;3MK FG' );
define( 'LOGGED_IN_KEY',    'F+l*t/x3`74KajG%y9RiO~5Wp-Cun*(c?mpp&QnFk+ x5I)c(LLNgq.3#3uv]$}n' );
define( 'NONCE_KEY',        '|)o8#f|_1hubx~agq~?oq=]do&{!UO9,[GPxJ_Fm0Tox1CUGyNH7#e]jNDB:j@ve' );
define( 'AUTH_SALT',        '_%0YCwx8# xI;lG,ZrSz`i/AXSf;AlAYQ2693)[UB,R0GBVAv}[>4|*K|>1)v9LA' );
define( 'SECURE_AUTH_SALT', '}o0bR3,IBufiFa@q$_W)y9p3*#H}:pd?>K6viZHiYJoS3b/Q(%!{H<w!qk;REHyH' );
define( 'LOGGED_IN_SALT',   ';I,03_uiV@%Qr,<QB~,A>3(4~~Q7TlP`#&}[`Z7}fg!XyUR6Ej.y?-Rq;^Rc?.a[' );
define( 'NONCE_SALT',       'M*[{^hO@`_yB#)7<K+e1D-h1>}pB*c;JwKOSU}0`fQpVN6aT-~RSc<vC6fD)3hYS' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
