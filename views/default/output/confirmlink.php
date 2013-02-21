<?php
/**
 * Elgg confirmation link
 * A link that displays a confirmation dialog before it executes
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars['text']        The text of the link
 * @uses $vars['href']        The address
 * @uses $vars['title']       The title text (defaults to confirm text)
 * @uses $vars['confirm']     The dialog text
 * @uses $vars['encode_text'] Run $vars['text'] through htmlspecialchars() (false)
 */

$vars['data-confirm'] = elgg_extract('confirm', $vars, elgg_echo('question:areyousure'));
$vars['data-confirm'] = addslashes($vars['data-confirm']);
$encode = elgg_extract('encode_text', $vars, false);

// always generate missing action tokens
$vars['href'] = elgg_add_action_tokens_to_url(elgg_normalize_url($vars['href']), true);

$text = elgg_extract('text', $vars, '');
if ($encode) {
	$text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
}

if (!isset($vars['title']) && isset($vars['confirm'])) {
	$vars['title'] = $vars['data-confirm'];
}

if (isset($vars['class'])) {
	if (is_array($vars['class'])) {
		$vars['class'] = implode(" ", $vars['class']);
	}
	$vars['class'] .= ' elgg-requires-confirmation';
} else {
	$vars['class'] = 'elgg-requires-confirmation';
}

// Deprecate rel="toggle" and rel="popup"
foreach (array('toggle', 'popup') as $rel) {
	if (preg_match("/$rel/i", $vars['rel'])) {
		$vars['rel'] = preg_replace("/$rel/i", '', $vars['rel']);
		$vars['class'] .= " elgg-$rel";
		elgg_deprecated_notice("Use class=\"elgg-$rel\" instead of rel=\"$rel\"", 1.9);
	}
}

unset($vars['encode_text']);
unset($vars['text']);
unset($vars['confirm']);
unset($vars['is_trusted']);

$attributes = elgg_format_attributes($vars);
echo "<a $attributes>$text</a>";
