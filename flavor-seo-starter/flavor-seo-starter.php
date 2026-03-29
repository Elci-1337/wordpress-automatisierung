<?php
/**
 * Plugin Name: Flavor SEO Starter
 * Plugin URI:  https://github.com/elci-1337/wordpress-automatisierung
 * Description: Verwandelt eine leere WordPress-Instanz in ein vollständig SEO-, GEO- und LLM-optimiertes Magazin/Blog mit E-E-A-T, Impressum und professionellem Design – mit nur einem Setup-Formular.
 * Version:     1.0.0
 * Author:      flavor
 * Author URI:  https://github.com/elci-1337
 * License:     GPL-2.0-or-later
 * Text Domain: flavor-seo-starter
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'FLAVOR_SEO_VERSION', '1.0.0' );
define( 'FLAVOR_SEO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FLAVOR_SEO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FLAVOR_SEO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Autoload includes
$includes = [
    'includes/class-flavor-setup-wizard.php',
    'includes/class-flavor-seo-engine.php',
    'includes/class-flavor-llm-visibility.php',
    'includes/class-flavor-geo-optimizer.php',
    'includes/class-flavor-eeat-generator.php',
    'includes/class-flavor-legal-pages.php',
    'includes/class-flavor-category-generator.php',
    'includes/class-flavor-design-engine.php',
];

foreach ( $includes as $file ) {
    $path = FLAVOR_SEO_PLUGIN_DIR . $file;
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}

/**
 * Main Plugin Class
 */
final class Flavor_SEO_Starter {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_assets' ] );
        add_filter( 'plugin_action_links_' . FLAVOR_SEO_PLUGIN_BASENAME, [ $this, 'add_settings_link' ] );

        // Initialize modules
        new Flavor_SEO_Engine();
        new Flavor_LLM_Visibility();
        new Flavor_GEO_Optimizer();
        new Flavor_Design_Engine();
    }

    public function load_textdomain(): void {
        load_plugin_textdomain( 'flavor-seo-starter', false, dirname( FLAVOR_SEO_PLUGIN_BASENAME ) . '/languages/' );
    }

    public function register_admin_menu(): void {
        add_menu_page(
            __( 'Flavor SEO Starter', 'flavor-seo-starter' ),
            __( 'Flavor SEO', 'flavor-seo-starter' ),
            'manage_options',
            'flavor-seo-starter',
            [ $this, 'render_admin_page' ],
            'dashicons-superhero-alt',
            3
        );
    }

    public function render_admin_page(): void {
        $wizard = new Flavor_Setup_Wizard();
        $wizard->render();
    }

    public function enqueue_admin_assets( string $hook ): void {
        if ( 'toplevel_page_flavor-seo-starter' !== $hook ) {
            return;
        }
        wp_enqueue_style(
            'flavor-seo-admin',
            FLAVOR_SEO_PLUGIN_URL . 'admin/css/admin.css',
            [],
            FLAVOR_SEO_VERSION
        );
        wp_enqueue_script(
            'flavor-seo-admin',
            FLAVOR_SEO_PLUGIN_URL . 'admin/js/admin.js',
            [ 'jquery' ],
            FLAVOR_SEO_VERSION,
            true
        );
        wp_localize_script( 'flavor-seo-admin', 'flavorSeo', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'flavor_seo_nonce' ),
            'i18n'    => [
                'processing' => __( 'Setup läuft...', 'flavor-seo-starter' ),
                'success'    => __( 'Setup abgeschlossen!', 'flavor-seo-starter' ),
                'error'      => __( 'Fehler beim Setup.', 'flavor-seo-starter' ),
            ],
        ] );
    }

    public function enqueue_public_assets(): void {
        if ( ! get_option( 'flavor_seo_setup_complete' ) ) {
            return;
        }
        wp_enqueue_style(
            'flavor-seo-public',
            FLAVOR_SEO_PLUGIN_URL . 'public/css/magazine.css',
            [],
            FLAVOR_SEO_VERSION
        );
    }

    public function add_settings_link( array $links ): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'admin.php?page=flavor-seo-starter' ),
            __( 'Setup', 'flavor-seo-starter' )
        );
        array_unshift( $links, $settings_link );
        return $links;
    }
}

// Boot
add_action( 'plugins_loaded', [ 'Flavor_SEO_Starter', 'instance' ] );

// Activation hook
register_activation_hook( __FILE__, function () {
    add_option( 'flavor_seo_setup_complete', false );
    add_option( 'flavor_seo_config', [] );
    flush_rewrite_rules();
} );

// Deactivation hook
register_deactivation_hook( __FILE__, function () {
    flush_rewrite_rules();
} );
