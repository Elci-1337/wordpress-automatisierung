<?php
/**
 * E-E-A-T Generator – Über-uns-Seite, Autorenprofile, Trust-Signals, Redaktionsrichtlinien
 * Erstellt alle für E-E-A-T (Experience, Expertise, Authoritativeness, Trustworthiness) nötigen Inhalte.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Flavor_EEAT_Generator {

    /**
     * Run E-E-A-T setup
     */
    public function setup( array $config ): array {
        $results = [];

        // 1. Create "Über uns" page
        $results['about'] = $this->create_about_page( $config );

        // 2. Create editorial guidelines page
        $results['editorial'] = $this->create_editorial_page( $config );

        // 3. Setup author profile
        $results['author'] = $this->setup_author_profile( $config );

        return [ 'status' => 'ok', 'message' => 'E-E-A-T Seiten erstellt', 'details' => $results ];
    }

    /**
     * Create a comprehensive "Über uns" page
     */
    private function create_about_page( array $config ): string {
        $name    = $config['blog_name'] ?? '';
        $owner   = $config['owner_name'] ?? '';
        $email   = $config['owner_email'] ?? '';
        $author  = $config['author_name'] ?? $owner;
        $bio     = $config['author_bio'] ?? '';
        $expert  = $config['author_expertise'] ?? '';
        $topic_labels = $this->get_topic_labels();
        $topic   = $topic_labels[ $config['blog_topic'] ?? '' ] ?? ( $config['blog_topic'] ?? '' );

        $content = <<<HTML
<!-- wp:heading {"level":1} -->
<h1>Über {$name}</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"lead"} -->
<p class="lead"><strong>{$name}</strong> ist ein unabhängiges Magazin rund um das Thema <strong>{$topic}</strong>. Wir liefern fundierte Artikel, aktuelle Analysen und praxisnahe Ratgeber – geschrieben von Experten für eine informierte Community.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Unsere Mission</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Wir glauben, dass qualitativ hochwertiger Journalismus und Fachexpertise den Unterschied machen. Bei {$name} setzen wir auf:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Fundierte Recherche:</strong> Jeder Artikel basiert auf gründlicher Recherche und verifizierten Quellen.</li>
<li><strong>Expertenwissen:</strong> Unsere Autoren verfügen über nachgewiesene Fachkenntnisse in ihren Bereichen.</li>
<li><strong>Aktualität:</strong> Wir halten unsere Inhalte regelmäßig auf dem neuesten Stand.</li>
<li><strong>Transparenz:</strong> Wir kennzeichnen gesponserte Inhalte und legen Interessenkonflikte offen.</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Unser Team</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3>{$author} – Chefredakteur</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>{$bio}</p>
<!-- /wp:paragraph -->

HTML;

        if ( ! empty( $expert ) ) {
            $content .= <<<HTML
<!-- wp:paragraph -->
<p><strong>Qualifikationen:</strong> {$expert}</p>
<!-- /wp:paragraph -->

HTML;
        }

        $content .= <<<HTML
<!-- wp:heading -->
<h2>Redaktionelle Standards</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Alle Inhalte auf {$name} durchlaufen einen mehrstufigen Qualitätsprozess:</p>
<!-- /wp:paragraph -->

<!-- wp:list {"ordered":true} -->
<ol>
<li><strong>Recherche & Faktencheck:</strong> Mehrere unabhängige Quellen werden geprüft.</li>
<li><strong>Fachliche Prüfung:</strong> Experten überprüfen technische Genauigkeit.</li>
<li><strong>Redaktionelle Qualitätssicherung:</strong> Lektorat und Stilprüfung.</li>
<li><strong>Regelmäßige Aktualisierung:</strong> Ältere Artikel werden überprüft und aktualisiert.</li>
</ol>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Kontakt</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Hast du Fragen, Anregungen oder Feedback? Schreib uns gerne:</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>E-Mail:</strong> <a href="mailto:{$email}">{$email}</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Betreiber:</strong> {$owner}</p>
<!-- /wp:paragraph -->
HTML;

        return $this->create_or_update_page( 'Über uns', $content, 'ueber-uns' );
    }

    /**
     * Create editorial guidelines page (trust signal)
     */
    private function create_editorial_page( array $config ): string {
        $name = $config['blog_name'] ?? '';

        $content = <<<HTML
<!-- wp:heading {"level":1} -->
<h1>Redaktionsrichtlinien</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"lead"} -->
<p class="lead">Transparenz und Qualität stehen bei {$name} an erster Stelle. Hier erklären wir unsere redaktionellen Grundsätze.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Quellenarbeit & Faktencheck</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Alle Behauptungen und Statistiken in unseren Artikeln werden durch mindestens eine verlässliche Quelle gestützt. Wir bevorzugen primäre Quellen wie wissenschaftliche Studien, offizielle Berichte und Experteninterviews.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Autorenqualifikation</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Unsere Autoren verfügen über nachgewiesene Expertise in ihren jeweiligen Fachgebieten. Jeder Autor hat ein detailliertes Profil mit Qualifikationen, Erfahrung und Veröffentlichungen.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Unabhängigkeit & Transparenz</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Redaktionelle Inhalte sind vollständig unabhängig von kommerziellen Interessen. Gesponserte Inhalte und Affiliate-Links werden immer klar gekennzeichnet.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Korrekturen & Aktualisierungen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Wenn uns Fehler auffallen oder gemeldet werden, korrigieren wir diese zeitnah und transparent. Wesentliche Änderungen werden am Artikel vermerkt. Alle Artikel enthalten ein "Zuletzt aktualisiert"-Datum.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Kontakt zur Redaktion</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Feedback, Korrekturhinweise oder Fragen zur Berichterstattung richten Sie bitte an unsere Redaktion: <a href="mailto:{$config['owner_email']}">{$config['owner_email']}</a></p>
<!-- /wp:paragraph -->
HTML;

        return $this->create_or_update_page( 'Redaktionsrichtlinien', $content, 'redaktionsrichtlinien' );
    }

    /**
     * Setup main author user profile
     */
    private function setup_author_profile( array $config ): string {
        if ( empty( $config['author_name'] ) ) {
            return 'Kein Autor angegeben – übersprungen';
        }

        // Update the current admin user profile
        $current_user_id = get_current_user_id();
        if ( ! $current_user_id ) {
            return 'Kein User eingeloggt';
        }

        $name_parts = explode( ' ', $config['author_name'], 2 );
        $first_name = $name_parts[0] ?? '';
        $last_name  = $name_parts[1] ?? '';

        wp_update_user( [
            'ID'           => $current_user_id,
            'display_name' => $config['author_name'],
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'description'  => $config['author_bio'] ?? '',
        ] );

        // Store expertise as user meta
        if ( ! empty( $config['author_expertise'] ) ) {
            update_user_meta( $current_user_id, 'flavor_expertise', $config['author_expertise'] );
        }

        return 'Autorenprofil aktualisiert';
    }

    /**
     * Create or update a page by slug
     */
    private function create_or_update_page( string $title, string $content, string $slug ): string {
        $existing = get_page_by_path( $slug );

        if ( $existing ) {
            wp_update_post( [
                'ID'           => $existing->ID,
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => 'publish',
            ] );
            return "Seite '{$title}' aktualisiert (ID: {$existing->ID})";
        }

        $page_id = wp_insert_post( [
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id(),
        ] );

        if ( is_wp_error( $page_id ) ) {
            return "Fehler bei '{$title}': " . $page_id->get_error_message();
        }

        return "Seite '{$title}' erstellt (ID: {$page_id})";
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
