<?php
/**
 * Design Engine – Sauberes Magazin-Design via Custom CSS, Theme-Mods und Customizer-Einstellungen
 * Funktioniert mit jedem Theme, optimiert für Block-Themes (Twenty Twenty-Four etc.)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Flavor_Design_Engine {

    public function __construct() {
        if ( ! get_option( 'flavor_seo_setup_complete' ) ) {
            return;
        }
        add_action( 'wp_head', [ $this, 'render_custom_properties' ], 0 );
        add_action( 'wp_footer', [ Flavor_Legal_Pages::class, 'render_cookie_banner' ] );
        add_filter( 'body_class', [ $this, 'add_body_classes' ] );
        add_action( 'wp_head', [ $this, 'render_author_schema_on_archive' ], 5 );
    }

    /**
     * Initial design setup
     */
    public function setup( array $config ): array {
        $this->generate_magazine_css( $config );
        $this->configure_reading_settings();

        return [ 'status' => 'ok', 'message' => 'Design konfiguriert' ];
    }

    /**
     * Output CSS custom properties for theme colors
     */
    public function render_custom_properties(): void {
        $config  = get_option( 'flavor_seo_config', [] );
        $primary = $config['primary_color'] ?? '#1e3a5f';
        $accent  = $config['accent_color'] ?? '#c9a227';

        // Calculate derived colors
        $primary_light = $this->adjust_brightness( $primary, 40 );
        $primary_dark  = $this->adjust_brightness( $primary, -20 );
        $accent_light  = $this->adjust_brightness( $accent, 30 );
        ?>
        <style id="flavor-custom-properties">
            :root {
                --flavor-primary: <?php echo esc_attr( $primary ); ?>;
                --flavor-primary-light: <?php echo esc_attr( $primary_light ); ?>;
                --flavor-primary-dark: <?php echo esc_attr( $primary_dark ); ?>;
                --flavor-accent: <?php echo esc_attr( $accent ); ?>;
                --flavor-accent-light: <?php echo esc_attr( $accent_light ); ?>;
                --flavor-text: #1a1a2e;
                --flavor-text-light: #4a4a68;
                --flavor-bg: #ffffff;
                --flavor-bg-alt: #f8f9fa;
                --flavor-border: #e2e8f0;
                --flavor-radius: 8px;
                --flavor-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
                --flavor-shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
                --flavor-transition: 0.2s ease;
            }
        </style>
        <?php
    }

    /**
     * Add custom body classes
     */
    public function add_body_classes( array $classes ): array {
        $classes[] = 'flavor-magazine';
        $config = get_option( 'flavor_seo_config', [] );
        if ( ! empty( $config['blog_topic'] ) ) {
            $classes[] = 'flavor-topic-' . sanitize_html_class( $config['blog_topic'] );
        }
        return $classes;
    }

    /**
     * Render Person schema on author archive pages
     */
    public function render_author_schema_on_archive(): void {
        if ( ! is_author() ) {
            return;
        }

        $author = get_queried_object();
        if ( ! $author ) {
            return;
        }

        $expertise = get_user_meta( $author->ID, 'flavor_expertise', true );

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Person',
            'name'        => $author->display_name,
            'url'         => get_author_posts_url( $author->ID ),
            'description' => $author->description ?? '',
        ];

        if ( ! empty( $expertise ) ) {
            $schema['knowsAbout'] = array_map( 'trim', explode( ',', $expertise ) );
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    /**
     * Generate magazine CSS file
     */
    private function generate_magazine_css( array $config ): void {
        $primary = $config['primary_color'] ?? '#1e3a5f';
        $accent  = $config['accent_color'] ?? '#c9a227';
        $primary_light = $this->adjust_brightness( $primary, 40 );
        $primary_dark  = $this->adjust_brightness( $primary, -20 );
        $accent_light  = $this->adjust_brightness( $accent, 30 );

        $css = <<<CSS
/* Flavor SEO Starter – Magazine Design */
/* Auto-generated – do not edit manually */

/* ============================================
   TYPOGRAPHY
   ============================================ */
body.flavor-magazine {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: var(--flavor-text, #1a1a2e);
    line-height: 1.7;
    -webkit-font-smoothing: antialiased;
}

body.flavor-magazine h1,
body.flavor-magazine h2,
body.flavor-magazine h3,
body.flavor-magazine h4,
body.flavor-magazine h5,
body.flavor-magazine h6 {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-weight: 700;
    line-height: 1.3;
    color: var(--flavor-primary, {$primary});
}

body.flavor-magazine h1 { font-size: clamp(1.8rem, 4vw, 2.5rem); }
body.flavor-magazine h2 { font-size: clamp(1.4rem, 3vw, 1.8rem); }
body.flavor-magazine h3 { font-size: clamp(1.1rem, 2.5vw, 1.4rem); }

/* ============================================
   LINKS & INTERACTIVE
   ============================================ */
body.flavor-magazine a {
    color: var(--flavor-primary, {$primary});
    text-decoration: none;
    transition: color var(--flavor-transition, 0.2s ease);
}

body.flavor-magazine a:hover {
    color: var(--flavor-accent, {$accent});
}

/* ============================================
   ARTICLE CARDS (Archive / Blog listing)
   ============================================ */
body.flavor-magazine .post,
body.flavor-magazine article {
    margin-bottom: 2rem;
}

body.flavor-magazine .entry-title a,
body.flavor-magazine .wp-block-post-title a {
    color: var(--flavor-text, #1a1a2e);
    text-decoration: none;
    transition: color var(--flavor-transition, 0.2s ease);
}

body.flavor-magazine .entry-title a:hover,
body.flavor-magazine .wp-block-post-title a:hover {
    color: var(--flavor-accent, {$accent});
}

/* ============================================
   SINGLE ARTICLE STYLING
   ============================================ */
body.flavor-magazine .entry-content,
body.flavor-magazine .wp-block-post-content {
    font-size: 1.05rem;
    max-width: 720px;
    margin-left: auto;
    margin-right: auto;
}

body.flavor-magazine .entry-content p {
    margin-bottom: 1.4em;
}

body.flavor-magazine .entry-content blockquote,
body.flavor-magazine blockquote {
    border-left: 4px solid var(--flavor-accent, {$accent});
    padding: 1rem 1.5rem;
    margin: 2rem 0;
    background: var(--flavor-bg-alt, #f8f9fa);
    border-radius: 0 var(--flavor-radius, 8px) var(--flavor-radius, 8px) 0;
    font-style: italic;
    color: var(--flavor-text-light, #4a4a68);
}

body.flavor-magazine .entry-content img {
    border-radius: var(--flavor-radius, 8px);
    box-shadow: var(--flavor-shadow, 0 1px 3px rgba(0,0,0,0.08));
}

/* ============================================
   META & AUTHOR INFO
   ============================================ */
body.flavor-magazine .entry-meta,
body.flavor-magazine .wp-block-post-date,
body.flavor-magazine .wp-block-post-author {
    font-size: 0.875rem;
    color: var(--flavor-text-light, #4a4a68);
}

/* Author box at bottom of articles */
.flavor-author-box {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    margin: 3rem 0;
    background: var(--flavor-bg-alt, #f8f9fa);
    border-radius: var(--flavor-radius, 8px);
    border: 1px solid var(--flavor-border, #e2e8f0);
}

.flavor-author-box .avatar {
    border-radius: 50%;
    flex-shrink: 0;
}

.flavor-author-box .author-name {
    font-weight: 700;
    color: var(--flavor-primary, {$primary});
    margin-bottom: 0.25rem;
}

.flavor-author-box .author-bio {
    font-size: 0.9rem;
    color: var(--flavor-text-light, #4a4a68);
}

/* ============================================
   CATEGORY BADGES
   ============================================ */
body.flavor-magazine .cat-links a,
body.flavor-magazine .wp-block-post-terms a {
    display: inline-block;
    padding: 0.2em 0.7em;
    background: var(--flavor-primary, {$primary});
    color: #fff !important;
    border-radius: 3px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: background var(--flavor-transition, 0.2s ease);
}

body.flavor-magazine .cat-links a:hover,
body.flavor-magazine .wp-block-post-terms a:hover {
    background: var(--flavor-accent, {$accent});
}

/* ============================================
   COOKIE BANNER
   ============================================ */
#flavor-cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--flavor-primary, {$primary});
    color: #fff;
    z-index: 999999;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.15);
}

#flavor-cookie-banner .flavor-cookie-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}

#flavor-cookie-banner p {
    margin: 0;
    font-size: 0.9rem;
    flex: 1;
}

#flavor-cookie-banner a {
    color: var(--flavor-accent-light, {$accent_light});
    text-decoration: underline;
}

.flavor-cookie-buttons {
    display: flex;
    gap: 0.75rem;
    flex-shrink: 0;
}

.flavor-cookie-btn {
    padding: 0.6rem 1.2rem;
    border: none;
    border-radius: var(--flavor-radius, 8px);
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all var(--flavor-transition, 0.2s ease);
}

.flavor-cookie-accept {
    background: var(--flavor-accent, {$accent});
    color: #fff;
}

.flavor-cookie-accept:hover {
    background: var(--flavor-accent-light, {$accent_light});
}

.flavor-cookie-essential {
    background: transparent;
    color: #fff;
    border: 1px solid rgba(255,255,255,0.4) !important;
}

.flavor-cookie-essential:hover {
    background: rgba(255,255,255,0.1);
}

/* ============================================
   LEAD PARAGRAPH
   ============================================ */
body.flavor-magazine .lead,
body.flavor-magazine p.lead {
    font-size: 1.2rem;
    color: var(--flavor-text-light, #4a4a68);
    line-height: 1.6;
    font-weight: 400;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 768px) {
    #flavor-cookie-banner .flavor-cookie-inner {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
    }

    .flavor-cookie-buttons {
        width: 100%;
        justify-content: center;
    }

    .flavor-author-box {
        flex-direction: column;
        text-align: center;
    }
}

/* ============================================
   PRINT STYLES
   ============================================ */
@media print {
    #flavor-cookie-banner { display: none !important; }
    body.flavor-magazine a[href]:after { content: " (" attr(href) ")"; }
}
CSS;

        $css_file = FLAVOR_SEO_PLUGIN_DIR . 'public/css/magazine.css';
        file_put_contents( $css_file, $css );
    }

    /**
     * Configure reading settings for a blog/magazine
     */
    private function configure_reading_settings(): void {
        // Show latest posts on front page (magazine style)
        update_option( 'show_on_front', 'posts' );
        update_option( 'posts_per_page', 12 );

        // Enable threaded comments
        update_option( 'thread_comments', 1 );
        update_option( 'thread_comments_depth', 3 );
    }

    /**
     * Adjust hex color brightness
     */
    private function adjust_brightness( string $hex, int $percent ): string {
        $hex = ltrim( $hex, '#' );
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );

        $r = max( 0, min( 255, $r + (int) ( $r * $percent / 100 ) ) );
        $g = max( 0, min( 255, $g + (int) ( $g * $percent / 100 ) ) );
        $b = max( 0, min( 255, $b + (int) ( $b * $percent / 100 ) ) );

        return sprintf( '#%02x%02x%02x', $r, $g, $b );
    }
}
