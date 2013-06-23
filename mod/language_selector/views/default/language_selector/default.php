<?php

$allowed = language_selector_get_allowed_translations();
$current_lang_id = get_current_language();

if (count($allowed) > 1) {

	foreach ($allowed as $lang_id) {
		$lang_name = elgg_echo($lang_id, array(), $lang_id);
		$action = false;

		$selected = false;
		if ($current_lang_id != $lang_id) {
			$action = elgg_add_action_tokens_to_url($vars['url'] . "action/language_selector/change?lang_id=" . $lang_id);
		} else {
			$selected = true;
		}

		elgg_register_menu_item('language-selector',  array(
			'name' => $lang_id,
			'href' => $action,
			'text' => $lang_name,
			'selected' => $selected,
		));
	}
}

echo elgg_view_menu('language-selector');
