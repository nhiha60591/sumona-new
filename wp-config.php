<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'sumona-new');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '?{gHXqd2U4?]g(S;l}wz36x$Z8hi9#j,1>k_uF/~k(5P`V5n_=hbc!k38jbtWEQ=');
define('SECURE_AUTH_KEY',  '.xlmH$n@LXq`Vb[XZ(/&mw%570nSnvOeQ}o3QzqATv1?mWt]to/H{UKf;~,v>]FG');
define('LOGGED_IN_KEY',    '+UAHFC6RbcL+rb`x?G~:*=NF5/^t~qK!3e{I$3#S1!,3Oz9|BC6=uHw7WFfHJUzE');
define('NONCE_KEY',        '[Jk&#nv/$`>29`,$UoZau6$aLdmr9uq-sZO!_[julVI`g}4KGr[&V!Iv>G-9)zQr');
define('AUTH_SALT',        'Y2WD;,[^{WT#3zwPfuj.^{@[9a#jkc,[yy|M!#4Hj|FrsdR@V/AKr?S&*WeFISpv');
define('SECURE_AUTH_SALT', '>p&#GY:F4qCQqN;]c%d$OdyefefR;;S>$%Gs*an}<.o_jmR~ EmQ$i~2JvsC yPb');
define('LOGGED_IN_SALT',   'b}+Ne%IoVGQ}]g&k.`GFSTy1@&n*PsDU5<%pW{}>eOtwh.&N?vQ2#_y6W#p2SYAE');
define('NONCE_SALT',       '5k.M}/DrO iVrFmgG/}|-iu|;9E5vFY[Vx_Z;LMVUp`k?[6V3qO-cz?~{CfE8)gf');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
