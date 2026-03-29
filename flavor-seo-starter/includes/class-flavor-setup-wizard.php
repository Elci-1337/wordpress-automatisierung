<?php
/**
 * Setup Wizard – Das Herzstück des Plugins
 * Ein einziges Formular → komplettes Blog-Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Flavor_Setup_Wizard {

    public function __construct() {
        add_action( 'wp_ajax_flavor_run_setup', [ $this, 'handle_setup' ] );
    }

    /**
     * Render the setup wizard page
     */
    public function render(): void {
        $config   = get_option( 'flavor_seo_config', [] );
        $complete = get_option( 'flavor_seo_setup_complete', false );
        ?>
        <div class="wrap flavor-wrap">
            <div class="flavor-header">
                <h1><?php esc_html_e( '🚀 Flavor SEO Starter', 'flavor-seo-starter' ); ?></h1>
                <p class="flavor-subtitle">
                    <?php esc_html_e( 'Verwandle deine leere WordPress-Instanz in ein professionelles, SEO-optimiertes Magazin.', 'flavor-seo-starter' ); ?>
                </p>
            </div>

            <?php if ( $complete ) : ?>
                <div class="flavor-notice flavor-notice-success">
                    <p><?php esc_html_e( 'Setup wurde bereits durchgeführt. Du kannst es erneut ausführen, um die Einstellungen zu aktualisieren.', 'flavor-seo-starter' ); ?></p>
                </div>
            <?php endif; ?>

            <form id="flavor-setup-form" class="flavor-form">
                <?php wp_nonce_field( 'flavor_seo_nonce', 'flavor_nonce' ); ?>

                <!-- Step 1: Basis-Informationen -->
                <div class="flavor-card" id="step-basis">
                    <h2><?php esc_html_e( '1. Basis-Informationen', 'flavor-seo-starter' ); ?></h2>

                    <div class="flavor-field">
                        <label for="blog_name"><?php esc_html_e( 'Blog- / Markenname', 'flavor-seo-starter' ); ?> *</label>
                        <input type="text" id="blog_name" name="blog_name"
                               value="<?php echo esc_attr( $config['blog_name'] ?? '' ); ?>"
                               placeholder="z.B. TechPulse Magazine" required>
                        <p class="description"><?php esc_html_e( 'Der Name deines Blogs/Magazins – wird überall verwendet.', 'flavor-seo-starter' ); ?></p>
                    </div>

                    <div class="flavor-field">
                        <label for="blog_tagline"><?php esc_html_e( 'Tagline / Untertitel', 'flavor-seo-starter' ); ?></label>
                        <input type="text" id="blog_tagline" name="blog_tagline"
                               value="<?php echo esc_attr( $config['blog_tagline'] ?? '' ); ?>"
                               placeholder="z.B. Dein Magazin für Technologie & Innovation">
                    </div>

                    <div class="flavor-field">
                        <label for="blog_topic"><?php esc_html_e( 'Thema / Nische', 'flavor-seo-starter' ); ?> *</label>
                        <select id="blog_topic" name="blog_topic" required>
                            <option value=""><?php esc_html_e( '— Thema wählen —', 'flavor-seo-starter' ); ?></option>
                            <option value="technology" <?php selected( $config['blog_topic'] ?? '', 'technology' ); ?>><?php esc_html_e( 'Technologie & Digital', 'flavor-seo-starter' ); ?></option>
                            <option value="health" <?php selected( $config['blog_topic'] ?? '', 'health' ); ?>><?php esc_html_e( 'Gesundheit & Wellness', 'flavor-seo-starter' ); ?></option>
                            <option value="finance" <?php selected( $config['blog_topic'] ?? '', 'finance' ); ?>><?php esc_html_e( 'Finanzen & Business', 'flavor-seo-starter' ); ?></option>
                            <option value="lifestyle" <?php selected( $config['blog_topic'] ?? '', 'lifestyle' ); ?>><?php esc_html_e( 'Lifestyle & Mode', 'flavor-seo-starter' ); ?></option>
                            <option value="food" <?php selected( $config['blog_topic'] ?? '', 'food' ); ?>><?php esc_html_e( 'Food & Rezepte', 'flavor-seo-starter' ); ?></option>
                            <option value="travel" <?php selected( $config['blog_topic'] ?? '', 'travel' ); ?>><?php esc_html_e( 'Reisen & Abenteuer', 'flavor-seo-starter' ); ?></option>
                            <option value="sports" <?php selected( $config['blog_topic'] ?? '', 'sports' ); ?>><?php esc_html_e( 'Sport & Fitness', 'flavor-seo-starter' ); ?></option>
                            <option value="education" <?php selected( $config['blog_topic'] ?? '', 'education' ); ?>><?php esc_html_e( 'Bildung & Wissen', 'flavor-seo-starter' ); ?></option>
                            <option value="marketing" <?php selected( $config['blog_topic'] ?? '', 'marketing' ); ?>><?php esc_html_e( 'Marketing & SEO', 'flavor-seo-starter' ); ?></option>
                            <option value="auto" <?php selected( $config['blog_topic'] ?? '', 'auto' ); ?>><?php esc_html_e( 'Auto & Mobilität', 'flavor-seo-starter' ); ?></option>
                            <option value="gaming" <?php selected( $config['blog_topic'] ?? '', 'gaming' ); ?>><?php esc_html_e( 'Gaming & Entertainment', 'flavor-seo-starter' ); ?></option>
                            <option value="diy" <?php selected( $config['blog_topic'] ?? '', 'diy' ); ?>><?php esc_html_e( 'DIY & Handwerk', 'flavor-seo-starter' ); ?></option>
                            <option value="custom" <?php selected( $config['blog_topic'] ?? '', 'custom' ); ?>><?php esc_html_e( 'Eigenes Thema (unten angeben)', 'flavor-seo-starter' ); ?></option>
                        </select>
                    </div>

                    <div class="flavor-field">
                        <label for="blog_language"><?php esc_html_e( 'Sprache', 'flavor-seo-starter' ); ?></label>
                        <select id="blog_language" name="blog_language">
                            <option value="de_DE" <?php selected( $config['blog_language'] ?? 'de_DE', 'de_DE' ); ?>>Deutsch (Deutschland)</option>
                            <option value="de_AT" <?php selected( $config['blog_language'] ?? '', 'de_AT' ); ?>>Deutsch (Österreich)</option>
                            <option value="de_CH" <?php selected( $config['blog_language'] ?? '', 'de_CH' ); ?>>Deutsch (Schweiz)</option>
                            <option value="en_US" <?php selected( $config['blog_language'] ?? '', 'en_US' ); ?>>English (US)</option>
                            <option value="en_GB" <?php selected( $config['blog_language'] ?? '', 'en_GB' ); ?>>English (UK)</option>
                        </select>
                    </div>
                </div>

                <!-- Step 2: Betreiber & E-E-A-T -->
                <div class="flavor-card" id="step-eeat">
                    <h2><?php esc_html_e( '2. Betreiber & E-E-A-T Informationen', 'flavor-seo-starter' ); ?></h2>

                    <div class="flavor-field-row">
                        <div class="flavor-field">
                            <label for="owner_name"><?php esc_html_e( 'Betreiber / Firmenname', 'flavor-seo-starter' ); ?> *</label>
                            <input type="text" id="owner_name" name="owner_name"
                                   value="<?php echo esc_attr( $config['owner_name'] ?? '' ); ?>"
                                   placeholder="z.B. Max Mustermann GmbH" required>
                        </div>
                        <div class="flavor-field">
                            <label for="owner_email"><?php esc_html_e( 'E-Mail', 'flavor-seo-starter' ); ?> *</label>
                            <input type="email" id="owner_email" name="owner_email"
                                   value="<?php echo esc_attr( $config['owner_email'] ?? '' ); ?>"
                                   placeholder="kontakt@example.de" required>
                        </div>
                    </div>

                    <div class="flavor-field-row">
                        <div class="flavor-field">
                            <label for="owner_street"><?php esc_html_e( 'Straße & Hausnummer', 'flavor-seo-starter' ); ?></label>
                            <input type="text" id="owner_street" name="owner_street"
                                   value="<?php echo esc_attr( $config['owner_street'] ?? '' ); ?>"
                                   placeholder="Musterstraße 1">
                        </div>
                        <div class="flavor-field">
                            <label for="owner_zip_city"><?php esc_html_e( 'PLZ & Ort', 'flavor-seo-starter' ); ?></label>
                            <input type="text" id="owner_zip_city" name="owner_zip_city"
                                   value="<?php echo esc_attr( $config['owner_zip_city'] ?? '' ); ?>"
                                   placeholder="12345 Musterstadt">
                        </div>
                    </div>

                    <div class="flavor-field-row">
                        <div class="flavor-field">
                            <label for="owner_country"><?php esc_html_e( 'Land', 'flavor-seo-starter' ); ?></label>
                            <input type="text" id="owner_country" name="owner_country"
                                   value="<?php echo esc_attr( $config['owner_country'] ?? 'Deutschland' ); ?>"
                                   placeholder="Deutschland">
                        </div>
                        <div class="flavor-field">
                            <label for="owner_phone"><?php esc_html_e( 'Telefon', 'flavor-seo-starter' ); ?></label>
                            <input type="text" id="owner_phone" name="owner_phone"
                                   value="<?php echo esc_attr( $config['owner_phone'] ?? '' ); ?>"
                                   placeholder="+49 123 456789">
                        </div>
                    </div>

                    <div class="flavor-field">
                        <label for="author_name"><?php esc_html_e( 'Hauptautor / Chefredakteur', 'flavor-seo-starter' ); ?></label>
                        <input type="text" id="author_name" name="author_name"
                               value="<?php echo esc_attr( $config['author_name'] ?? '' ); ?>"
                               placeholder="z.B. Dr. Anna Schmidt">
                    </div>

                    <div class="flavor-field">
                        <label for="author_bio"><?php esc_html_e( 'Kurz-Bio des Hauptautors', 'flavor-seo-starter' ); ?></label>
                        <textarea id="author_bio" name="author_bio" rows="3"
                                  placeholder="z.B. Expertin für digitale Trends mit über 10 Jahren Erfahrung..."><?php echo esc_textarea( $config['author_bio'] ?? '' ); ?></textarea>
                    </div>

                    <div class="flavor-field">
                        <label for="author_expertise"><?php esc_html_e( 'Expertise / Qualifikationen', 'flavor-seo-starter' ); ?></label>
                        <input type="text" id="author_expertise" name="author_expertise"
                               value="<?php echo esc_attr( $config['author_expertise'] ?? '' ); ?>"
                               placeholder="z.B. M.Sc. Informatik, Google-zertifiziert, 15 Jahre Branchenerfahrung">
                    </div>
                </div>

                <!-- Step 3: Individuelle Vorgaben -->
                <div class="flavor-card" id="step-custom">
                    <h2><?php esc_html_e( '3. Individuelle Vorgaben', 'flavor-seo-starter' ); ?></h2>

                    <div class="flavor-field">
                        <label for="custom_instructions"><?php esc_html_e( 'Freie Vorgaben & Wünsche', 'flavor-seo-starter' ); ?></label>
                        <textarea id="custom_instructions" name="custom_instructions" rows="5"
                                  placeholder="z.B. Farben: Dunkelblau + Gold, Zielgruppe: 25-45 jährige Professionals, Fokus auf Nachhaltigkeit..."><?php echo esc_textarea( $config['custom_instructions'] ?? '' ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Alles was du sonst noch angeben möchtest – Farben, Stil, Zielgruppe, besondere Wünsche.', 'flavor-seo-starter' ); ?></p>
                    </div>

                    <div class="flavor-field">
                        <label for="primary_color"><?php esc_html_e( 'Primärfarbe', 'flavor-seo-starter' ); ?></label>
                        <input type="color" id="primary_color" name="primary_color"
                               value="<?php echo esc_attr( $config['primary_color'] ?? '#1e3a5f' ); ?>">
                    </div>

                    <div class="flavor-field">
                        <label for="accent_color"><?php esc_html_e( 'Akzentfarbe', 'flavor-seo-starter' ); ?></label>
                        <input type="color" id="accent_color" name="accent_color"
                               value="<?php echo esc_attr( $config['accent_color'] ?? '#c9a227' ); ?>">
                    </div>

                    <div class="flavor-field">
                        <label><?php esc_html_e( 'Was soll erstellt werden?', 'flavor-seo-starter' ); ?></label>
                        <div class="flavor-checkboxes">
                            <label><input type="checkbox" name="modules[]" value="seo" checked> SEO-Optimierung (Meta, Schema, Open Graph)</label>
                            <label><input type="checkbox" name="modules[]" value="llm" checked> LLM-Sichtbarkeit (llms.txt, AI-Daten)</label>
                            <label><input type="checkbox" name="modules[]" value="geo" checked> GEO-Optimierung (lokale Signale)</label>
                            <label><input type="checkbox" name="modules[]" value="eeat" checked> E-E-A-T Seiten (Über uns, Autoren)</label>
                            <label><input type="checkbox" name="modules[]" value="legal" checked> Rechtliche Seiten (Impressum, Datenschutz)</label>
                            <label><input type="checkbox" name="modules[]" value="categories" checked> Kategorien & Menüstruktur</label>
                            <label><input type="checkbox" name="modules[]" value="design" checked> Magazin-Design & Styling</label>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flavor-card flavor-submit-card">
                    <div id="flavor-progress" class="flavor-progress" style="display:none;">
                        <div class="flavor-progress-bar">
                            <div class="flavor-progress-fill" id="flavor-progress-fill"></div>
                        </div>
                        <p id="flavor-progress-text"></p>
                    </div>

                    <div id="flavor-result" class="flavor-result" style="display:none;"></div>

                    <button type="submit" class="button button-primary button-hero flavor-submit-btn" id="flavor-submit">
                        <?php esc_html_e( 'Blog jetzt einrichten', 'flavor-seo-starter' ); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Handle AJAX setup request
     */
    public function handle_setup(): void {
        check_ajax_referer( 'flavor_seo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Keine Berechtigung.', 'flavor-seo-starter' ) );
        }

        $config = $this->sanitize_config( $_POST );
        update_option( 'flavor_seo_config', $config );

        $results = [];
        $modules = $config['modules'] ?? [];

        // 1. Site Identity
        $results['identity'] = $this->setup_site_identity( $config );

        // 2. SEO
        if ( in_array( 'seo', $modules, true ) ) {
            $seo = new Flavor_SEO_Engine();
            $results['seo'] = $seo->setup( $config );
        }

        // 3. LLM Visibility
        if ( in_array( 'llm', $modules, true ) ) {
            $llm = new Flavor_LLM_Visibility();
            $results['llm'] = $llm->setup( $config );
        }

        // 4. GEO
        if ( in_array( 'geo', $modules, true ) ) {
            $geo = new Flavor_GEO_Optimizer();
            $results['geo'] = $geo->setup( $config );
        }

        // 5. E-E-A-T
        if ( in_array( 'eeat', $modules, true ) ) {
            $eeat = new Flavor_EEAT_Generator();
            $results['eeat'] = $eeat->setup( $config );
        }

        // 6. Legal pages
        if ( in_array( 'legal', $modules, true ) ) {
            $legal = new Flavor_Legal_Pages();
            $results['legal'] = $legal->setup( $config );
        }

        // 7. Categories & Menus
        if ( in_array( 'categories', $modules, true ) ) {
            $cats = new Flavor_Category_Generator();
            $results['categories'] = $cats->setup( $config );
        }

        // 8. Design
        if ( in_array( 'design', $modules, true ) ) {
            $design = new Flavor_Design_Engine();
            $results['design'] = $design->setup( $config );
        }

        update_option( 'flavor_seo_setup_complete', true );

        wp_send_json_success( [
            'message' => __( 'Setup erfolgreich abgeschlossen!', 'flavor-seo-starter' ),
            'results' => $results,
        ] );
    }

    /**
     * Set basic site identity
     */
    private function setup_site_identity( array $config ): array {
        update_option( 'blogname', $config['blog_name'] );

        if ( ! empty( $config['blog_tagline'] ) ) {
            update_option( 'blogdescription', $config['blog_tagline'] );
        }

        // Set permalink structure to SEO-friendly
        update_option( 'permalink_structure', '/%postname%/' );

        // Set language
        if ( ! empty( $config['blog_language'] ) ) {
            update_option( 'WPLANG', $config['blog_language'] );
        }

        // Set timezone to Europe/Berlin for DACH
        $lang = $config['blog_language'] ?? 'de_DE';
        if ( str_starts_with( $lang, 'de' ) ) {
            update_option( 'timezone_string', 'Europe/Berlin' );
            update_option( 'date_format', 'd.m.Y' );
            update_option( 'time_format', 'H:i' );
        }

        // Discourage search engines until content is ready? No – we want indexing.
        update_option( 'blog_public', 1 );

        // Set reading settings
        update_option( 'posts_per_page', 12 );

        // Enable comments moderation
        update_option( 'comment_moderation', 1 );

        flush_rewrite_rules();

        return [ 'status' => 'ok', 'message' => 'Site Identity konfiguriert' ];
    }

    /**
     * Sanitize all config inputs
     */
    private function sanitize_config( array $data ): array {
        return [
            'blog_name'           => sanitize_text_field( $data['blog_name'] ?? '' ),
            'blog_tagline'        => sanitize_text_field( $data['blog_tagline'] ?? '' ),
            'blog_topic'          => sanitize_text_field( $data['blog_topic'] ?? '' ),
            'blog_language'       => sanitize_text_field( $data['blog_language'] ?? 'de_DE' ),
            'owner_name'          => sanitize_text_field( $data['owner_name'] ?? '' ),
            'owner_email'         => sanitize_email( $data['owner_email'] ?? '' ),
            'owner_street'        => sanitize_text_field( $data['owner_street'] ?? '' ),
            'owner_zip_city'      => sanitize_text_field( $data['owner_zip_city'] ?? '' ),
            'owner_country'       => sanitize_text_field( $data['owner_country'] ?? 'Deutschland' ),
            'owner_phone'         => sanitize_text_field( $data['owner_phone'] ?? '' ),
            'author_name'         => sanitize_text_field( $data['author_name'] ?? '' ),
            'author_bio'          => sanitize_textarea_field( $data['author_bio'] ?? '' ),
            'author_expertise'    => sanitize_text_field( $data['author_expertise'] ?? '' ),
            'custom_instructions' => sanitize_textarea_field( $data['custom_instructions'] ?? '' ),
            'primary_color'       => sanitize_hex_color( $data['primary_color'] ?? '#1e3a5f' ),
            'accent_color'        => sanitize_hex_color( $data['accent_color'] ?? '#c9a227' ),
            'modules'             => array_map( 'sanitize_text_field', $data['modules'] ?? [] ),
        ];
    }
}
