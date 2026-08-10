<?php
use PHPUnit\Framework\TestCase;

/**
 * Sanity and regression tests for the Amazon Associates PHP Snippets plugin.
 */
class ShortcodeTest extends TestCase {

    public function testShortcodeClassExists() {
        $this->assertTrue(class_exists('AA_Shortcodes'), 'AA_Shortcodes class should be loaded.');
    }

    public function testComparisonShortcodeOutput() {
        $atts = ['products' => 'B0GWHKHZRL,B0GMWYYRQL'];
        $output = AA_Shortcodes::amazon_comparison_shortcode($atts);
        
        // Assert grid container
        $this->assertStringContainsString('<div class="amazon-comparison-grid">', $output);
        $this->assertStringContainsString('data-asin="B0GWHKHZRL"', $output);
        $this->assertStringContainsString('data-asin="B0GMWYYRQL"', $output);
    }

    public function testFallbackPlaceholderImageAndBadge() {
        $product_box_html = aa_render_product_box('B0GWHKHZRL');
        
        // Assert placeholder image is used in fallback mode
        $this->assertStringContainsString('placeholder.png', $product_box_html);
        
        // Assert Fallback Mode badge notice is rendered
        $this->assertStringContainsString('aa-fallback-badge', $product_box_html);
        $this->assertStringContainsString('Fallback Mode', $product_box_html);
    }
}
