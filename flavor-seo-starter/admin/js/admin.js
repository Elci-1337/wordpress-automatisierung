/**
 * Flavor SEO Starter – Admin JavaScript
 */
(function ($) {
    'use strict';

    var $form = $('#flavor-setup-form');
    var $submit = $('#flavor-submit');
    var $progress = $('#flavor-progress');
    var $progressFill = $('#flavor-progress-fill');
    var $progressText = $('#flavor-progress-text');
    var $result = $('#flavor-result');

    var steps = [
        { progress: 10, text: 'Site Identity konfigurieren...' },
        { progress: 25, text: 'SEO-Engine einrichten...' },
        { progress: 40, text: 'LLM-Sichtbarkeit konfigurieren...' },
        { progress: 50, text: 'GEO-Optimierung anwenden...' },
        { progress: 65, text: 'E-E-A-T Seiten erstellen...' },
        { progress: 75, text: 'Rechtliche Seiten generieren...' },
        { progress: 85, text: 'Kategorien & Menüs anlegen...' },
        { progress: 95, text: 'Design anwenden...' },
    ];

    $form.on('submit', function (e) {
        e.preventDefault();

        // Validate required fields
        var blogName = $('#blog_name').val();
        var blogTopic = $('#blog_topic').val();
        var ownerName = $('#owner_name').val();
        var ownerEmail = $('#owner_email').val();

        if (!blogName || !blogTopic || !ownerName || !ownerEmail) {
            alert('Bitte fülle alle Pflichtfelder (*) aus.');
            return;
        }

        // Disable form
        $submit.prop('disabled', true).text(flavorSeo.i18n.processing);
        $progress.show();
        $result.hide();

        // Animate progress steps
        var stepIndex = 0;
        var stepInterval = setInterval(function () {
            if (stepIndex < steps.length) {
                $progressFill.css('width', steps[stepIndex].progress + '%');
                $progressText.text(steps[stepIndex].text);
                stepIndex++;
            }
        }, 500);

        // Collect form data
        var formData = $form.serializeArray();
        formData.push({ name: 'action', value: 'flavor_run_setup' });
        formData.push({ name: 'nonce', value: flavorSeo.nonce });

        // Send AJAX request
        $.ajax({
            url: flavorSeo.ajaxUrl,
            type: 'POST',
            data: $.param(formData),
            timeout: 60000,
            success: function (response) {
                clearInterval(stepInterval);
                $progressFill.css('width', '100%');
                $progressText.text('Fertig!');

                if (response.success) {
                    var html = '<h3>' + flavorSeo.i18n.success + '</h3>';
                    html += renderResults(response.data.results);
                    $result.html(html).addClass('success').removeClass('error').show();
                } else {
                    $result.html('<h3>' + flavorSeo.i18n.error + '</h3><p>' + (response.data || 'Unbekannter Fehler') + '</p>')
                        .addClass('error').removeClass('success').show();
                }
            },
            error: function (xhr, status, error) {
                clearInterval(stepInterval);
                $result.html('<h3>' + flavorSeo.i18n.error + '</h3><p>' + error + '</p>')
                    .addClass('error').removeClass('success').show();
            },
            complete: function () {
                $submit.prop('disabled', false).text('Blog jetzt einrichten');
            }
        });
    });

    /**
     * Render setup results as HTML
     */
    function renderResults(results) {
        if (!results) return '';

        var html = '<ul>';
        for (var key in results) {
            if (!results.hasOwnProperty(key)) continue;
            var item = results[key];
            var icon = item.status === 'ok' ? '&#10004;' : '&#10060;';
            html += '<li>' + icon + ' <strong>' + capitalize(key) + ':</strong> ' + (item.message || '') + '</li>';

            // Show sub-details
            if (item.details) {
                html += '<ul>';
                for (var subKey in item.details) {
                    if (!item.details.hasOwnProperty(subKey)) continue;
                    var sub = item.details[subKey];
                    if (typeof sub === 'string') {
                        html += '<li>' + sub + '</li>';
                    } else if (sub.message) {
                        html += '<li>' + sub.message + '</li>';
                    }
                }
                html += '</ul>';
            }
        }
        html += '</ul>';
        return html;
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

})(jQuery);
