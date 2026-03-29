<?php
/**
 * AI Generator – KI-gestützte Inhaltsgenerierung für alle Plugin-Felder
 * Unterstützt OpenAI API (und kompatible Endpoints wie LocalAI, Ollama, etc.)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Flavor_AI_Generator {

    public function __construct() {
        add_action( 'wp_ajax_flavor_ai_generate', [ $this, 'handle_generate' ] );
    }

    /**
     * Handle AJAX AI generation request
     */
    public function handle_generate(): void {
        check_ajax_referer( 'flavor_seo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Keine Berechtigung.' );
        }

        $field      = sanitize_text_field( $_POST['field'] ?? '' );
        $context    = $this->sanitize_context( $_POST['context'] ?? [] );
        $api_key    = sanitize_text_field( $_POST['api_key'] ?? '' );
        $api_url    = esc_url_raw( $_POST['api_url'] ?? 'https://api.openai.com/v1/chat/completions' );
        $model      = sanitize_text_field( $_POST['model'] ?? 'gpt-4o-mini' );

        if ( empty( $api_key ) ) {
            // Try saved key
            $api_key = get_option( 'flavor_ai_api_key', '' );
        }

        if ( empty( $api_key ) ) {
            wp_send_json_error( 'Bitte gib zuerst einen API-Key ein.' );
        }

        // Save key for future use
        update_option( 'flavor_ai_api_key', $api_key );
        update_option( 'flavor_ai_api_url', $api_url );
        update_option( 'flavor_ai_model', $model );

        $prompt = $this->build_prompt( $field, $context );

        if ( empty( $prompt ) ) {
            wp_send_json_error( 'Unbekanntes Feld: ' . $field );
        }

        $result = $this->call_api( $api_key, $api_url, $model, $prompt );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( [ 'content' => $result ] );
    }

    /**
     * Build the AI prompt based on the field and context
     */
    private function build_prompt( string $field, array $context ): string {
        $blog_name = $context['blog_name'] ?? '';
        $topic     = $context['blog_topic'] ?? '';
        $topic_label = $this->get_topic_label( $topic );
        $language  = $this->get_language_label( $context['blog_language'] ?? 'de_DE' );
        $custom    = $context['custom_instructions'] ?? '';

        $base_context = "Blog/Magazin: \"{$blog_name}\", Thema: {$topic_label}, Sprache: {$language}.";
        if ( ! empty( $custom ) ) {
            $base_context .= " Zusätzliche Vorgaben: {$custom}";
        }

        $prompts = [
            'blog_tagline' => [
                'system' => 'Du bist ein erfahrener Marketing-Texter und SEO-Experte. Antworte NUR mit dem Tagline-Text, ohne Anführungszeichen, ohne Erklärung.',
                'user'   => "Erstelle einen kurzen, einprägsamen Tagline/Untertitel (max. 60 Zeichen) für folgendes Projekt:\n{$base_context}\n\nDer Tagline soll professionell, merkfähig und SEO-freundlich sein.",
            ],
            'author_name' => [
                'system' => 'Du bist ein Branding-Experte. Antworte NUR mit dem Namen, ohne Erklärung.',
                'user'   => "Schlage einen glaubwürdigen, professionell klingenden Autorennamen (Vor- und Nachname) vor, der als Chefredakteur für folgendes Magazin passt:\n{$base_context}\n\nDer Name soll authentisch und vertrauenswürdig wirken. Optional mit akademischem Titel wenn es zum Thema passt.",
            ],
            'author_bio' => [
                'system' => 'Du bist ein E-E-A-T und Personal-Branding-Experte. Antworte NUR mit der Bio, ohne Erklärung.',
                'user'   => "Schreibe eine überzeugende Kurz-Bio (2-3 Sätze) für den Chefredakteur eines Magazins.\n{$base_context}\nAutorenname: {$context['author_name']}\n\nDie Bio soll Erfahrung, Expertise und Leidenschaft für das Thema zeigen. Sie dient als E-E-A-T-Signal für Google.",
            ],
            'author_expertise' => [
                'system' => 'Du bist ein E-E-A-T-Spezialist. Antworte NUR mit den Qualifikationen, kommagetrennt, ohne Erklärung.',
                'user'   => "Liste 3-5 passende Qualifikationen/Expertise-Punkte auf (kommagetrennt) für den Chefredakteur eines Magazins.\n{$base_context}\nAutorenname: {$context['author_name']}\n\nBeispiel-Format: M.Sc. Informatik, Google-zertifiziert, 15 Jahre Branchenerfahrung",
            ],
            'owner_name' => [
                'system' => 'Du bist ein Unternehmensberater. Antworte NUR mit dem Firmennamen, ohne Erklärung.',
                'user'   => "Schlage einen professionellen Betreiber-/Firmennamen vor für folgendes Online-Magazin:\n{$base_context}\n\nDer Name soll seriös klingen und kann eine GmbH, UG oder Einzelunternehmen sein.",
            ],
            'custom_instructions' => [
                'system' => 'Du bist ein Digital-Stratege und Magazin-Konzepter. Antworte mit konkreten, stichpunktartigen Vorgaben.',
                'user'   => "Erstelle sinnvolle Design- und Content-Vorgaben für folgendes Magazin-Projekt:\n{$base_context}\n\nGib 4-6 konkrete Empfehlungen zu: Zielgruppe, Tonalität, Farbstimmung, Content-Fokus, USP. Format: Stichpunkte, je eine Zeile.",
            ],
            'meta_description' => [
                'system' => 'Du bist ein SEO-Experte. Antworte NUR mit der Meta-Description, ohne Erklärung.',
                'user'   => "Schreibe eine SEO-optimierte Meta-Description (max. 155 Zeichen) für die Startseite von:\n{$base_context}\n\nSie soll das Hauptkeyword enthalten und zum Klicken animieren.",
            ],
            'about_intro' => [
                'system' => 'Du bist ein Texter für Über-uns-Seiten mit E-E-A-T-Fokus. Antworte NUR mit dem Text.',
                'user'   => "Schreibe einen überzeugenden Einleitungsabsatz (3-4 Sätze) für die Über-uns-Seite von:\n{$base_context}\nBetreiber: {$context['owner_name']}\n\nDer Text soll Vertrauen aufbauen, die Mission beschreiben und E-E-A-T-Signale enthalten.",
            ],
            'impressum_extra' => [
                'system' => 'Du bist ein Rechtstext-Assistent. Antworte NUR mit dem Text.',
                'user'   => "Erstelle einen kurzen einleitenden Satz für das Impressum von:\n{$base_context}\nBetreiber: {$context['owner_name']}\n\nNur 1-2 Sätze, die das Unternehmen/Projekt kurz vorstellen.",
            ],
            'category_descriptions' => [
                'system' => 'Du bist ein SEO-Content-Stratege. Antworte mit einer JSON-Liste von Objekten: [{"name":"Kategorie","description":"SEO-Beschreibung"}]',
                'user'   => "Erstelle 6 passende Blog-Kategorien mit SEO-optimierten Beschreibungen (je 1 Satz, max. 120 Zeichen) für:\n{$base_context}\n\nFormat: JSON-Array mit name und description.",
            ],
        ];

        if ( ! isset( $prompts[ $field ] ) ) {
            return '';
        }

        return wp_json_encode( $prompts[ $field ] );
    }

    /**
     * Call the AI API
     */
    private function call_api( string $api_key, string $api_url, string $model, string $prompt_json ): string|\WP_Error {
        $prompt = json_decode( $prompt_json, true );

        $body = [
            'model'       => $model,
            'messages'    => [
                [ 'role' => 'system', 'content' => $prompt['system'] ],
                [ 'role' => 'user', 'content' => $prompt['user'] ],
            ],
            'max_tokens'  => 500,
            'temperature' => 0.7,
        ];

        $response = wp_remote_post( $api_url, [
            'timeout' => 30,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body' => wp_json_encode( $body ),
        ] );

        if ( is_wp_error( $response ) ) {
            return new \WP_Error( 'api_error', 'API-Verbindungsfehler: ' . $response->get_error_message() );
        }

        $status = wp_remote_retrieve_response_code( $response );
        $body   = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status !== 200 ) {
            $error_msg = $body['error']['message'] ?? "HTTP {$status}";
            return new \WP_Error( 'api_error', 'API-Fehler: ' . $error_msg );
        }

        $content = $body['choices'][0]['message']['content'] ?? '';
        $content = trim( $content, " \t\n\r\0\x0B\"'" );

        return $content;
    }

    /**
     * Sanitize context array
     */
    private function sanitize_context( $context ): array {
        if ( ! is_array( $context ) ) {
            return [];
        }
        return array_map( function ( $val ) {
            return is_string( $val ) ? sanitize_text_field( $val ) : '';
        }, $context );
    }

    /**
     * Get human-readable topic label
     */
    private function get_topic_label( string $topic ): string {
        $labels = [
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
        return $labels[ $topic ] ?? $topic;
    }

    /**
     * Get language label
     */
    private function get_language_label( string $locale ): string {
        $map = [
            'de_DE' => 'Deutsch (Deutschland)',
            'de_AT' => 'Deutsch (Österreich)',
            'de_CH' => 'Deutsch (Schweiz)',
            'en_US' => 'Englisch (US)',
            'en_GB' => 'Englisch (UK)',
        ];
        return $map[ $locale ] ?? $locale;
    }
}
