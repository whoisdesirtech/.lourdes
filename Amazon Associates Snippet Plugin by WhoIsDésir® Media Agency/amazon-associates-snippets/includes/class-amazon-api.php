<?php
/**
 * Amazon Creators API Facade (compatibility alias)
 *
 * AA_Amazon_API is retained as a thin subclass of AA_Amazon_Provider for
 * backward compatibility. Existing code that constructs or references
 * AA_Amazon_API continues to work unchanged.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Amazon_API extends AA_Amazon_Provider {}
