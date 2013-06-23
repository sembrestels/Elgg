<?php

elgg_register_event_handler('plugins_boot', 'system', 'language_selector_plugins_boot');

function language_selector_plugins_boot(){

	if (!elgg_is_logged_in()) {

		// register hooks
		elgg_register_plugin_hook_handler("all", "plugin", "language_selector_invalidate_setting");

		elgg_register_event_handler("all", "plugin", "language_selector_invalidate_setting");

		// Default event handlers for plugin functionality
		elgg_register_event_handler('pagesetup', 'system', 'language_selector_pagesetup');
		elgg_register_event_handler('init', 'system', 'language_selector_init');
		elgg_register_event_handler('upgrade', 'system', 'language_selector_invalidate_setting');

		// actions
		elgg_register_action('language_selector/change', dirname(__FILE__) . '/actions/change.php', "public");

		if (!empty($_COOKIE['client_language'])) {
			// switched with language selector
			$new_lang = $_COOKIE['client_language'];
		} else {

			$browserlang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

			if (!empty($browserlang)) {
				// autodetect language
				if (elgg_get_plugin_setting("autodetect", "language_selector") == "yes") {
					$new_lang = $browserlang;
				}
			}
		}

		if(!empty($new_lang) && ($new_lang !== elgg_get_config('language'))) {
			// set new language
			elgg_set_config('language', $new_lang);
			reload_all_translations();
		}
	}

	elgg_extend_view("css/elgg", "language_selector/css");
}

function language_selector_invalidate_setting() {
	elgg_unset_plugin_setting("allowed_languages", "language_selector");
}

function language_selector_pagesetup() {
	elgg_extend_view("page/elements/topbar", "language_selector/default");
}

function language_selector_get_allowed_translations() {

	$configured_allowed = elgg_get_plugin_setting("allowed_languages", "language_selector");

	if (empty($configured_allowed)) {
		$allowed = "en";

		$installed_languages = get_installed_translations();

		$min_completeness = (int) elgg_get_plugin_setting("min_completeness", "language_selector");

		if ($min_completeness >= 0) {

			$completeness_function = "get_language_completeness";

			foreach ($installed_languages as $lang_id => $lang_description) {
				if ($lang_id != "en") {
					if (($completeness = $completeness_function($lang_id)) >= $min_completeness) {
						$allowed .= "," . $lang_id;

					}
				}
			}
		}

		elgg_set_plugin_setting("allowed_languages", implode(",", $allowed), "language_selector");
		$allowed = string_to_tag_array($allowed);
	} else {
		$allowed = string_to_tag_array($configured_allowed);
	}
	return $allowed;
}
