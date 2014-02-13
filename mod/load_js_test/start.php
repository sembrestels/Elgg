<?php

elgg_register_event_handler('init', 'system', 'load_js_test_init');

function load_js_test_init() {
	// Vanilla JS vendor
	elgg_register_js('vanilla-vendor-1', '/mod/load_js_test/vendors/vanilla_vendor_1.js');
	elgg_load_js('vanilla-vendor-1');
	elgg_load_js('vanilla-vendor-2');
	elgg_register_js('vanilla-vendor-2', '/mod/load_js_test/vendors/vanilla_vendor_2.js');

	// Vanilla JS simplecache view
	elgg_register_js('vanilla-view-1', elgg_get_simplecache_url('js', 'vanilla_view_1.js'));
	elgg_load_js('vanilla-view-1');
	elgg_load_js('vanilla-view-2');
	elgg_register_js('vanilla-view-2', elgg_get_simplecache_url('js', 'vanilla_view_2.js'));

	// AMD vendor
	elgg_register_js('amd-vendor-1', array(
		'src' => '/mod/load_js_test/vendors/amd_vendor_1.js',
	));
	elgg_load_js('amd-vendor-1');
	elgg_load_js('amd-vendor-2');
	elgg_register_js('amd-vendor-2', array(
		'src' => '/mod/load_js_test/vendors/amd_vendor_2.js',
	));
	
	// AMD shim vendor
	elgg_register_js('amd-shim-vendor-1', array(
		'src' => '/mod/load_js_test/vendors/amd_shim_vendor_1.js',
		'exports' => 'jQuery.fn.testShim',
		'deps' => array('jquery'),
	));
	elgg_load_js('amd-shim-vendor-1');
	elgg_load_js('amd-shim-vendor-2');
	elgg_register_js('amd-shim-vendor-2', array(
		'src' => '/mod/load_js_test/vendors/amd_shim_vendor_2.js',
		'exports' => 'testShim',
	));
	
	// AMD view
	elgg_load_js('amd_view');
}

