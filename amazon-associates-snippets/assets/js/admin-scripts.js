/**
 * Admin Dashboard Interactivity Script with OAuth 2.0 Support
 */

jQuery(document).ready(function ($) {
	'use strict';

	// 1. Authentication Mode Toggle Handler
	$('.aa-auth-toggle').on('change', function () {
		var selectedMode = $('input[name="aa_auth_mode"]:checked').val();
		if ('oauth2' === selectedMode) {
			$('#aa-sigv4-section').slideUp(200);
			$('#aa-oauth2-section').slideDown(200);
		} else {
			$('#aa-oauth2-section').slideUp(200);
			$('#aa-sigv4-section').slideDown(200);
		}
	});

	// 2. Fetch Fresh OAuth Access Token via AJAX
	$('#aa-fetch-token-btn').on('click', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var $status = $('#aa-token-status');

		$btn.prop('disabled', true);
		$status.text('Requesting OAuth 2.0 Access Token...').css('color', '#0284c7');

		$.post(aaSnippetsAdmin.ajax_url, {
			action: 'aa_refresh_oauth_token',
			nonce: aaSnippetsAdmin.nonce
		}, function (res) {
			$btn.prop('disabled', false);
			if (res.success) {
				$('#aa_oauth_access_token').val(res.data.access_token);
				$status.text(res.data.message).css('color', '#16a34a');
			} else {
				$status.text('OAuth Error: ' + (res.data.message || 'Failed to fetch token')).css('color', '#dc2626');
			}
		});
	});

	// 3. One-Click Copy to Clipboard
	$(document).on('click', '.aa-copy-btn', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var targetId = $btn.data('target');
		var textToCopy = $('#' + targetId).text();

		if (navigator.clipboard) {
			navigator.clipboard.writeText(textToCopy).then(function () {
				showCopyFeedback($btn);
			});
		} else {
			var $temp = $('<textarea>');
			$('body').append($temp);
			$temp.val(textToCopy).select();
			document.execCommand('copy');
			$temp.remove();
			showCopyFeedback($btn);
		}
	});

	function showCopyFeedback($btn) {
		var origText = $btn.text();
		$btn.text('Copied! ✓').addClass('button-primary');
		setTimeout(function () {
			$btn.text(origText).removeClass('button-primary');
		}, 1800);
	}

	// 4. Interactive Generator Dynamic ASIN Updater
	$('#aa-generate-btn').on('click', function () {
		var asin = $('#aa-gen-asin').val().trim() || 'B08N5WRWNW';
		asin = asin.toUpperCase();

		$('#sc-box').text('[amazon_box asin="' + asin + '"]');
		$('#php-box').text("<?php echo aa_render_product_box( '" + asin + "' ); ?>");

		$('#sc-btn').text('[amazon_button asin="' + asin + '" text="Check Price on Amazon"]');
		$('#php-btn').text("<?php echo aa_render_button( '" + asin + "', 'Check Price on Amazon' ); ?>");

		$('#php-raw').text("<?php\n$product = aa_get_product_data( '" + asin + "' );\nif ( $product ) {\n    echo esc_html( $product['title'] ) . ' - ' . esc_html( $product['price'] );\n}\n?>");
	});

	// 5. Purge Transient Cache
	$('#aa-clear-cache-btn').on('click', function () {
		var $btn = $(this);
		var $status = $('#aa-cache-status');

		$btn.prop('disabled', true);
		$status.text('Clearing transient cache...').css('color', '#0284c7');

		$.post(aaSnippetsAdmin.ajax_url, {
			action: 'aa_clear_plugin_cache',
			nonce: aaSnippetsAdmin.nonce
		}, function (res) {
			$btn.prop('disabled', false);
			if (res.success) {
				$status.text(res.data.message).css('color', '#16a34a');
			} else {
				$status.text(res.data.message || 'Error clearing cache.').css('color', '#dc2626');
			}
		});
	});

	// 6. Live API Connection Tester
	$('#aa-run-test-btn').on('click', function () {
		var $btn = $(this);
		var $terminal = $('#aa-test-log-output');
		var asin = $('#aa-test-asin').val().trim() || 'B08N5WRWNW';

		$btn.prop('disabled', true);
		$terminal.text('> Initiating API connection test for ASIN: ' + asin + '...\n');

		$.post(aaSnippetsAdmin.ajax_url, {
			action: 'aa_test_api_connection',
			nonce: aaSnippetsAdmin.nonce,
			asin: asin
		}, function (res) {
			$btn.prop('disabled', false);
			if (res.success) {
				$terminal.append('> [STATUS 200 OK] ' + res.data.message + '\n\n');
				$terminal.append('> Parsed Product Payload:\n');
				$terminal.append(JSON.stringify(res.data.data, null, 2));
			} else {
				$terminal.append('> [API ERROR] ' + res.data.message + '\n\n');
				if (res.data.data) {
					$terminal.append('> Fallback Data Payload:\n');
					$terminal.append(JSON.stringify(res.data.data, null, 2));
				}
			}
		});
	});
});
