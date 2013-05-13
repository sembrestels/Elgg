<?php
/**
 * Elgg footer
 * The standard HTML footer that displays across the site
 *
 * @package Elgg
 * @subpackage Core
 *
 */

$site = elgg_get_site_entity();
echo "<a class=\"coopfunding-footer-title mts\" href=\"$site->url\">".
		"<span>$site->name</span> $site->description".
		"</a>";

$footer = elgg_get_entities(array(
	'type' => 'object',
	'subtype' => 'footer',
	'limit' => 1,
));

echo elgg_view('output/longtext', array(
	'value' => current($footer)->description,
	'style' => 'display: inline-block',
));

echo elgg_view_menu('footer', array('sort_by' => 'priority', 'class' => 'elgg-menu-hz'));

$cic_img  = elgg_get_site_url() . "mod/coopfunding_theme/_graphics/cic.png";
$casx_img = elgg_get_site_url() . "mod/coopfunding_theme/_graphics/casx.png";

echo '<div class="mts clearfloat float-alt">';
echo elgg_view('output/url', array(
	'href' => 'http://cooperativa.cat',
	'text' => "<img src=\"$cic_img\" alt=\"Powered by Cooperativa Integral Catalana\" height=\"50\" />",
	'class' => '',
	'is_trusted' => true,
));
echo elgg_view('output/url', array(
	'href' => 'http://casx.cat',
	'text' => "<img src=\"$casx_img\" alt=\"Powered by CASX\" height=\"50\" />",
	'class' => '',
	'is_trusted' => true,
));
echo '</div>';
