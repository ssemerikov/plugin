/**
 * @file plugins/generic/reviewerCertificate/js/certificate.js
 *
 * Copyright (c) 2024
 * Distributed under the GNU GPL v3.
 *
 * JavaScript for reviewer certificate functionality
 */

(function($) {
    'use strict';

    /**
     * Certificate download handler
     */
    var CertificateHandler = {

        /**
         * Initialize certificate functionality
         */
        init: function() {
            this.bindEvents();
            this.checkCertificateAvailability();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Handle certificate download button clicks
            $(document).on('click', '.certificate-download-button', function(e) {
                var $button = $(this);
                var url = $button.attr('href');

                // Track download
                CertificateHandler.trackDownload(url);

                // Show loading state
                $button.addClass('loading');
                $button.find('span').removeClass('fa-certificate').addClass('fa-spinner fa-spin');
            });

            // Handle certificate verification
            $(document).on('submit', '#certificateVerificationForm', function(e) {
                e.preventDefault();
                CertificateHandler.verifyCertificate();
            });

            // Color picker helpers for settings form
            if ($('#textColorR').length) {
                CertificateHandler.initColorPicker();
            }
        },

        /**
         * Check certificate availability for completed reviews
         */
        checkCertificateAvailability: function() {
            $('.review-assignment').each(function() {
                var $review = $(this);
                var reviewId = $review.data('review-id');
                var isCompleted = $review.data('review-completed');

                if (isCompleted && reviewId) {
                    CertificateHandler.loadCertificateButton(reviewId, $review);
                }
            });
        },

        /**
         * Load certificate button for a review
         * @param {number} reviewId
         * @param {jQuery} $container
         */
        loadCertificateButton: function(reviewId, $container) {
            $.ajax({
                url: pkp.registry.get('baseUrl') + '/index.php/certificate/checkAvailability/' + reviewId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status && response.available) {
                        var buttonHtml = '<div class="reviewer-certificate-section">' +
                            '<a href="' + response.url + '" class="pkp_button certificate-download-button" target="_blank">' +
                            '<span class="fa fa-certificate"></span> ' +
                            response.label +
                            '</a>' +
                            '</div>';
                        $container.append(buttonHtml);
                    }
                },
                error: function() {
                    console.log('Could not check certificate availability');
                }
            });
        },

        /**
         * Track certificate download
         * @param {string} url
         */
        trackDownload: function(url) {
            // Send analytics event if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'certificate_download', {
                    'event_category': 'reviewer_engagement',
                    'event_label': 'Certificate Download'
                });
            }

            // Log for internal tracking
            console.log('Certificate download initiated:', url);
        },

        /**
         * Verify certificate by code
         */
        verifyCertificate: function() {
            var code = $('#certificateCode').val().trim();
            var $resultDiv = $('#verificationResult');
            var $button = $('#verifyButton');

            if (!code) {
                $resultDiv.html('<div class="error">Please enter a certificate code</div>');
                return;
            }

            $button.prop('disabled', true).text('Verifying...');

            $.ajax({
                url: pkp.registry.get('baseUrl') + '/index.php/certificate/verify/' + code,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status && response.content.valid) {
                        var data = response.content;
                        var resultHtml = '<div class="success">' +
                            '<h4>Certificate Verified</h4>' +
                            '<p><strong>Reviewer:</strong> ' + data.reviewerName + '</p>' +
                            '<p><strong>Journal:</strong> ' + data.journalName + '</p>' +
                            '<p><strong>Date Issued:</strong> ' + data.dateIssued + '</p>' +
                            '<p><strong>Certificate Code:</strong> ' + data.certificateCode + '</p>' +
                            '</div>';
                        $resultDiv.html(resultHtml);
                    } else {
                        $resultDiv.html('<div class="error">Invalid certificate code</div>');
                    }
                },
                error: function() {
                    $resultDiv.html('<div class="error">Verification failed. Please try again.</div>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Verify');
                }
            });
        },

        /**
         * Initialize color picker for settings form
         */
        initColorPicker: function() {
            var $colorInputs = $('#textColorR, #textColorG, #textColorB');
            var $preview = $('<div id="colorPreview"></div>').css({
                'width': '50px',
                'height': '50px',
                'border': '1px solid #ccc',
                'display': 'inline-block',
                'margin-left': '10px',
                'border-radius': '3px'
            });

            $colorInputs.last().parent().append($preview);

            var updateColorPreview = function() {
                var r = parseInt($('#textColorR').val()) || 0;
                var g = parseInt($('#textColorG').val()) || 0;
                var b = parseInt($('#textColorB').val()) || 0;

                // Validate RGB values
                r = Math.max(0, Math.min(255, r));
                g = Math.max(0, Math.min(255, g));
                b = Math.max(0, Math.min(255, b));

                $preview.css('background-color', 'rgb(' + r + ',' + g + ',' + b + ')');
            };

            $colorInputs.on('input change', updateColorPreview);
            updateColorPreview();
        },

        /**
         * Load template preview
         */
        loadTemplatePreview: function() {
            var $previewBtn = $('.preview-certificate-btn');

            $previewBtn.on('click', function(e) {
                e.preventDefault();

                var previewUrl = $(this).attr('href');
                window.open(previewUrl, 'CertificatePreview', 'width=800,height=600');
            });
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        CertificateHandler.init();
    });

    // Make CertificateHandler globally accessible
    window.ReviewerCertificate = CertificateHandler;

})(jQuery);
