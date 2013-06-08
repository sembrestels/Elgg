<?php
/**
 * Elgg projects invite form
 *
 * @package Coopfunding
 * @subpackage Projects
 */

$project = $vars['entity'];
$forward_url = $project->getURL();
echo elgg_view('input/userpicker', array('name' => 'user_guid'));
echo '<div class="elgg-foot">';
echo elgg_view('input/hidden', array('name' => 'forward_url', 'value' => $forward_url));
echo elgg_view('input/hidden', array('name' => 'project_guid', 'value' => $project->guid));
echo elgg_view('input/submit', array('value' => elgg_echo('add')));
echo '</div>';
