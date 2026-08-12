<?php
use PHPUnit\Framework\TestCase;

/**
 * Tests for Creators API GetItems request construction (headers + body).
 */
class GetItemsRequestTest extends TestCase {

    protected function setUp(): void {
        aa_test_reset();
    }

    private function okResponse() {
        return aa_creators_http_response(200, aa_creators_fixture_response());
    }

    public function testRequestUsesLowerCamelCaseBody() {
        $transport = new Recording_Http_Transport(new Fake_OAuth_Client('test-token'), $this->okResponse());
        $result = $transport->get_items(array('B08N5WRWNW'), 'www.amazon.com', 'jeanfils-20');

        $this->assertFalse(is_wp_error($result));
        $this->assertSame(1, $transport->get_call_count());

        $body = json_decode($transport->calls[0]['body'], true);

        $this->assertSame(array('B08N5WRWNW'), $body['itemIds']);
        $this->assertSame('ASIN', $body['itemIdType']);
        $this->assertSame('www.amazon.com', $body['marketplace']);
        $this->assertSame('jeanfils-20', $body['partnerTag']);

        // No PA-API PascalCase keys.
        $this->assertArrayNotHasKey('ItemIds', $body);
        $this->assertArrayNotHasKey('PartnerTag', $body);
        $this->assertArrayNotHasKey('ItemIdType', $body);
    }

    public function testRequestUsesOffersV2Resources() {
        $transport = new Recording_Http_Transport(new Fake_OAuth_Client('test-token'), $this->okResponse());
        $transport->get_items(array('B08N5WRWNW'), 'www.amazon.com', 'jeanfils-20');

        $resources = json_decode($transport->calls[0]['body'], true)['resources'];

        $this->assertContains('offersV2.listings.price', $resources);
        $this->assertContains('offersV2.listings.availability', $resources);
        $this->assertContains('itemInfo.title', $resources);
        $this->assertContains('itemInfo.features', $resources);
        $this->assertContains('itemInfo.byLineInfo', $resources);
        $this->assertContains('images.primary.large', $resources);

        // No legacy Offers resource names.
        $this->assertNotContains('Offers.Listings.Price', $resources);
        $this->assertNotContains('Offers.Listings.DeliveryInfo.IsPrimeEligible', $resources);
    }

    public function testRequestHeaders() {
        $transport = new Recording_Http_Transport(new Fake_OAuth_Client('test-token'), $this->okResponse());
        $transport->get_items(array('B08N5WRWNW'), 'www.amazon.com', 'jeanfils-20');

        $call = $transport->calls[0];

        $this->assertSame('https://creatorsapi.amazon/catalog/v1/getItems', $call['url']);
        $this->assertSame('application/json', $call['headers']['Content-Type']);
        $this->assertSame('Bearer test-token', $call['headers']['Authorization']);
        $this->assertSame('www.amazon.com', $call['headers']['x-marketplace']);

        // No legacy SigV4 / X-Amz headers.
        $this->assertArrayNotHasKey('X-Amz-Date', $call['headers']);
        $this->assertArrayNotHasKey('X-Amz-Target', $call['headers']);
        $this->assertArrayNotHasKey('x-amz-access-token', $call['headers']);
    }

    public function testMissingTokenReturnsErrorBeforeAnyRequest() {
        $transport = new Recording_Http_Transport(new Fake_OAuth_Client(''), $this->okResponse());
        $result = $transport->get_items(array('B08N5WRWNW'), 'www.amazon.com', 'jeanfils-20');

        $this->assertTrue(is_wp_error($result));
        $this->assertSame('creators_api_token_missing', $result->get_error_code());
        $this->assertSame(0, $transport->get_call_count(), 'No HTTP request should be sent without a token.');
    }

    public function testHttpErrorResponseReturnsWpError() {
        $transport = new Recording_Http_Transport(
            new Fake_OAuth_Client('test-token'),
            aa_creators_http_response(400, array(
                'errors' => array(array('code' => 'InvalidParameterValue', 'message' => 'partnerTag is invalid')),
            ))
        );

        $result = $transport->get_items(array('B08N5WRWNW'), 'www.amazon.com', 'bad-tag');

        $this->assertTrue(is_wp_error($result));
        $this->assertSame('creators_api_error', $result->get_error_code());
        $this->assertSame('partnerTag is invalid', $result->get_error_message());
    }

