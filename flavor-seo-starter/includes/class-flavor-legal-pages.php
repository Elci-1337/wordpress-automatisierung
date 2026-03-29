<?php
/**
 * Legal Pages Generator – Impressum, Datenschutzerklärung, Cookie-Hinweis
 * DSGVO-konforme rechtliche Seiten automatisch erstellt.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Flavor_Legal_Pages {

    /**
     * Run legal pages setup
     */
    public function setup( array $config ): array {
        $results = [];

        $results['impressum']    = $this->create_impressum( $config );
        $results['datenschutz']  = $this->create_datenschutz( $config );
        $results['cookie']       = $this->setup_cookie_notice( $config );

        return [ 'status' => 'ok', 'message' => 'Rechtliche Seiten erstellt', 'details' => $results ];
    }

    /**
     * Create Impressum page
     */
    private function create_impressum( array $config ): string {
        $owner   = esc_html( $config['owner_name'] ?? '' );
        $street  = esc_html( $config['owner_street'] ?? '' );
        $zip_city = esc_html( $config['owner_zip_city'] ?? '' );
        $country = esc_html( $config['owner_country'] ?? 'Deutschland' );
        $email   = esc_html( $config['owner_email'] ?? '' );
        $phone   = esc_html( $config['owner_phone'] ?? '' );
        $blog    = esc_html( $config['blog_name'] ?? '' );

        $content = <<<HTML
<!-- wp:heading {"level":1} -->
<h1>Impressum</h1>
<!-- /wp:heading -->

<!-- wp:heading -->
<h2>Angaben gemäß § 5 TMG</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>{$owner}</strong></p>
<!-- /wp:paragraph -->

HTML;

        if ( ! empty( $street ) ) {
            $content .= <<<HTML
<!-- wp:paragraph -->
<p>{$street}<br>{$zip_city}<br>{$country}</p>
<!-- /wp:paragraph -->

HTML;
        }

        $content .= <<<HTML
<!-- wp:heading -->
<h2>Kontakt</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>E-Mail: <a href="mailto:{$email}">{$email}</a></p>
<!-- /wp:paragraph -->

HTML;

        if ( ! empty( $phone ) ) {
            $content .= <<<HTML
<!-- wp:paragraph -->
<p>Telefon: {$phone}</p>
<!-- /wp:paragraph -->

HTML;
        }

        $content .= <<<HTML
<!-- wp:heading -->
<h2>Redaktionell verantwortlich</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>{$owner}</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>EU-Streitschlichtung</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit: <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener">https://ec.europa.eu/consumers/odr/</a>. Unsere E-Mail-Adresse finden Sie oben im Impressum.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Verbraucherstreitbeilegung / Universalschlichtungsstelle</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Haftung für Inhalte</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Haftung für Links</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Unser Angebot enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Urheberrecht</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers.</p>
<!-- /wp:paragraph -->
HTML;

        return $this->create_or_update_page( 'Impressum', $content, 'impressum' );
    }

    /**
     * Create Datenschutzerklärung page
     */
    private function create_datenschutz( array $config ): string {
        $owner = esc_html( $config['owner_name'] ?? '' );
        $email = esc_html( $config['owner_email'] ?? '' );
        $blog  = esc_html( $config['blog_name'] ?? '' );
        $street = esc_html( $config['owner_street'] ?? '' );
        $zip_city = esc_html( $config['owner_zip_city'] ?? '' );

        $content = <<<HTML
<!-- wp:heading {"level":1} -->
<h1>Datenschutzerklärung</h1>
<!-- /wp:heading -->

<!-- wp:heading -->
<h2>1. Datenschutz auf einen Blick</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3>Allgemeine Hinweise</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Die folgenden Hinweise geben einen einfachen Überblick darüber, was mit Ihren personenbezogenen Daten passiert, wenn Sie diese Website besuchen. Personenbezogene Daten sind alle Daten, mit denen Sie persönlich identifiziert werden können.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Datenerfassung auf dieser Website</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>Wer ist verantwortlich für die Datenerfassung auf dieser Website?</strong></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Die Datenverarbeitung auf dieser Website erfolgt durch den Websitebetreiber: {$owner}, {$street}, {$zip_city}. E-Mail: {$email}</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>2. Hosting</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Wir hosten die Inhalte unserer Website bei einem externen Dienstleister (Hoster). Die personenbezogenen Daten, die auf dieser Website erfasst werden, werden auf den Servern des Hosters gespeichert. Hierbei kann es sich v.a. um IP-Adressen, Kontaktanfragen, Meta- und Kommunikationsdaten, Vertragsdaten, Kontaktdaten, Namen, Websitezugriffe und sonstige Daten, die über eine Website generiert werden, handeln.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>3. Allgemeine Hinweise und Pflichtinformationen</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3>Datenschutz</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Die Betreiber dieser Seiten nehmen den Schutz Ihrer persönlichen Daten sehr ernst. Wir behandeln Ihre personenbezogenen Daten vertraulich und entsprechend den gesetzlichen Datenschutzvorschriften sowie dieser Datenschutzerklärung.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Hinweis zur verantwortlichen Stelle</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Die verantwortliche Stelle für die Datenverarbeitung auf dieser Website ist:</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>{$owner}<br>{$street}<br>{$zip_city}<br>E-Mail: {$email}</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>4. Datenerfassung auf dieser Website</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3>Cookies</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Unsere Internetseiten verwenden so genannte „Cookies". Cookies sind kleine Datenpakete und richten auf Ihrem Endgerät keinen Schaden an. Sie werden entweder vorübergehend für die Dauer einer Sitzung (Session-Cookies) oder dauerhaft (permanente Cookies) auf Ihrem Endgerät gespeichert.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Sie können Ihren Browser so einstellen, dass Sie über das Setzen von Cookies informiert werden und Cookies nur im Einzelfall erlauben, die Annahme von Cookies für bestimmte Fälle oder generell ausschließen sowie das automatische Löschen der Cookies beim Schließen des Browsers aktivieren.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Server-Log-Dateien</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Der Provider der Seiten erhebt und speichert automatisch Informationen in so genannten Server-Log-Dateien, die Ihr Browser automatisch an uns übermittelt. Dies sind: Browsertyp und Browserversion, verwendetes Betriebssystem, Referrer URL, Hostname des zugreifenden Rechners, Uhrzeit der Serveranfrage, IP-Adresse.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Eine Zusammenführung dieser Daten mit anderen Datenquellen wird nicht vorgenommen. Die Erfassung dieser Daten erfolgt auf Grundlage von Art. 6 Abs. 1 lit. f DSGVO.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Kommentarfunktion</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Wenn Nutzer Kommentare auf dieser Website hinterlassen, werden neben dem Kommentar auch Angaben zum Zeitpunkt der Erstellung des Kommentars, die E-Mail-Adresse und der gewählte Nutzername gespeichert.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>5. Ihre Rechte</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Sie haben jederzeit das Recht, unentgeltlich Auskunft über Herkunft, Empfänger und Zweck Ihrer gespeicherten personenbezogenen Daten zu erhalten. Sie haben außerdem ein Recht, die Berichtigung oder Löschung dieser Daten zu verlangen. Wenn Sie eine Einwilligung zur Datenverarbeitung erteilt haben, können Sie diese Einwilligung jederzeit für die Zukunft widerrufen. Hierzu sowie zu weiteren Fragen zum Thema Datenschutz können Sie sich jederzeit an uns wenden.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><em>Stand: {$this->get_current_date()}</em></p>
<!-- /wp:paragraph -->
HTML;

        // Set as WP privacy policy page
        $page_result = $this->create_or_update_page( 'Datenschutzerklärung', $content, 'datenschutz' );

        $privacy_page = get_page_by_path( 'datenschutz' );
        if ( $privacy_page ) {
            update_option( 'wp_page_for_privacy_policy', $privacy_page->ID );
        }

        return $page_result;
    }

    /**
     * Setup cookie notice via wp_footer
     */
    private function setup_cookie_notice( array $config ): string {
        update_option( 'flavor_cookie_notice_enabled', true );
        add_action( 'wp_footer', [ $this, 'render_cookie_banner' ] );
        return 'Cookie-Hinweis aktiviert';
    }

    /**
     * Render DSGVO cookie banner
     */
    public static function render_cookie_banner(): void {
        if ( ! get_option( 'flavor_cookie_notice_enabled' ) ) {
            return;
        }

        $privacy_page_id = get_option( 'wp_page_for_privacy_policy' );
        $privacy_url     = $privacy_page_id ? get_permalink( $privacy_page_id ) : home_url( '/datenschutz/' );
        ?>
        <div id="flavor-cookie-banner" style="display:none;">
            <div class="flavor-cookie-inner">
                <p>
                    <?php esc_html_e( 'Wir verwenden Cookies, um Ihnen die bestmögliche Erfahrung auf unserer Website zu bieten.', 'flavor-seo-starter' ); ?>
                    <a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Mehr erfahren', 'flavor-seo-starter' ); ?></a>
                </p>
                <div class="flavor-cookie-buttons">
                    <button id="flavor-cookie-accept" class="flavor-cookie-btn flavor-cookie-accept">
                        <?php esc_html_e( 'Akzeptieren', 'flavor-seo-starter' ); ?>
                    </button>
                    <button id="flavor-cookie-essential" class="flavor-cookie-btn flavor-cookie-essential">
                        <?php esc_html_e( 'Nur essenzielle Cookies', 'flavor-seo-starter' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <script>
        (function(){
            if(document.cookie.indexOf('flavor_cookies_accepted')>-1) return;
            var b=document.getElementById('flavor-cookie-banner');
            if(b) b.style.display='block';
            document.getElementById('flavor-cookie-accept').addEventListener('click',function(){
                document.cookie='flavor_cookies_accepted=all;path=/;max-age=31536000;SameSite=Lax';
                b.style.display='none';
            });
            document.getElementById('flavor-cookie-essential').addEventListener('click',function(){
                document.cookie='flavor_cookies_accepted=essential;path=/;max-age=31536000;SameSite=Lax';
                b.style.display='none';
            });
        })();
        </script>
        <?php
    }

    /**
     * Create or update page
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
            return "Seite '{$title}' aktualisiert";
        }

        $page_id = wp_insert_post( [
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id(),
        ] );

        return is_wp_error( $page_id )
            ? "Fehler: " . $page_id->get_error_message()
            : "Seite '{$title}' erstellt";
    }

    private function get_current_date(): string {
        return wp_date( 'd.m.Y' );
    }
}
