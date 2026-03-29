<?php
/**
 * GEO Optimizer – Geografische SEO-Signale, hreflang, lokale Schema-Daten
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Flavor_GEO_Optimizer {

    public function __construct() {
        if ( ! get_option( 'flavor_seo_setup_complete' ) ) {
            return;
        }
        add_action( 'wp_head', [ $this, 'render_geo_meta' ], 4 );
        add_action( 'wp_head', [ $this, 'render_hreflang' ], 4 );
        add_action( 'wp_head', [ $this, 'render_local_business_schema' ], 4 );
    }

    /**
     * Initial setup
     */
    public function setup( array $config ): array {
        $geo_data = $this->extract_geo_data( $config );
        update_option( 'flavor_geo_settings', $geo_data );
        return [ 'status' => 'ok', 'message' => 'GEO-Optimierung konfiguriert' ];
    }

    /**
     * Render geographic meta tags
     */
    public function render_geo_meta(): void {
        $config = get_option( 'flavor_seo_config', [] );
        $geo    = get_option( 'flavor_geo_settings', [] );

        $country_codes = [
            'Deutschland'  => 'DE',
            'Österreich'   => 'AT',
            'Schweiz'      => 'CH',
            'Germany'      => 'DE',
            'Austria'      => 'AT',
            'Switzerland'  => 'CH',
        ];

        $country      = $config['owner_country'] ?? 'Deutschland';
        $country_code = $country_codes[ $country ] ?? 'DE';
        ?>
        <meta name="geo.region" content="<?php echo esc_attr( $country_code ); ?>">
        <?php if ( ! empty( $geo['city'] ) ) : ?>
            <meta name="geo.placename" content="<?php echo esc_attr( $geo['city'] ); ?>">
        <?php endif; ?>
        <meta name="content-language" content="<?php echo esc_attr( $this->get_content_language( $config ) ); ?>">
        <?php
    }

    /**
     * Render hreflang tags
     */
    public function render_hreflang(): void {
        $config = get_option( 'flavor_seo_config', [] );
        $lang   = $this->get_hreflang_code( $config['blog_language'] ?? 'de_DE' );
        $url    = is_singular() ? get_permalink() : home_url( '/' );
        ?>
        <link rel="alternate" hreflang="<?php echo esc_attr( $lang ); ?>" href="<?php echo esc_url( $url ); ?>">
        <link rel="alternate" hreflang="x-default" href="<?php echo esc_url( $url ); ?>">
        <?php
    }

    /**
     * Render LocalBusiness schema if address is provided
     */
    public function render_local_business_schema(): void {
        $config = get_option( 'flavor_seo_config', [] );

        if ( empty( $config['owner_street'] ) && empty( $config['owner_zip_city'] ) ) {
            return;
        }

        $geo = get_option( 'flavor_geo_settings', [] );

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'LocalBusiness',
            'name'     => $config['owner_name'] ?? $config['blog_name'] ?? '',
            'url'      => home_url( '/' ),
        ];

        if ( ! empty( $config['owner_email'] ) ) {
            $schema['email'] = $config['owner_email'];
        }

        if ( ! empty( $config['owner_phone'] ) ) {
            $schema['telephone'] = $config['owner_phone'];
        }

        $address = [
            '@type' => 'PostalAddress',
        ];

        if ( ! empty( $config['owner_street'] ) ) {
            $address['streetAddress'] = $config['owner_street'];
        }

        if ( ! empty( $geo['zip'] ) ) {
            $address['postalCode'] = $geo['zip'];
        }

        if ( ! empty( $geo['city'] ) ) {
            $address['addressLocality'] = $geo['city'];
        }

        if ( ! empty( $config['owner_country'] ) ) {
            $address['addressCountry'] = $config['owner_country'];
        }

        $schema['address'] = $address;

        if ( is_front_page() ) {
            echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
        }
    }

    /**
     * Extract city and zip from zip_city field
     */
    private function extract_geo_data( array $config ): array {
        $zip_city = $config['owner_zip_city'] ?? '';
        $zip      = '';
        $city     = '';

        if ( preg_match( '/^(\d{4,5})\s+(.+)$/', $zip_city, $m ) ) {
            $zip  = $m[1];
            $city = $m[2];
        } else {
            $city = $zip_city;
        }

        return [
            'zip'     => $zip,
            'city'    => $city,
            'country' => $config['owner_country'] ?? 'Deutschland',
        ];
    }

    /**
     * Convert WP locale to hreflang code
     */
    private function get_hreflang_code( string $locale ): string {
        $map = [
            'de_DE' => 'de-DE',
            'de_AT' => 'de-AT',
            'de_CH' => 'de-CH',
            'en_US' => 'en-US',
            'en_GB' => 'en-GB',
        ];
        return $map[ $locale ] ?? 'de-DE';
    }

    /**
     * Get content-language value
     */
    private function get_content_language( array $config ): string {
        $locale = $config['blog_language'] ?? 'de_DE';
        return str_replace( '_', '-', strtolower( $locale ) );
    }
}
