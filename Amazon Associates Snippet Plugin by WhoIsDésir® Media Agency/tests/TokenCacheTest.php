<?php
use PHPUnit\Framework\TestCase;

/**
 * Tests for Creators API OAuth 2.0 token caching and expiration.
 */
class TokenCacheTest extends TestCase {

    protected function setUp(): void {
        aa_test_reset();
    }

    private function configureCredentials($version = '3.1') {
        aa_test_set_option('aa_credential_id', 'credential-id-123');
        aa_test_set_option('aa_credential_secret', 'credential-secret-456');
        aa_test_set_option('aa_credential_version', $version);
    }

    private function queueResponse(Token_Requesting_OAuth_Client $client, $token) {
        $client->responses = array(array(
            'response' => array('code' => 200, 'message' => 'OK'),
            'body'     => json_encode(array('access_token' => $token, 'expires_in' => 3600)),
        ));
    }

    public function testTokenEndpointMappingByCredentialVersion() {
        $client = new AA_Creators_OAuth_Client();
        $this->assertSame('https://api.amazon.com/auth/o2/token', $client->get_token_endpoint('3.1'));
        $this->assertSame('https://api.amazon.co.uk/auth/o2/token', $client->get_token_endpoint('3.2'));
        $this->assertSame('https://api.amazon.co.jp/auth/o2/token', $client->get_token_endpoint('3.3'));
        $this->assertSame('https://creatorsapi.auth.us-east-1.amazoncognito.com/oauth2/token', $client->get_token_endpoint('2.1'));
        $this->assertSame('https://creatorsapi.auth.eu-south-2.amazoncognito.com/oauth2/token', $client->get_token_endpoint('2.2'));
        $this->assertSame('https://creatorsapi.auth.us-west-2.amazoncognito.com/oauth2/token', $client->get_token_endpoint('2.3'));
    }

    public function testUnsupportedVersionReturnsError() {
        $client = new AA_Creators_OAuth_Client();
        $this->assertTrue(is_wp_error($client->get_token_endpoint('9.9')));
    }

    public function testMissingCredentialsReturnsError() {
        $client = new AA_Creators_OAuth_Client();
        $result = $client->request_token();
        $this->assertTrue(is_wp_error($result));
        $this->assertSame('creators_oauth_missing_credentials', $result->get_error_code());
    }

    public function testGetTokenReturnsFalseWithoutCredentials() {
        $client = new AA_Creators_OAuth_Client();
        $this->assertFalse($client->get_token());
    }

    public function testTokenIsCachedAndReused() {
        $this->configureCredentials();
        $client = new Token_Requesting_OAuth_Client(array(
            array('access_token' => 'token-1', 'expires_in' => 3600),
        ));

        $this->assertSame('token-1', $client->get_token());
        $this->assertSame('token-1', $client->get_token());
        $this->assertSame('token-1', $client->get_token());
        $this->assertSame(1, $client->request_count, 'A cached token must not trigger new token requests.');
    }

    public function testTokenReusedAcrossManyLookups() {
        $this->configureCredentials();
        $client = new Token_Requesting_OAuth_Client(array(
            array('access_token' => 'token-1', 'expires_in' => 3600),
        ));

        for ($i = 0; $i < 5; $i++) {
            $this->assertSame('token-1', $client->get_token());
        }

        $this->assertSame(1, $client->request_count, 'Product lookups must not each request a new token.');
    }

    public function testExpiredTokenTriggersFreshRequest() {
        $this->configureCredentials();
        $client = new Token_Requesting_OAuth_Client(array(
            array('access_token' => 'token-1', 'expires_in' => 3600),
        ));

        $this->assertSame('token-1', $client->get_token());

        // Simulate the cached token expiring.
        $GLOBALS['aa_transients'][AA_Creators_OAuth_Client::TOKEN_TRANSIENT]['expires'] = time() - 10;
        $this->queueResponse($client, 'token-2');

        $this->assertSame('token-2', $client->get_token());
        $this->assertSame(2, $client->request_count, 'An expired token must trigger exactly one refresh.');
    }

