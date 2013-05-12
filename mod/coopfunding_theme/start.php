<?php

elgg_register_event_handler('init', 'system', 'coopfunding_theme_init');

function coopfunding_theme_init() {
	elgg_extend_view('css/elgg', 'coopfunding_theme/css');
	elgg_extend_view('js/elgg', 'coopfunding_theme/js');
	elgg_extend_view('page/elements/title', 'coopfunding_theme/header', 0);
}
