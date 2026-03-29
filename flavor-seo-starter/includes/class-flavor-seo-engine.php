<?php
/**
 * SEO Engine – Meta-Tags, Schema.org JSON-LD, Open Graph, Twitter Cards,
 * robots.txt Optimierung, Canonical URLs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Flavor_SEO_Engine {

    public function __construct() {
        $config = get_option( 'flavor_seo_config', [] );
        if ( empty( $config ) ) {
            return;
        }

        add_action( 'wp_head', [ $this, 'render_meta_tags' ], 1 );
        add_action( 'wp_head', [ $this, 'render_schema_json_ld' ], 2 );
        add_action( 'wp_head', [ $this, 'render_open_graph' ], 3 );
        add_filter( 'robots_txt', [ $this, 'optimize_robots_txt' ], 10, 2 );
        add_filter( 'document_title_parts', [ $this, 'optimize_title' ] );
        add_action( 'wp_head', [ $this, 'render_canonical' ], 1 );
    }

    /**
     * Run initial SEO setup
     */
    public function setup( array $config ): array {
        // Store SEO-specific settings
        $seo_settings = [
            'title_separator' => '—',
            'meta_description' => $this->generate_meta_description( $config ),
            'schema_type'      => $this->get_schema_type( $config['blog_topic'] ?? '' ),
        ];
        update_option( 'flavor_seo_settings', $seo_settings );

        return [ 'status' => 'ok', 'message' => 'SEO-Engine konfiguriert' ];
    }

    /**
     * Render essential meta tags
     */
    public function render_meta_tags(): void {
        $config = get_option( 'flavor_seo_config', [] );
        $seo    = get_option( 'flavor_seo_settings', [] );

        if ( is_singular() ) {
            $description = get_the_excerpt();
            if ( empty( $description ) ) {
                $description = wp_trim_words( get_the_content(), 25 );
            }
        } else {
            $description = $seo['meta_description'] ?? get_bloginfo( 'description' );
        }

        $description = wp_strip_all_tags( $description );
        ?>
        <meta name="description" content="<?php echo esc_attr( $description ); ?>">
        <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
        <meta name="author" content="<?php echo esc_attr( $config['author_name'] ?? $config['owner_name'] ?? '' ); ?>">
        <?php
    }

    /**
     * Render Schema.org JSON-LD structured data
     */
    public function render_schema_json_ld(): void {
        $config = get_option( 'flavor_seo_config', [] );
        $seo    = get_option( 'flavor_seo_settings', [] );

        // Organization Schema
        $org_schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => $config['blog_name'] ?? get_bloginfo( 'name' ),
            'url'      => home_url( '/' ),
            'logo'     => $this->get_logo_url(),
            'contactPoint' => [
                '@type'       => 'ContactPoint',
                'email'       => $config['owner_email'] ?? '',
                'contactType' => 'customer service',
            ],
        ];

        if ( ! empty( $config['owner_phone'] ) ) {
            $org_schema['contactPoint']['telephone'] = $config['owner_phone'];
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $org_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

        // Website Schema with SearchAction (for Google Sitelinks Search)
        $website_schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => $config['blog_name'] ?? get_bloginfo( 'name' ),
            'url'             => home_url( '/' ),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ],
        ];
        echo '<script type="application/ld+json">' . wp_json_encode( $website_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

        // Article Schema for single posts
        if ( is_singular( 'post' ) ) {
            $this->render_article_schema( $config );
        }

        // BreadcrumbList
        if ( ! is_front_page() ) {
            $this->render_breadcrumb_schema();
        }
    }

    /**
     * Render Article schema for blog posts
     */
    private function render_article_schema( array $config ): void {
        global $post;

        $schema = [
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => get_the_title(),
            'description'      => wp_strip_all_tags( get_the_excerpt() ),
            'datePublished'    => get_the_date( 'c' ),
            'dateModified'     => get_the_modified_date( 'c' ),
            'mainEntityOfPage' => get_permalink(),
            'author'           => [
                '@type' => 'Person',
                'name'  => get_the_author(),
                'url'   => get_author_posts_url( $post->post_author ),
            ],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => $config['blog_name'] ?? get_bloginfo( 'name' ),
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => $this->get_logo_url(),
                ],
            ],
        ];

        // Featured image
        if ( has_post_thumbnail() ) {
            $img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
            if ( $img ) {
                $schema['image'] = [
                    '@type'  => 'ImageObject',
                    'url'    => $img[0],
                    'width'  => $img[1],
                    'height' => $img[2],
                ];
            }
        }

        // Word count for E-E-A-T
        $schema['wordCount'] = str_word_count( wp_strip_all_tags( $post->post_content ) );

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    /**
     * Render breadcrumb structured data
     */
    private function render_breadcrumb_schema(): void {
        $items   = [];
        $items[] = [
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => __( 'Startseite', 'flavor-seo-starter' ),
            'item'     => home_url( '/' ),
        ];

        $position = 2;
        if ( is_category() || is_single() ) {
            $cats = get_the_category();
            if ( ! empty( $cats ) ) {
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => $position++,
                    'name'     => $cats[0]->name,
                    'item'     => get_category_link( $cats[0]->term_id ),
                ];
            }
        }

        if ( is_single() ) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => get_the_title(),
                'item'     => get_permalink(),
            ];
        }

        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    /**
     * Render Open Graph and Twitter Card meta tags
     */
    public function render_open_graph(): void {
        $config = get_option( 'flavor_seo_config', [] );
        $lang   = str_replace( '_', '-', $config['blog_language'] ?? 'de-DE' );

        echo '<meta property="og:locale" content="' . esc_attr( $config['blog_language'] ?? 'de_DE' ) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr( $config['blog_name'] ?? get_bloginfo( 'name' ) ) . '">' . "\n";

        if ( is_singular() ) {
            echo '<meta property="og:type" content="article">' . "\n";
            echo '<meta property="og:title" content="' . esc_attr( get_the_title() ) . '">' . "\n";
            echo '<meta property="og:description" content="' . esc_attr( wp_strip_all_tags( get_the_excerpt() ) ) . '">' . "\n";
            echo '<meta property="og:url" content="' . esc_url( get_permalink() ) . '">' . "\n";
            echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
            echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
            echo '<meta property="article:author" content="' . esc_attr( get_the_author() ) . '">' . "\n";

            if ( has_post_thumbnail() ) {
                $img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
                if ( $img ) {
                    echo '<meta property="og:image" content="' . esc_url( $img[0] ) . '">' . "\n";
                    echo '<meta property="og:image:width" content="' . esc_attr( $img[1] ) . '">' . "\n";
                    echo '<meta property="og:image:height" content="' . esc_attr( $img[2] ) . '">' . "\n";
                }
            }

            // Twitter Card
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
            echo '<meta name="twitter:title" content="' . esc_attr( get_the_title() ) . '">' . "\n";
            echo '<meta name="twitter:description" content="' . esc_attr( wp_strip_all_tags( get_the_excerpt() ) ) . '">' . "\n";
        } else {
            echo '<meta property="og:type" content="website">' . "\n";
            echo '<meta property="og:title" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
            echo '<meta property="og:description" content="' . esc_attr( get_bloginfo( 'description' ) ) . '">' . "\n";
            echo '<meta property="og:url" content="' . esc_url( home_url( '/' ) ) . '">' . "\n";
            echo '<meta name="twitter:card" content="summary">' . "\n";
        }
    }

    /**
     * Optimize robots.txt
     */
    public function optimize_robots_txt( string $output, bool $public ): string {
        if ( ! $public ) {
            return $output;
        }

        $custom = "User-agent: *\n";
        $custom .= "Allow: /\n";
        $custom .= "Disallow: /wp-admin/\n";
        $custom .= "Allow: /wp-admin/admin-ajax.php\n";
        $custom .= "Disallow: /wp-includes/\n";
        $custom .= "Disallow: /?s=\n";
        $custom .= "Disallow: /search/\n";
        $custom .= "Disallow: /tag/\n";
        $custom .= "\n";
        $custom .= "# AI Crawlers Welcome\n";
        $custom .= "User-agent: GPTBot\n";
        $custom .= "Allow: /\n";
        $custom .= "User-agent: Google-Extended\n";
        $custom .= "Allow: /\n";
        $custom .= "User-agent: Anthropic-AI\n";
        $custom .= "Allow: /\n";
        $custom .= "User-agent: ClaudeBot\n";
        $custom .= "Allow: /\n";
        $custom .= "User-agent: Amazonbot\n";
        $custom .= "Allow: /\n";
        $custom .= "User-agent: PerplexityBot\n";
        $custom .= "Allow: /\n";
        $custom .= "\n";
        $custom .= "Sitemap: " . home_url( '/wp-sitemap.xml' ) . "\n";

        return $custom;
    }

    /**
     * Optimize document title
     */
    public function optimize_title( array $title_parts ): array {
        $seo = get_option( 'flavor_seo_settings', [] );
        // Use dash separator for cleaner look
        add_filter( 'document_title_separator', function () use ( $seo ) {
            return $seo['title_separator'] ?? '—';
        } );
        return $title_parts;
    }

    /**
     * Render canonical URL
     */
    public function render_canonical(): void {
        if ( is_singular() ) {
            echo '<link rel="canonical" href="' . esc_url( get_permalink() ) . '">' . "\n";
        } elseif ( is_home() || is_front_page() ) {
            echo '<link rel="canonical" href="' . esc_url( home_url( '/' ) ) . '">' . "\n";
        }
    }

    /**
     * Generate a meta description from config
     */
    private function generate_meta_description( array $config ): string {
        $topic_labels = $this->get_topic_labels();
        $topic        = $topic_labels[ $config['blog_topic'] ] ?? $config['blog_topic'];
        $name         = $config['blog_name'] ?? '';

        return sprintf(
            '%s – Dein Magazin für %s. Aktuelle Artikel, Expertenwissen und Trends.',
            $name,
            $topic
        );
    }

    /**
     * Get schema type based on topic
     */
    private function get_schema_type( string $topic ): string {
        $types = [
            'technology' => 'TechArticle',
            'health'     => 'MedicalWebPage',
            'finance'    => 'FinancialProduct',
            'food'       => 'Recipe',
            'education'  => 'EducationalOrganization',
        ];
        return $types[ $topic ] ?? 'Article';
    }

    /**
     * Get topic label mapping
     */
    private function get_topic_labels(): array {
        return [
            'technology' => 'Technologie & Digital',
            'health'     => 'Gesundheit & Wellness',
            'finance'    => 'Finanzen & Business',
            'lifestyle'  => 'Lifestyle & Mode',
            'food'       => 'Food & Rezepte',
            'travel'     => 'Reisen & Abenteuer',
            'sports'     => 'Sport & Fitness',
            'education'  => 'Bildung & Wissen',
            'marketing'  => 'Marketing & SEO',
            'auto'       => 'Auto & Mobilität',
            'gaming'     => 'Gaming & Entertainment',
            'diy'        => 'DIY & Handwerk',
        ];
    }

    /**
     * Get logo URL or fallback
     */
    private function get_logo_url(): string {
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        if ( $custom_logo_id ) {
            $logo = wp_get_attachment_image_src( $custom_logo_id, 'full' );
            if ( $logo ) {
                return $logo[0];
            }
        }
        return home_url( '/favicon.ico' );
    }
}