    public function testTokenCacheExpiresBeforeTokenValidity() {
        $this->configureCredentials();
        $client = new Token_Requesting_OAuth_Client(array(
            array('access_token' => 'token-1', 'expires_in' => 3600),
        ));

        $client->get_token();

        $entry = $GLOBALS['aa_transients'][AA_Creators_OAuth_Client::TOKEN_TRANSIENT];
        $remaining = $entry['expires'] - time();

        // TTL should be expires_in minus the 60s refresh buffer.
        $this->assertGreaterThanOrEqual(3500, $remaining, 'Cache should live ~1h.');
        $this->assertLessThanOrEqual(3540, $remaining, 'Cache must refresh before the token actually expires.');
    }

    public function testCachedTokenDataExpiryCheck() {
        $this->configureCredentials();
        $client = new Token_Requesting_OAuth_Client(array(
            array('access_token' => 'token-1', 'expires_in' => 3600),
        ));

        $this->assertSame('token-1', $client->get_token());

        // Simulate a stale token whose expires_at (inside the cached array) has passed.
        $cached = $GLOBALS['aa_transients'][AA_Creators_OAuth_Client::TOKEN_TRANSIENT];
        $cached['expires'] = time() + 300; // transient still technically live...
        $GLOBALS['aa_transients'][AA_Creators_OAuth_Client::TOKEN_TRANSIENT] = $cached;
        $cached['value']['expires_at'] = time() - 5; // ...but stored expiry says otherwise.
        $GLOBALS['aa_transients'][AA_Creators_OAuth_Client::TOKEN_TRANSIENT]['value'] = $cached['value'];

        $this->queueResponse($client, 'token-2');
        $this->assertSame('token-2', $client->get_token());
        $this->assertSame(2, $client->request_count);
    }

    public function testOAuthRequestUsesJsonLwaFlowForV3() {
        $this->configureCredentials('3.1');
        $client = new Token_Requesting_OAuth_Client(array(
            array('access_token' => 'token-1', 'expires_in' => 3600),
        ));

        $client->get_token();

        $this->assertSame('https://api.amazon.com/auth/o2/token', $client->last_url);
        $this->assertSame('application/json', $client->last_content_type);
        $body = json_decode($client->last_body, true);
        $this->assertSame('client_credentials', $body['grant_type']);
        $this->assertSame('credential-id-123', $body['client_id']);
        $this->assertSame('credential-secret-456', $body['client_secret']);
        $this->assertSame('creatorsapi::default', $body['scope']);
    }

    public function testOAuthRequestUsesFormFlowForV2() {
        $this->configureCredentials('2.1');
        $client = new Token_Requesting_OAuth_Client(array(
            array('access_token' => 'token-1', 'expires_in' => 3600),
        ));

        $client->get_token();

        $this->assertSame('https://creatorsapi.auth.us-east-1.amazoncognito.com/oauth2/token', $client->last_url);
        $this->assertSame('application/x-www-form-urlencoded', $client->last_content_type);
        $this->assertStringContainsString('grant_type=client_credentials', $client->last_body);
        $this->assertStringContainsString('scope=creatorsapi', $client->last_body);
    }

    public function testTokenRequestFailureReturnsFalse() {
        $this->configureCredentials();
        $client = new Token_Requesting_OAuth_Client(array());
        $this->assertFalse($client->get_token());
        $this->assertSame(1, $client->request_count);
    }

    public function testClearCachedTokenForcesRefresh() {
        $this->configureCredentials();
        $client = new Token_Requesting_OAuth_Client(array(
            array('access_token' => 'token-1', 'expires_in' => 3600),
        ));

        $this->assertSame('token-1', $client->get_token());
        $client->clear_cached_token();
        $this->queueResponse($client, 'token-2');

        $this->assertSame('token-2', $client->get_token());
        $this->assertSame(2, $client->request_count);
    }
}
