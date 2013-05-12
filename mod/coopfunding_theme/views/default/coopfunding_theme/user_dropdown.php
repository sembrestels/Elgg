<?php
/**
 * Elgg drop-down user
 */

$user = elgg_get_logged_in_user_entity();

if (!$user) {
	return true;
}

$body = elgg_view_menu('topbar', array('sort_by' => 'priority', array('elgg-menu-hz')));

?>
<div id="login-dropdown" class="user-dropdown">
	<?php 
		echo elgg_view('output/url', array(
			'href' => '#login-dropdown-box',
			'rel' => 'popup',
			'class' => 'elgg-button elgg-button-dropdown',
			'text' => $user->name,
		)); 
		echo elgg_view_module('dropdown', '', $body, array(
			'id' => 'login-dropdown-box',
			'class' => 'user-dropdown-box',
		)); 
	?>
</div>
