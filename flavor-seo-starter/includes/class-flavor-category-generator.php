<?php
/**
 * Category & Menu Generator – Erstellt themenspezifische Kategorien und Navigationsmenüs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Flavor_Category_Generator {

    /**
     * Run category & menu setup
     */
    public function setup( array $config ): array {
        $topic = $config['blog_topic'] ?? 'technology';

        // Create categories
        $categories = $this->get_categories_for_topic( $topic );
        $cat_ids    = $this->create_categories( $categories );

        // Remove default "Uncategorized" or rename it
        $this->cleanup_default_category( $categories );

        // Create navigation menus
        $this->create_menus( $config, $cat_ids );

        return [
            'status'     => 'ok',
            'message'    => count( $cat_ids ) . ' Kategorien und Menüs erstellt',
            'categories' => array_keys( $categories ),
        ];
    }

    /**
     * Get topic-specific category definitions
     */
    private function get_categories_for_topic( string $topic ): array {
        $topic_categories = [
            'technology' => [
                'Software & Apps'      => 'Neueste Software-Reviews, App-Tests und digitale Tools im Überblick.',
                'Hardware & Gadgets'   => 'Testberichte und News zu Smartphones, Laptops und Tech-Gadgets.',
                'KI & Zukunft'         => 'Künstliche Intelligenz, Machine Learning und Zukunftstechnologien.',
                'Tutorials & Guides'   => 'Schritt-für-Schritt-Anleitungen und Praxis-Tipps.',
                'News & Trends'        => 'Aktuelle Nachrichten und Entwicklungen aus der Tech-Welt.',
                'Cybersecurity'        => 'IT-Sicherheit, Datenschutz und Schutz vor digitalen Bedrohungen.',
            ],
            'health' => [
                'Ernährung'            => 'Gesunde Ernährung, Diäten und Nahrungsergänzung.',
                'Fitness & Training'   => 'Workouts, Trainingspläne und Sportmedizin.',
                'Mental Health'        => 'Psychische Gesundheit, Achtsamkeit und Stressbewältigung.',
                'Naturheilkunde'       => 'Pflanzenheilkunde, Homöopathie und alternative Medizin.',
                'Prävention'           => 'Vorsorge, Gesundheits-Checks und Krankheitsprävention.',
                'News & Studien'       => 'Aktuelle Gesundheitsnachrichten und wissenschaftliche Studien.',
            ],
            'finance' => [
                'Investieren'          => 'Aktien, ETFs, Kryptowährungen und Anlagestrategien.',
                'Sparen & Budget'      => 'Spartipps, Budgetplanung und Finanzmanagement.',
                'Immobilien'           => 'Immobilieninvestment, Hausbau und Finanzierung.',
                'Steuern & Recht'      => 'Steuertipps, rechtliche Grundlagen und Finanzregulierung.',
                'Unternehmertum'       => 'Gründung, Geschäftsmodelle und Business-Strategien.',
                'Marktanalysen'        => 'Wirtschaftsnachrichten und Marktentwicklungen.',
            ],
            'lifestyle' => [
                'Mode & Style'         => 'Fashion-Trends, Outfits und Styling-Tipps.',
                'Beauty & Pflege'      => 'Hautpflege, Make-up und Wellness-Routinen.',
                'Wohnen & Einrichten'  => 'Interior Design, Deko-Ideen und Wohntrends.',
                'Beziehungen'          => 'Partnerschaft, Familie und soziale Beziehungen.',
                'Karriere & Erfolg'    => 'Berufliche Entwicklung, Produktivität und Work-Life-Balance.',
                'Kultur & Events'      => 'Kulturelle Highlights, Events und Freizeitgestaltung.',
            ],
            'food' => [
                'Rezepte'              => 'Leckere Rezepte für jeden Anlass und Geschmack.',
                'Kochtipps & Techniken' => 'Küchentechniken, Kochtipps und Küchengeräte.',
                'Ernährungsformen'     => 'Vegane, vegetarische und spezielle Ernährungsweisen.',
                'Backen'               => 'Backrezepte für Kuchen, Brot, Torten und Gebäck.',
                'Getränke'             => 'Cocktails, Kaffee, Tee und Smoothie-Rezepte.',
                'Restaurant-Tipps'     => 'Restaurant-Reviews und kulinarische Empfehlungen.',
            ],
            'travel' => [
                'Reiseziele'           => 'Inspirationen und Guides für Reiseziele weltweit.',
                'Abenteuer & Outdoor'  => 'Wandern, Trekking und Outdoor-Abenteuer.',
                'Städtetrips'          => 'City Guides, Insider-Tipps und Wochenendtrips.',
                'Budget-Reisen'        => 'Günstig reisen, Spartipps und Backpacking.',
                'Reise-Ausrüstung'     => 'Packlisten, Reise-Gadgets und Equipment-Tests.',
                'Kultur & Geschichte'  => 'Kulturelle Entdeckungen und historische Sehenswürdigkeiten.',
            ],
            'sports' => [
                'Training & Workouts'  => 'Trainingspläne, Übungen und Workout-Routinen.',
                'Sportarten'           => 'Fußball, Basketball, Tennis und weitere Sportarten.',
                'Ernährung für Sportler' => 'Sportnahrung, Supplements und Meal-Prep.',
                'Sportmedizin'         => 'Verletzungsprävention, Regeneration und Physiotherapie.',
                'Events & Wettkämpfe'  => 'Sportereignisse, Meisterschaften und Live-Berichte.',
                'Equipment & Tests'    => 'Sportausrüstung, Schuh-Tests und Gear-Reviews.',
            ],
            'education' => [
                'Lerntechniken'        => 'Effektive Lernmethoden, Gedächtnistechniken und Produktivität.',
                'Online-Kurse'         => 'Reviews und Empfehlungen zu Online-Lernplattformen.',
                'Wissenschaft'         => 'Spannende wissenschaftliche Erkenntnisse und Forschung.',
                'Sprachen lernen'      => 'Tipps und Ressourcen zum Sprachenlernen.',
                'Karriere & Studium'   => 'Studienratgeber, Bewerbungstipps und Karriereplanung.',
                'Bücher & Medien'      => 'Buchempfehlungen, Rezensionen und Lesetipps.',
            ],
            'marketing' => [
                'SEO & Content'        => 'Suchmaschinenoptimierung, Content-Marketing und Keywords.',
                'Social Media'         => 'Social-Media-Strategien, Plattform-Updates und Best Practices.',
                'E-Mail Marketing'     => 'Newsletter, Automatisierung und E-Mail-Kampagnen.',
                'Analytics & Daten'    => 'Web-Analytics, Tracking und datengetriebenes Marketing.',
                'Branding & Design'    => 'Markenaufbau, Corporate Design und visuelle Kommunikation.',
                'Tools & Software'     => 'Marketing-Tools, Software-Reviews und Vergleiche.',
            ],
            'auto' => [
                'E-Mobilität'          => 'Elektroautos, Ladeinfrastruktur und Elektromobilität.',
                'Tests & Reviews'      => 'Autotests, Fahrberichte und Vergleiche.',
                'Tuning & Pflege'      => 'Auto-Tuning, Pflege-Tipps und DIY-Reparaturen.',
                'Motorrad'             => 'Motorrad-News, Tests und Touren.',
                'Zukunft der Mobilität' => 'Autonomes Fahren, Carsharing und Mobilitätskonzepte.',
                'News & Markt'         => 'Auto-Nachrichten, Neuvorstellungen und Marktanalysen.',
            ],
            'gaming' => [
                'Game Reviews'         => 'Testberichte und Bewertungen aktueller Spiele.',
                'News & Releases'      => 'Gaming-Nachrichten, Release-Termine und Ankündigungen.',
                'Guides & Tipps'       => 'Spielanleitungen, Walkthroughs und Profi-Tipps.',
                'Hardware'             => 'Gaming-PCs, Konsolen, Monitore und Peripherie.',
                'Esports'              => 'Esport-Turniere, Teams und Ergebnisse.',
                'Indie & Retro'        => 'Indie-Games, Retro-Klassiker und Hidden Gems.',
            ],
            'diy' => [
                'Heimwerken'           => 'Heimwerker-Projekte, Renovierung und Reparaturen.',
                'Garten & Outdoor'     => 'Gartenpflege, Balkongestaltung und Outdoor-Projekte.',
                'Upcycling & Kreativ'  => 'Kreative Upcycling-Ideen und Bastelprojekte.',
                'Werkzeug & Material'  => 'Werkzeug-Tests, Materialkunde und Kaufberatung.',
                'Anleitungen'          => 'Schritt-für-Schritt-Anleitungen für alle Schwierigkeitsstufen.',
                'Smart Home'           => 'Heimautomatisierung, IoT-Projekte und Smart-Home-Technik.',
            ],
        ];

        return $topic_categories[ $topic ] ?? $topic_categories['technology'];
    }

    /**
     * Create categories from definitions
     */
    private function create_categories( array $categories ): array {
        $cat_ids = [];

        foreach ( $categories as $name => $description ) {
            $existing = get_term_by( 'name', $name, 'category' );
            if ( $existing ) {
                $cat_ids[ $name ] = $existing->term_id;
                wp_update_term( $existing->term_id, 'category', [
                    'description' => $description,
                ] );
                continue;
            }

            $result = wp_insert_term( $name, 'category', [
                'description' => $description,
                'slug'        => sanitize_title( $name ),
            ] );

            if ( ! is_wp_error( $result ) ) {
                $cat_ids[ $name ] = $result['term_id'];
            }
        }

        return $cat_ids;
    }

    /**
     * Remove or rename the default "Uncategorized" category
     */
    private function cleanup_default_category( array $new_categories ): void {
        $default_cat_id = (int) get_option( 'default_category' );
        $default_cat    = get_category( $default_cat_id );

        if ( $default_cat && in_array( $default_cat->name, [ 'Uncategorized', 'Allgemein' ], true ) ) {
            // Set first new category as default
            $first_cat_name = array_key_first( $new_categories );
            $first_cat      = get_term_by( 'name', $first_cat_name, 'category' );
            if ( $first_cat ) {
                update_option( 'default_category', $first_cat->term_id );
            }

            // Delete old default category
            wp_delete_term( $default_cat_id, 'category' );
        }
    }

    /**
     * Create WordPress navigation menus
     */
    private function create_menus( array $config, array $cat_ids ): void {
        // Primary Navigation
        $menu_name = $config['blog_name'] . ' – Hauptmenü';
        $menu_exists = wp_get_nav_menu_object( $menu_name );
        $menu_id = $menu_exists ? $menu_exists->term_id : wp_create_nav_menu( $menu_name );

        if ( is_wp_error( $menu_id ) ) {
            return;
        }

        // Clear existing menu items
        $menu_items = wp_get_nav_menu_items( $menu_id );
        if ( $menu_items ) {
            foreach ( $menu_items as $item ) {
                wp_delete_post( $item->ID, true );
            }
        }

        // Add Home link
        wp_update_nav_menu_item( $menu_id, 0, [
            'menu-item-title'  => __( 'Startseite', 'flavor-seo-starter' ),
            'menu-item-url'    => home_url( '/' ),
            'menu-item-status' => 'publish',
            'menu-item-type'   => 'custom',
            'menu-item-position' => 1,
        ] );

        // Add category links
        $position = 2;
        $max_items = 6; // Keep nav clean
        $count = 0;
        foreach ( $cat_ids as $name => $cat_id ) {
            if ( $count >= $max_items ) {
                break;
            }
            wp_update_nav_menu_item( $menu_id, 0, [
                'menu-item-title'     => $name,
                'menu-item-object'    => 'category',
                'menu-item-object-id' => $cat_id,
                'menu-item-type'      => 'taxonomy',
                'menu-item-status'    => 'publish',
                'menu-item-position'  => $position++,
            ] );
            $count++;
        }

        // Assign to theme locations
        $locations = get_theme_mod( 'nav_menu_locations', [] );
        $registered = get_registered_nav_menus();

        // Try common location names
        $primary_locations = [ 'primary', 'main', 'main-menu', 'header', 'top', 'primary-menu' ];
        foreach ( $primary_locations as $loc ) {
            if ( isset( $registered[ $loc ] ) ) {
                $locations[ $loc ] = $menu_id;
                break;
            }
        }
        set_theme_mod( 'nav_menu_locations', $locations );

        // Footer Menu
        $footer_menu_name = $config['blog_name'] . ' – Footer';
        $footer_menu_exists = wp_get_nav_menu_object( $footer_menu_name );
        $footer_menu_id = $footer_menu_exists ? $footer_menu_exists->term_id : wp_create_nav_menu( $footer_menu_name );

        if ( ! is_wp_error( $footer_menu_id ) ) {
            $footer_items = wp_get_nav_menu_items( $footer_menu_id );
            if ( $footer_items ) {
                foreach ( $footer_items as $item ) {
                    wp_delete_post( $item->ID, true );
                }
            }

            $footer_pages = [
                'Über uns'                => '/ueber-uns/',
                'Redaktionsrichtlinien'   => '/redaktionsrichtlinien/',
                'Impressum'               => '/impressum/',
                'Datenschutz'             => '/datenschutz/',
            ];

            $pos = 1;
            foreach ( $footer_pages as $label => $path ) {
                $page = get_page_by_path( trim( $path, '/' ) );
                if ( $page ) {
                    wp_update_nav_menu_item( $footer_menu_id, 0, [
                        'menu-item-title'     => $label,
                        'menu-item-object'    => 'page',
                        'menu-item-object-id' => $page->ID,
                        'menu-item-type'      => 'post_type',
                        'menu-item-status'    => 'publish',
                        'menu-item-position'  => $pos++,
                    ] );
                }
            }

            // Assign footer menu
            $footer_locations = [ 'footer', 'footer-menu', 'secondary', 'social' ];
            $locations = get_theme_mod( 'nav_menu_locations', [] );
            foreach ( $footer_locations as $loc ) {
                if ( isset( $registered[ $loc ] ) ) {
                    $locations[ $loc ] = $footer_menu_id;
                    break;
                }
            }
            set_theme_mod( 'nav_menu_locations', $locations );
        }
    }
}
