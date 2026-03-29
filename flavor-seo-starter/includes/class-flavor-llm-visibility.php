<?php
/**
 * LLM Visibility – llms.txt, llms-full.txt, AI-optimierte strukturierte Daten
 * Macht den Blog für AI-Crawler und LLMs optimal auffindbar und interpretierbar.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Flavor_LLM_Visibility {

    public function __construct() {
        if ( ! get_option( 'flavor_seo_setup_complete' ) ) {
            return;
        }
        add_action( 'init', [ $this, 'register_rewrite_rules' ] );
        add_action( 'template_redirect', [ $this, 'serve_llms_files' ] );
        add_action( 'wp_head', [ $this, 'render_ai_meta_tags' ], 5 );
        add_action( 'wp_head', [ $this, 'render_faq_schema' ], 6 );
    }

    /**
     * Initial setup
     */
    public function setup( array $config ): array {
        $this->generate_llms_content( $config );
        flush_rewrite_rules();
        return [ 'status' => 'ok', 'message' => 'LLM-Sichtbarkeit konfiguriert' ];
    }

    /**
     * Register rewrite rules for llms.txt and llms-full.txt
     */
    public function register_rewrite_rules(): void {
        add_rewrite_rule( '^llms\.txt$', 'index.php?flavor_llms=short', 'top' );
        add_rewrite_rule( '^llms-full\.txt$', 'index.php?flavor_llms=full', 'top' );
        add_rewrite_tag( '%flavor_llms%', '([^&]+)' );
    }

    /**
     * Serve llms.txt and llms-full.txt
     */
    public function serve_llms_files(): void {
        $llms_type = get_query_var( 'flavor_llms' );
        if ( empty( $llms_type ) ) {
            return;
        }

        header( 'Content-Type: text/plain; charset=utf-8' );
        header( 'X-Robots-Tag: noindex' );

        $config = get_option( 'flavor_seo_config', [] );

        if ( 'short' === $llms_type ) {
            echo $this->build_llms_short( $config );
        } else {
            echo $this->build_llms_full( $config );
        }
        exit;
    }

    /**
     * Build llms.txt (short version)
     */
    private function build_llms_short( array $config ): string {
        $name  = $config['blog_name'] ?? get_bloginfo( 'name' );
        $desc  = $config['blog_tagline'] ?? get_bloginfo( 'description' );
        $topic_labels = $this->get_topic_labels();
        $topic = $topic_labels[ $config['blog_topic'] ?? '' ] ?? ( $config['blog_topic'] ?? '' );

        $out  = "# {$name}\n\n";
        $out .= "> {$desc}\n\n";
        $out .= "## About\n\n";
        $out .= "{$name} ist ein Magazin/Blog zum Thema {$topic}. ";
        $out .= "Betrieben von {$config['owner_name']}.\n\n";

        if ( ! empty( $config['author_name'] ) ) {
            $out .= "Chefredakteur: {$config['author_name']}";
            if ( ! empty( $config['author_expertise'] ) ) {
                $out .= " ({$config['author_expertise']})";
            }
            $out .= "\n\n";
        }

        $out .= "## Links\n\n";
        $out .= "- [Startseite](" . home_url( '/' ) . ")\n";
        $out .= "- [Über uns](" . home_url( '/ueber-uns/' ) . ")\n";
        $out .= "- [Impressum](" . home_url( '/impressum/' ) . ")\n";
        $out .= "- [Datenschutz](" . home_url( '/datenschutz/' ) . ")\n";
        $out .= "- [Vollständige LLM-Infos](" . home_url( '/llms-full.txt' ) . ")\n";
        $out .= "- [Sitemap](" . home_url( '/wp-sitemap.xml' ) . ")\n";

        return $out;
    }

    /**
     * Build llms-full.txt (detailed version with all content)
     */
    private function build_llms_full( array $config ): string {
        $out = $this->build_llms_short( $config );

        // Add categories
        $categories = get_categories( [ 'hide_empty' => false ] );
        if ( ! empty( $categories ) ) {
            $out .= "\n## Kategorien\n\n";
            foreach ( $categories as $cat ) {
                if ( 'Uncategorized' === $cat->name || 'Allgemein' === $cat->name ) {
                    continue;
                }
                $out .= "- [{$cat->name}](" . get_category_link( $cat->term_id ) . ")";
                if ( ! empty( $cat->description ) ) {
                    $out .= ": {$cat->description}";
                }
                $out .= "\n";
            }
        }

        // Add recent articles
        $posts = get_posts( [
            'numberposts'  => 50,
            'post_status'  => 'publish',
            'post_type'    => 'post',
        ] );

        if ( ! empty( $posts ) ) {
            $out .= "\n## Artikel\n\n";
            foreach ( $posts as $p ) {
                $excerpt = wp_strip_all_tags( $p->post_excerpt ?: wp_trim_words( $p->post_content, 30 ) );
                $out .= "### [{$p->post_title}](" . get_permalink( $p ) . ")\n";
                $out .= "{$excerpt}\n\n";
            }
        }

        // Add pages
        $pages = get_pages( [ 'post_status' => 'publish' ] );
        if ( ! empty( $pages ) ) {
            $out .= "\n## Seiten\n\n";
            foreach ( $pages as $page ) {
                $out .= "- [{$page->post_title}](" . get_page_link( $page ) . ")\n";
            }
        }

        return $out;
    }

    /**
     * Render AI-specific meta tags
     */
    public function render_ai_meta_tags(): void {
        $config = get_option( 'flavor_seo_config', [] );
        ?>
        <link rel="alternate" type="text/plain" href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" title="LLM Information">
        <meta name="ai-content-declaration" content="human-created">
        <meta name="citation_title" content="<?php echo esc_attr( is_singular() ? get_the_title() : get_bloginfo( 'name' ) ); ?>">
        <meta name="citation_author" content="<?php echo esc_attr( $config['author_name'] ?? $config['owner_name'] ?? '' ); ?>">
        <meta name="citation_publication_date" content="<?php echo esc_attr( is_singular() ? get_the_date( 'Y/m/d' ) : gmdate( 'Y/m/d' ) ); ?>">
        <meta name="citation_publisher" content="<?php echo esc_attr( $config['blog_name'] ?? get_bloginfo( 'name' ) ); ?>">
        <?php
    }

    /**
     * Auto-generate FAQ Schema from H2/H3 headings in content
     */
    public function render_faq_schema(): void {
        if ( ! is_singular( 'post' ) ) {
            return;
        }

        global $post;
        $content = $post->post_content;

        // Find question-like headings (starting with common question words)
        preg_match_all(
            '/<h[23][^>]*>((?:Was|Wie|Warum|Wer|Wann|Wo|Welche|Which|What|How|Why|When|Where|Who|Kann|Ist|Sind|Gibt)[^<]*\??)<\/h[23]>/iu',
            $content,
            $matches
        );

        if ( empty( $matches[1] ) ) {
            return;
        }

        $faq_items = [];
        foreach ( $matches[1] as $question ) {
            // Get content after the heading until next heading
            $pos = strpos( $content, $question );
            if ( false === $pos ) {
                continue;
            }
            $after   = substr( $content, $pos + strlen( $question ) );
            $next_h  = preg_match( '/<h[23]/', $after, $h_match, PREG_OFFSET_MATCH );
            $answer  = $next_h ? substr( $after, 0, $h_match[0][1] ) : $after;
            $answer  = wp_strip_all_tags( $answer );
            $answer  = wp_trim_words( $answer, 50 );

            if ( strlen( $answer ) > 20 ) {
                $faq_items[] = [
                    '@type'          => 'Question',
                    'name'           => wp_strip_all_tags( $question ),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $answer,
                    ],
                ];
            }
        }

        if ( empty( $faq_items ) ) {
            return;
        }

        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $faq_items,
        ];

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    /**
     * Generate and store llms content data
     */
    private function generate_llms_content( array $config ): void {
        update_option( 'flavor_llms_generated', true );
    }

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
}
