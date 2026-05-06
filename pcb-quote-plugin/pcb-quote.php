<?php
/**
 * Plugin Name: PCB Quote Manager Pro
 * Description: Professional PCB quote request system with admin field control, email template editor, and product categories.
 * Version:     2.0.1
 * Author:      PCB Quote Manager
 * License:     GPL-2.0+
 * Text Domain: pcbq
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PCBQ_VERSION',    '2.0.1' );
define( 'PCBQ_DIR',        plugin_dir_path( __FILE__ ) );
define( 'PCBQ_URL',        plugin_dir_url(  __FILE__ ) );
define( 'PCBQ_TABLE',      'pcb_quotes' );

/* ── Autoload includes ─────────────────────────────────── */
foreach ( ['db','helpers','shortcode','ajax','admin-menu','admin-quotes','admin-settings','admin-email-editor','email'] as $f ) {
    require_once PCBQ_DIR . "includes/{$f}.php";
}

/* ── Activation ────────────────────────────────────────── */
register_activation_hook( __FILE__, 'pcbq_run_setup' );

function pcbq_run_setup() {
    pcbq_create_tables();
    pcbq_seed_defaults();
    $up  = wp_upload_dir();
    $dir = $up['basedir'] . '/pcb-gerber-files';
    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
        file_put_contents( $dir . '/.htaccess', "Options -Indexes\ndeny from all\n" );
        file_put_contents( $dir . '/index.php', '<?php // silence' );
    }
    update_option( 'pcbq_db_version', PCBQ_VERSION );
}

/* ── Auto-create tables if missing (e.g. after manual zip upload) ── */
add_action( 'plugins_loaded', function() {
    if ( get_option( 'pcbq_db_version' ) !== PCBQ_VERSION ) {
        pcbq_run_setup();
    }
} );

/* ── Assets: Frontend ──────────────────────────────────── */
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(  'pcbq-front', PCBQ_URL . 'assets/css/frontend.css', [], PCBQ_VERSION );
    wp_enqueue_script( 'pcbq-front', PCBQ_URL . 'assets/js/frontend.js',  ['jquery'], PCBQ_VERSION, true );
    wp_localize_script( 'pcbq-front', 'PCBQ', [
        'ajax' => admin_url('admin-ajax.php'),
        'nonce'=> wp_create_nonce('pcbq_submit'),
    ]);
});

/* ── Assets: Admin ─────────────────────────────────────── */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( strpos( $hook, 'pcbq' ) === false && strpos( $hook, 'pcb-quote' ) === false ) return;
    wp_enqueue_style(  'pcbq-admin', PCBQ_URL . 'assets/css/admin.css', [], PCBQ_VERSION );
    wp_enqueue_script( 'pcbq-admin', PCBQ_URL . 'assets/js/admin.js',  ['jquery','wp-util'], PCBQ_VERSION, true );
    wp_localize_script( 'pcbq-admin', 'PCBQ_Admin', [
        'ajax'  => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pcbq_admin'),
    ]);
    // Code mirror for email editor
    wp_enqueue_code_editor(['type' => 'text/html']);
    wp_enqueue_script('wp-theme-plugin-editor');
    wp_enqueue_style('wp-codemirror');
});
