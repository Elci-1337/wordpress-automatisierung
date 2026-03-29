/**
 * Flavor SEO Starter – Admin JavaScript
 * Setup-Wizard + KI-Generierung
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
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

        // ============================================
        // AI Provider Presets
        // ============================================
        $('#ai_provider').on('change', function () {
            var provider = $(this).val();
            var presets = {
                'openai':    { url: 'https://api.openai.com/v1/chat/completions', model: 'gpt-4o-mini' },
                'anthropic': { url: 'https://api.openai.com/v1/chat/completions', model: 'claude-sonnet-4-20250514' },
                'local':     { url: 'http://localhost:11434/v1/chat/completions', model: 'llama3' },
                'custom':    { url: '', model: '' },
            };
            if (presets[provider]) {
                $('#ai_api_url').val(presets[provider].url);
                $('#ai_model').val(presets[provider].model);
            }
        });

        // Toggle API key visibility
        $('.flavor-toggle-visibility').on('click', function () {
            var $target = $('#' + $(this).data('target'));
            var type = $target.attr('type') === 'password' ? 'text' : 'password';
            $target.attr('type', type);
            $(this).find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
        });

        // ============================================
        // AI Test Connection
        // ============================================
        $('#ai-test-connection').on('click', function () {
            var $btn = $(this);
            var $status = $('#ai-status-text');
            var apiKey = $('#ai_api_key').val();

            if (!apiKey) {
                $status.text('Bitte gib einen API-Key ein.').css('color', '#991b1b');
                return;
            }

            $btn.prop('disabled', true);
            $status.text('Teste Verbindung...').css('color', '#666');

            $.ajax({
                url: flavorSeo.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'flavor_ai_generate',
                    nonce: flavorSeo.nonce,
                    field: 'blog_tagline',
                    api_key: apiKey,
                    api_url: $('#ai_api_url').val(),
                    model: $('#ai_model').val(),
                    context: { blog_name: 'Test', blog_topic: 'technology', blog_language: 'de_DE' }
                },
                timeout: 30000,
                success: function (response) {
                    if (response.success) {
                        $status.html('<span style="color:#166534;">&#10004; Verbindung erfolgreich!</span>');
                    } else {
                        $status.html('<span style="color:#991b1b;">&#10060; ' + (response.data || 'Fehler') + '</span>');
                    }
                },
                error: function (xhr, status, error) {
                    $status.html('<span style="color:#991b1b;">&#10060; Verbindungsfehler: ' + error + '</span>');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        });

        // ============================================
        // Single Field AI Generation
        // ============================================
        $(document).on('click', '.flavor-ai-btn', function () {
            var $btn = $(this);
            var field = $btn.data('field');
            var apiKey = $('#ai_api_key').val();

            if (!apiKey) {
                alert('Bitte gib zuerst im Abschnitt "KI-Assistent" einen API-Key ein.');
                $('html, body').animate({ scrollTop: $('#step-ai').offset().top - 50 }, 400);
                return;
            }

            if (!$('#blog_name').val() || !$('#blog_topic').val()) {
                alert('Bitte fülle zuerst Blog-Name und Thema aus.');
                return;
            }

            generateField(field, $btn);
        });

        function generateField(field, $btn) {
            var $target = $('#' + field);
            var originalText = $btn.html();

            $btn.html('<span class="flavor-ai-spinner"></span>').prop('disabled', true);
            $target.addClass('flavor-ai-loading');

            $.ajax({
                url: flavorSeo.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'flavor_ai_generate',
                    nonce: flavorSeo.nonce,
                    field: field,
                    api_key: $('#ai_api_key').val(),
                    api_url: $('#ai_api_url').val(),
                    model: $('#ai_model').val(),
                    context: getFormContext()
                },
                timeout: 30000,
                success: function (response) {
                    if (response.success && response.data.content) {
                        $target.val(response.data.content).trigger('change');
                        // Brief green flash
                        $target.addClass('flavor-ai-success');
                        setTimeout(function () { $target.removeClass('flavor-ai-success'); }, 1500);
                    } else {
                        alert('KI-Fehler: ' + (response.data || 'Unbekannter Fehler'));
                    }
                },
                error: function (xhr, status, error) {
                    alert('Verbindungsfehler: ' + error);
                },
                complete: function () {
                    $btn.html(originalText).prop('disabled', false);
                    $target.removeClass('flavor-ai-loading');
                }
            });
        }

        // ============================================
        // Bulk AI Generation (fill all empty fields)
        // ============================================
        $('#flavor-ai-fill-all').on('click', function () {
            var apiKey = $('#ai_api_key').val();
            if (!apiKey) {
                alert('Bitte gib zuerst im Abschnitt "KI-Assistent" einen API-Key ein.');
                $('html, body').animate({ scrollTop: $('#step-ai').offset().top - 50 }, 400);
                return;
            }

            if (!$('#blog_name').val()) {
                alert('Bitte gib zuerst einen Blog-Namen ein.');
                return;
            }

            if (!$('#blog_topic').val()) {
                alert('Bitte wähle zuerst ein Thema aus.');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true);
            $('#flavor-ai-bulk-progress').show();

            // Fields to fill (in order, only if empty)
            var fields = [
                { id: 'blog_tagline', label: 'Tagline' },
                { id: 'owner_name', label: 'Betreibername' },
                { id: 'author_name', label: 'Autorenname' },
                { id: 'author_bio', label: 'Autoren-Bio' },
                { id: 'author_expertise', label: 'Expertise' },
                { id: 'custom_instructions', label: 'Vorgaben' },
            ];

            // Filter to only empty fields
            var emptyFields = fields.filter(function (f) {
                return !$('#' + f.id).val().trim();
            });

            if (emptyFields.length === 0) {
                alert('Alle Felder sind bereits ausgefüllt.');
                $btn.prop('disabled', false);
                $('#flavor-ai-bulk-progress').hide();
                return;
            }

            var total = emptyFields.length;
            var current = 0;

            function processNext() {
                if (current >= emptyFields.length) {
                    $('#ai-bulk-progress-fill').css('width', '100%');
                    $('#ai-bulk-progress-text').text('Fertig! Alle Felder wurden ausgefüllt.');
                    $btn.prop('disabled', false);
                    return;
                }

                var field = emptyFields[current];
                var percent = Math.round(((current + 1) / total) * 100);
                $('#ai-bulk-progress-fill').css('width', percent + '%');
                $('#ai-bulk-progress-text').text('Generiere: ' + field.label + ' (' + (current + 1) + '/' + total + ')...');

                $.ajax({
                    url: flavorSeo.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'flavor_ai_generate',
                        nonce: flavorSeo.nonce,
                        field: field.id,
                        api_key: $('#ai_api_key').val(),
                        api_url: $('#ai_api_url').val(),
                        model: $('#ai_model').val(),
                        context: getFormContext()
                    },
                    timeout: 30000,
                    success: function (response) {
                        if (response.success && response.data.content) {
                            var $target = $('#' + field.id);
                            $target.val(response.data.content).trigger('change');
                            $target.addClass('flavor-ai-success');
                            setTimeout(function () { $target.removeClass('flavor-ai-success'); }, 2000);
                        }
                    },
                    complete: function () {
                        current++;
                        // Small delay between requests to avoid rate limits
                        setTimeout(processNext, 500);
                    }
                });
            }

            processNext();
        });

        // ============================================
        // Setup Form Submit
        // ============================================
        $form.on('submit', function (e) {
            e.preventDefault();

            var blogName = $('#blog_name').val();
            var blogTopic = $('#blog_topic').val();
            var ownerName = $('#owner_name').val();
            var ownerEmail = $('#owner_email').val();

            if (!blogName || !blogTopic || !ownerName || !ownerEmail) {
                alert('Bitte fülle alle Pflichtfelder (*) aus.');
                return;
            }

            $submit.prop('disabled', true).text(flavorSeo.i18n.processing);
            $progress.show();
            $result.hide();

            var stepIndex = 0;
            var stepInterval = setInterval(function () {
                if (stepIndex < steps.length) {
                    $progressFill.css('width', steps[stepIndex].progress + '%');
                    $progressText.text(steps[stepIndex].text);
                    stepIndex++;
                }
            }, 500);

            // Collect form data – use serialize for proper encoding
            var formData = $form.serialize();
            formData += '&action=flavor_run_setup&nonce=' + encodeURIComponent(flavorSeo.nonce);

            $.ajax({
                url: flavorSeo.ajaxUrl,
                type: 'POST',
                data: formData,
                timeout: 120000,
                success: function (response) {
                    clearInterval(stepInterval);
                    $progressFill.css('width', '100%');
                    $progressText.text('Fertig!');

                    if (response.success) {
                        var html = '<h3>' + flavorSeo.i18n.success + '</h3>';
                        html += renderResults(response.data.results);
                        $result.html(html).addClass('success').removeClass('error').show();
                    } else {
                        var errMsg = response.data;
                        if (typeof errMsg === 'object') {
                            errMsg = errMsg.message || JSON.stringify(errMsg);
                        }
                        $result.html('<h3>' + flavorSeo.i18n.error + '</h3><p>' + (errMsg || 'Unbekannter Fehler') + '</p>')
                            .addClass('error').removeClass('success').show();
                    }
                },
                error: function (xhr, status, error) {
                    clearInterval(stepInterval);
                    var detail = '';
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        detail = resp.data || error;
                    } catch (e) {
                        // Check for common PHP errors in response
                        if (xhr.responseText && xhr.responseText.indexOf('Fatal error') !== -1) {
                            detail = 'PHP Fatal Error – bitte prüfe die Server-Logs. ';
                            detail += xhr.responseText.substring(0, 300);
                        } else {
                            detail = error || status;
                        }
                    }
                    $result.html('<h3>' + flavorSeo.i18n.error + '</h3><p>' + detail + '</p>')
                        .addClass('error').removeClass('success').show();
                },
                complete: function () {
                    $submit.prop('disabled', false).text('Blog jetzt einrichten');
                }
            });
        });

        // ============================================
        // Helpers
        // ============================================
        function getFormContext() {
            return {
                blog_name: $('#blog_name').val() || '',
                blog_topic: $('#blog_topic').val() || '',
                blog_tagline: $('#blog_tagline').val() || '',
                blog_language: $('#blog_language').val() || 'de_DE',
                owner_name: $('#owner_name').val() || '',
                owner_email: $('#owner_email').val() || '',
                author_name: $('#author_name').val() || '',
                author_bio: $('#author_bio').val() || '',
                author_expertise: $('#author_expertise').val() || '',
                custom_instructions: $('#custom_instructions').val() || ''
            };
        }

        function renderResults(results) {
            if (!results) return '<p>Keine Details verfügbar.</p>';

            var html = '<ul>';
            for (var key in results) {
                if (!results.hasOwnProperty(key)) continue;
                var item = results[key];
                var icon = (item.status === 'ok') ? '&#10004;' : '&#10060;';
                html += '<li>' + icon + ' <strong>' + capitalize(key) + ':</strong> ' + (item.message || '') + '</li>';

                if (item.details) {
                    html += '<ul>';
                    for (var subKey in item.details) {
                        if (!item.details.hasOwnProperty(subKey)) continue;
                        var sub = item.details[subKey];
                        if (typeof sub === 'string') {
                            html += '<li>' + sub + '</li>';
                        } else if (sub && sub.message) {
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
    });

})(jQuery);