    public function testExpiredTokenRetriesOnceWithFreshToken() {
        $oauth = new Refreshable_OAuth_Client();
        $oauth->manual_token = 'Atza|expired';
        $oauth->refresh_token = 'fresh-token';

        $transport = new Queueing_Http_Transport($oauth, array(
            aa_creators_http_response(401, array(
                'errors' => array(array('code' => 'TokenInvalid', 'message' => 'Token has expired')),
            )),
            $this->okResponse(),
        ));

        $result = $transport->get_items(array('B08N5WRWNW'), 'www.amazon.com', 'jeanfils-20');

        $this->assertFalse(is_wp_error($result));
        $this->assertSame(2, $transport->get_call_count());
        $this->assertSame('Bearer fresh-token', $transport->calls[1]['headers']['Authorization']);
        $this->assertSame(1, $oauth->refresh_count, 'Cached token should be invalidated before the retry.');
    }

    public function testExpiredManualTokenWithoutCredentialsReturnsClearError() {
        $oauth = new Refreshable_OAuth_Client();
        $oauth->manual_token = 'Atza|expired';
        $oauth->refresh_token = '';
        $oauth->credentials_configured = false;

        $transport = new Queueing_Http_Transport($oauth, array(
            aa_creators_http_response(401, array(
                'errors' => array(array('code' => 'TokenInvalid', 'message' => 'Token has expired')),
            )),
        ));

        $result = $transport->get_items(array('B08N5WRWNW'), 'www.amazon.com', 'jeanfils-20');

        $this->assertTrue(is_wp_error($result));
        $this->assertSame('creators_api_token_expired', $result->get_error_code());
        $this->assertStringContainsString('manually pasted', $result->get_error_message());
        $this->assertSame(1, $transport->get_call_count(), 'No retry without credentials to refresh with.');
    }

    public function testTokenRetryFailureReturnsError() {
        $oauth = new Refreshable_OAuth_Client();
        $oauth->manual_token = 'Atza|expired';
        $oauth->refresh_token = 'also-bad';

        $transport = new Queueing_Http_Transport($oauth, array(
            aa_creators_http_response(401, array(
                'errors' => array(array('message' => 'Token has expired')),
            )),
            aa_creators_http_response(401, array(
                'errors' => array(array('message' => 'Token has expired')),
            )),
        ));

        $result = $transport->get_items(array('B08N5WRWNW'), 'www.amazon.com', 'jeanfils-20');

        $this->assertTrue(is_wp_error($result));
        $this->assertSame('creators_api_token_expired', $result->get_error_code());
        $this->assertSame(2, $transport->get_call_count());
    }

    public function testNonTokenErrorIsNotRetried() {
        $oauth = new Refreshable_OAuth_Client();
        $oauth->manual_token = 'Atza|expired';
        $oauth->refresh_token = 'fresh-token';

        $transport = new Queueing_Http_Transport($oauth, array(
            aa_creators_http_response(400, array(
                'errors' => array(array('message' => 'partnerTag is invalid')),
            )),
        ));

        $result = $transport->get_items(array('B08N5WRWNW'), 'www.amazon.com', 'bad-tag');

        $this->assertTrue(is_wp_error($result));
        $this->assertSame('creators_api_error', $result->get_error_code());
        $this->assertSame(1, $transport->get_call_count(), 'Non-token errors must not trigger a retry.');
    }

    public function testInvalidJsonResponseReturnsWpError() {
        $transport = new Recording_Http_Transport(
            new Fake_OAuth_Client('test-token'),
            array('response' => array('code' => 200, 'message' => 'OK'), 'body' => 'this is not json')
        );

        $result = $transport->get_items(array('B08N5WRWNW'), 'www.amazon.com', 'jeanfils-20');

        $this->assertTrue(is_wp_error($result));
    }

    public function testTransportFactoryReturnsAValidTransport() {
        $transport = AA_Creators_API_Transport::create(new Fake_OAuth_Client('test-token'));
        $this->assertInstanceOf(AA_Creators_API_Transport::class, $transport);

        if (AA_Creators_API_Transport::sdk_is_available()) {
            $this->assertInstanceOf(AA_Creators_API_Sdk_Transport::class, $transport);
        } else {
            $this->assertInstanceOf(AA_Creators_API_Http_Transport::class, $transport);
        }
    }
}
