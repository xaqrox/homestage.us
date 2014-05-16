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

// ** Heroku Postgres settings - from Heroku Environment ** //
$db = parse_url($_ENV["DATABASE_URL"]);

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', trim($db["path"],"/"));

/** MySQL database username */
define('DB_USER', $db["user"]);

/** MySQL database password */
define('DB_PASSWORD', $db["pass"]);

/** MySQL hostname */
define('DB_HOST', $db["host"]);

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
define('AUTH_KEY',         '-~)lWF7D+IW=_{CyUG$P8ol`Ku: >[H$+]HZ$KM2!Yj(vr -<WCoD]EX+kli3=U`');
define('SECURE_AUTH_KEY',  'x?QkzIXH#?o6tKbm^|kLcf)$^lcmiTR D_y|Cl M=jrk42}|+FYHYAO<Ps5A~Mzb');
define('LOGGED_IN_KEY',    'cl K-fm3r3<E{s_VjJ+U?)NHSDXx*|p[%|<f{b<x@gP<2;dk3n|H-@yB8sQj!5A&');
define('NONCE_KEY',        'bwmb%-J6q{.G?DS3|8M, +Td{2TrGs^iu,g1#O=CX[+G`I9QjP48(R0z^+5Z$#O^');
define('AUTH_SALT',        'EWZ$hH`9XQ~B`gX9B7PtY2KZre21[d2.Pxidf:1oZ+1h3/TagJRKRa98E[Oc~s2F');
define('SECURE_AUTH_SALT', '},94QzNn<Jm%j*RWQl}WCN+[ac+DS~?N<EKJrrPzSK-&c?-PW)RftO67-R(l(9!^');
define('LOGGED_IN_SALT',   '~TM-6q;=G40qem(v7&A$6LUM(G:>Qx6& 1bHd{5eq%j5`+g@]Ajow?[`&j*$C]7I');
define('NONCE_SALT',       ')2W$6Z<qA~{t~R,Lz#bqZXZ{$wj]_25_-#[?!Q+Isndzyc:-S-yPymo8[m|3|Ut.');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
