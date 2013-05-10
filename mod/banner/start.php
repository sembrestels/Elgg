<?php

function banner_init() {
	
	global $CONFIG;
	register_translations($CONFIG->pluginspath . "banner/languages/");
	elgg_extend_view("page/elements/body", "banner/banner", 0);
	register_action("banner/banner",false,$CONFIG->pluginspath . "banner/actions/banner.php");
	elgg_extend_view('css','banner/css');
	
}

register_elgg_event_handler('init','system','banner_init');
