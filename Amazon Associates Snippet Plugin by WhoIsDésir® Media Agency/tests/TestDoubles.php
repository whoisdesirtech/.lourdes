<?php
/**
 * Shared test doubles & fixtures for the Amazon plugin test suite.
 */

/**
 * OAuth client that returns a fixed token without any HTTP traffic.
 */
class Fake_OAuth_Client extends AA_Creators_OAuth_Client {
    private $token;

    public function __construct($token = '') {
        $this->token = (string) $token;
    }

    public function get_token() {
        return $this->token;
    }
}

/**
 * OAuth client that records token requests and serves queued responses.
 */
class Token_Requesting_OAuth_Client extends AA_Creators_OAuth_Client {
    public $request_count = 0;
    public $last_url = '';
    public $last_body = '';
    public $last_content_type = '';
    public $responses = array();

    /**
     * @param array[] $token_responses List of token payload arrays
     *                                  (access_token, expires_in, ...).
     */
    public function __construct(array $token_responses) {
        foreach ($token_responses as $data) {
            $this->responses[] = array(
                'response' => array('code' => 200, 'message' => 'OK'),
                'body'     => json_encode($data),
            );
        }
    }

    protected function post_token_request($url, $body, $content_type) {
        $this->request_count++;
        $this->last_url = $url;
        $this->last_body = $body;
        $this->last_content_type = $content_type;

        if (empty($this->responses)) {
            return new WP_Error('no_response', 'No token response queued.');
        }

        return array_shift($this->responses);
    }
}

/**
 * HTTP transport that records the last request and returns a fixed response.
 */
class Recording_Http_Transport extends AA_Creators_API_Http_Transport {
    public $calls = array();
    private $response;

    public function __construct($oauth_client, $response) {
        parent::__construct($oauth_client);
        $this->response = $response;
    }

    public function get_call_count() {
        return count($this->calls);
    }

    protected function post_json($url, array $headers, $body) {
        $this->calls[] = array(
            'url'     => $url,
            'headers' => $headers,
            'body'    => $body,
        );
        return $this->response;
    }
}

/**
 * Transport stub that always returns a preconfigured result.
 */
class Stub_Transport extends AA_Creators_API_Transport {
    private $result;

    public function __construct($result) {
        parent::__construct();
        $this->result = $result;
    }

    public function get_items(array $asins, $marketplace_domain, $partner_tag) {
        return $this->result;
    }
}

/**
 * Sample Creators API GetItems response used across tests.
 */
function aa_creators_fixture_response() {
    return array(
        'itemResults' => array(
            'items' => array(
                array(
                    'asin'           => 'B08N5WRWNW',
                    'detailPageURL'  => 'https://www.amazon.com/dp/B08N5WRWNW?tag=test-20',
                    'images'         => array(
                        'primary' => array(
                            'large'  => array('url' => 'https://m.media-amazon.com/images/I/61large.jpg'),
                            'medium' => array('url' => 'https://m.media-amazon.com/images/I/61medium.jpg'),
                            'small'  => array('url' => 'https://m.media-amazon.com/images/I/61small.jpg'),
                        ),
                    ),
                    'itemInfo'       => array(
                        'title'     => array('displayValue' => 'Example Product Title'),
                        'features'  => array('displayValues' => array('F1', 'F2', 'F3', 'F4', 'F5')),
                        'byLineInfo' => array('brand' => array('displayValue' => 'ExampleBrand')),
                    ),
                    'offersV2'       => array(
                        'listings' => array(
                            array(
                                'isBuyBoxWinner' => true,
                                'price'          => array(
                                    'money'        => array('amount' => 59.49, 'currency' => 'USD', 'displayAmount' => '$59.49'),
                                    'savingBasis'  => array('money' => array('amount' => 69.99, 'currency' => 'USD', 'displayAmount' => '$69.99')),
                                ),
                            ),
                            array(
                                'isBuyBoxWinner' => false,
                                'price'          => array(
                                    'money'        => array('amount' => 49.99, 'currency' => 'USD', 'displayAmount' => '$49.99'),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );
}

/**
 * HTTP response wrapper helper.
 */
function aa_creators_http_response($code, $payload) {
    return array(
        'response' => array('code' => $code, 'message' => 200 === $code ? 'OK' : 'Error'),
        'body'     => json_encode($payload),
    );
}
