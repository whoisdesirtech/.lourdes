<?php
use PHPUnit\Framework\TestCase;

/**
 * Tests for fallback behavior and Creators API error handling.
 */
class FallbackAndErrorHandlingTest extends TestCase {

    protected function setUp(): void {
        aa_test_reset();
    }

    private function configureCredentials() {
        aa_test_set_option('aa_partner_tag', 'jeanfils-20');
        aa_test_set_option('aa_credential_id', 'credential-id');
        aa_test_set_option('aa_credential_secret', 'credential-secret');
        aa_test_set_option('aa_credential_version', '3.1');
    }

    public function testFallbackProductStructure() {
        $api = AA_Amazon_API::get_instance();
        $fallback = $api->get_fallback_product('B08N5WRWNW', 'Something failed');

        $this->assertTrue($fallback['is_fallback']);
        $this->assertSame('Something failed', $fallback['error']);
        $this->assertSame('B08N5WRWNW', $fallback['asin']);
        $this->assertStringContainsString('placeholder.png', $fallback['image']);
        $this->assertSame('', $fallback['price']);
        $this->assertFalse($fallback['is_prime']);
        $this->assertSame(array(), $fallback['features']);
        $this->assertArrayHasKey('title', $fallback);
        $this->assertArrayHasKey('url', $fallback);
    }

    public function testFallbackWhenCredentialsMissing() {
        $api = AA_Amazon_API::get_instance();
        $data = $api->get_item('B08N5WRWNW');

        $this->assertTrue($data['is_fallback']);
        $this->assertStringContainsString('credentials', strtolower($data['error']));
        $this->assertStringContainsString('B08N5WRWNW', $data['url']);
    }

    public function testFallbackWhenPartnerTagMissing() {
        aa_test_set_option('aa_credential_id', 'credential-id');
        aa_test_set_option('aa_credential_secret', 'credential-secret');
        aa_test_set_option('aa_credential_version', '3.1');

        $api = AA_Amazon_API::get_instance();
        $data = $api->get_item('B08N5WRWNW');

        $this->assertTrue($data['is_fallback']);
        $this->assertStringContainsString('credentials', strtolower($data['error']));
    }

    public function testFallbackWhenTransportReturnsError() {
        $this->configureCredentials();
        $api = new AA_Amazon_API(
            new Fake_OAuth_Client('token'),
            new Stub_Transport(new WP_Error('creators_api_error', 'The request throttled')),
            new AA_Amazon_Response_Normalizer()
        );

        $data = $api->get_item('B08N5WRWNW');

        $this->assertTrue($data['is_fallback']);
        $this->assertSame('The request throttled', $data['error']);
    }

    public function testFallbackWhenItemNotFound() {
        $this->configureCredentials();
        $api = new AA_Amazon_API(
            new Fake_OAuth_Client('token'),
            new Stub_Transport(aa_creators_fixture_response()),
            new AA_Amazon_Response_Normalizer()
        );

        $data = $api->get_item('B09NOTHERE');

        $this->assertTrue($data['is_fallback']);
        $this->assertStringContainsString('not found', strtolower($data['error']));
    }

    public function testFallbackUrlIncludesPartnerTag() {
        aa_test_set_option('aa_partner_tag', 'jeanfils-20');
        $api = AA_Amazon_API::get_instance();

        $fallback = $api->get_fallback_product('B08N5WRWNW');

        $this->assertStringContainsString('/dp/B08N5WRWNW', $fallback['url']);
        $this->assertStringContainsString('tag=jeanfils-20', $fallback['url']);
    }

    public function testFallbackWhenTokenMissing() {
        $this->configureCredentials();
        $transport = new Recording_Http_Transport(
            new Fake_OAuth_Client(''),
            aa_creators_http_response(200, aa_creators_fixture_response())
        );

        $api = new AA_Amazon_API(
            new Fake_OAuth_Client(''),
            $transport,
            new AA_Amazon_Response_Normalizer()
        );

        $data = $api->get_item('B08N5WRWNW');

        $this->assertTrue($data['is_fallback']);
        $this->assertSame(0, $transport->get_call_count(), 'No HTTP call should happen without a token.');
    }

    public function testHttpErrorSurfacesInFallback() {
        $this->configureCredentials();
        $api = new AA_Amazon_API(
            new Fake_OAuth_Client('token'),
            new Recording_Http_Transport(
                new Fake_OAuth_Client('token'),
                aa_creators_http_response(503, array(
                    'errors' => array(array('code' => 'Throttled', 'message' => 'Rate limit exceeded')),
                ))
            ),
            new AA_Amazon_Response_Normalizer()
        );

        $data = $api->get_item('B08N5WRWNW');

        $this->assertTrue($data['is_fallback']);
        $this->assertSame('Rate limit exceeded', $data['error']);
    }

    public function testSuccessfulLookupNotFallback() {
        $this->configureCredentials();
        $api = new AA_Amazon_API(
            new Fake_OAuth_Client('token'),
            new Recording_Http_Transport(
                new Fake_OAuth_Client('token'),
                aa_creators_http_response(200, aa_creators_fixture_response())
            ),
            new AA_Amazon_Response_Normalizer()
        );

        $data = $api->get_item('B08N5WRWNW');

        $this->assertFalse($data['is_fallback']);
        $this->assertSame('Example Product Title', $data['title']);
        $this->assertSame('$59.49', $data['price']);
        $this->assertArrayHasKey('updated_at', $data);
    }

    public function testGetItemCachesSuccessfulResult() {
        $this->configureCredentials();
        $transport = new Recording_Http_Transport(
            new Fake_OAuth_Client('token'),
            aa_creators_http_response(200, aa_creators_fixture_response())
        );
        $api = new AA_Amazon_API(
            new Fake_OAuth_Client('token'),
            $transport,
            new AA_Amazon_Response_Normalizer()
        );

        $api->get_item('B08N5WRWNW');
        $api->get_item('B08N5WRWNW');
        $api->get_item('B08N5WRWNW');

        $this->assertSame(1, $transport->get_call_count(), 'Successful results must be transient-cached.');
    }

    public function testGetItemFillsPlaceholderImageWhenMissing() {
        $this->configureCredentials();
        $response = aa_creators_fixture_response();
        unset($response['itemResults']['items'][0]['images']);

        $api = new AA_Amazon_API(
            new Fake_OAuth_Client('token'),
            new Stub_Transport($response),
            new AA_Amazon_Response_Normalizer()
        );

        $data = $api->get_item('B08N5WRWNW');

        $this->assertFalse($data['is_fallback']);
        $this->assertStringContainsString('placeholder.png', $data['image']);
    }
}
