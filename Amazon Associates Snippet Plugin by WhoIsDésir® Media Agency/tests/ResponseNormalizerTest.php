<?php
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Creators API response normalization layer.
 */
class ResponseNormalizerTest extends TestCase {

    /**
     * @var AA_Amazon_Response_Normalizer
     */
    private $normalizer;

    protected function setUp(): void {
        $this->normalizer = new AA_Amazon_Response_Normalizer();
    }

    public function testFullResponseNormalization() {
        $data = $this->normalizer->normalize(aa_creators_fixture_response(), 'B08N5WRWNW');

        $this->assertIsArray($data);
        $this->assertSame('B08N5WRWNW', $data['asin']);
        $this->assertSame('Example Product Title', $data['title']);
        $this->assertSame('https://www.amazon.com/dp/B08N5WRWNW?tag=test-20', $data['url']);
        $this->assertSame('https://m.media-amazon.com/images/I/61large.jpg', $data['image']);
        $this->assertSame('$59.49', $data['price']);
        $this->assertSame('$69.99', $data['saving_basis']);
        $this->assertFalse($data['is_prime']);
        $this->assertSame(array('F1', 'F2', 'F3', 'F4'), $data['features']);
        $this->assertSame('ExampleBrand', $data['brand']);
    }

    public function testCanonicalStructureKeys() {
        $data = $this->normalizer->normalize(aa_creators_fixture_response(), 'B08N5WRWNW');

        $this->assertSame(
            array('asin', 'title', 'url', 'image', 'price', 'saving_basis', 'is_prime', 'features', 'brand'),
            array_keys($data)
        );
    }

    public function testLargeImageMissingFallsBackToMedium() {
        $response = aa_creators_fixture_response();
        unset($response['itemResults']['items'][0]['images']['primary']['large']);

        $data = $this->normalizer->normalize($response, 'B08N5WRWNW');

        $this->assertSame('https://m.media-amazon.com/images/I/61medium.jpg', $data['image']);
    }

    public function testNoImagesReturnsEmptyString() {
        $response = aa_creators_fixture_response();
        unset($response['itemResults']['items'][0]['images']);

        $data = $this->normalizer->normalize($response, 'B08N5WRWNW');

        $this->assertSame('', $data['image']);
    }

    public function testPartialResponseUsesDefaults() {
        $response = array(
            'itemResults' => array(
                'items' => array(
                    array('asin' => 'B08N5WRWNW'),
                ),
            ),
        );

        $data = $this->normalizer->normalize($response, 'B08N5WRWNW');

        $this->assertSame('Amazon Product', $data['title']);
        $this->assertSame('', $data['url']);
        $this->assertSame('', $data['image']);
        $this->assertSame('', $data['price']);
        $this->assertSame('', $data['saving_basis']);
        $this->assertFalse($data['is_prime']);
        $this->assertSame(array(), $data['features']);
        $this->assertSame('', $data['brand']);
    }

    public function testFeaturesLimitedToFour() {
        $data = $this->normalizer->normalize(aa_creators_fixture_response(), 'B08N5WRWNW');
        $this->assertCount(4, $data['features']);
    }

    public function testBuyBoxWinnerListingPreferred() {
        $response = aa_creators_fixture_response();
        $listings = &$response['itemResults']['items'][0]['offersV2']['listings'];

        // Swap winner flag: make the second listing the buy box winner.
        $listings[0]['isBuyBoxWinner'] = false;
        $listings[1]['isBuyBoxWinner'] = true;
        $listings[1]['price']['savingBasis'] = array('money' => array('amount' => 59.99, 'currency' => 'USD', 'displayAmount' => '$59.99'));

        $data = $this->normalizer->normalize($response, 'B08N5WRWNW');

        $this->assertSame('$49.99', $data['price']);
        $this->assertSame('$59.99', $data['saving_basis']);
    }

    public function testItemNotFoundReturnsNull() {
        $response = aa_creators_fixture_response();
        $this->assertNull($this->normalizer->normalize($response, 'B09NOTHERE'));
    }

    public function testEmptyResponseReturnsNull() {
        $this->assertNull($this->normalizer->normalize(array(), 'B08N5WRWNW'));
    }

    public function testErrorsContainerWithoutItemReturnsNull() {
        $response = array(
            'errors' => array(
                array('code' => 'ItemNotAccessible', 'message' => 'Item is not accessible.'),
            ),
            'itemResults' => array(
                'items' => array(),
            ),
        );
        $this->assertNull($this->normalizer->normalize($response, 'B08N5WRWNW'));
    }

    public function testAsinMatchIsCaseInsensitive() {
        $data = $this->normalizer->normalize(aa_creators_fixture_response(), 'b08n5wrwnw');
        $this->assertIsArray($data);
        $this->assertSame('B08N5WRWNW', $data['asin']);
    }

    public function testItemsResultAliasContainerSupported() {
        $response = aa_creators_fixture_response();
        $response['itemsResult'] = $response['itemResults'];
        unset($response['itemResults']);

        $data = $this->normalizer->normalize($response, 'B08N5WRWNW');
        $this->assertSame('Example Product Title', $data['title']);
    }
}
