<?php
/**
 * Elgg upgrade script.
 *
 * This script triggers any necessary upgrades. If the site has been upgraded
 * to the most recent version of the code, no upgrades are run and the caches
 * are flushed. If you would prefer that this script is not accessible to others
 * after an upgrade, you can delete it. Future versions of Elgg will include a
 * new version of the script. Deleting the script is not a requirement and
 * leaving it behind does not affect the security of the site.
 *
 * Upgrades use a table {db_prefix}upgrade_lock as a mutex to prevent concurrent upgrades.
 *
 * The URL to forward to after upgrades are complete can be specified by setting $_GET['forward']
 * to a relative URL.
 *
 * @package    Elgg.Core
 * @subpackage Upgrade
 */

// we want to know if an error occurs
ini_set('display_errors', 1);

define('UPGRADING', 'upgrading');
require_once(dirname(__FILE__) . "/engine/start.php");

$site_url = elgg_get_config('url');
$site_host = parse_url($site_url, PHP_URL_HOST) . '/';

// turn any full in-site URLs into absolute paths
$forward_url = get_input('forward', '/admin', false);
$forward_url = str_replace(array($site_url, $site_host), '/', $forward_url);

if (strpos($forward_url, '/') !== 0) {
	$forward_url = '/' . $forward_url;
}

if (get_input('upgrade') == 'upgrade') {

	$upgrader = new Elgg_UpgradeService();
	$result = $upgrader->run();
	if ($result['failure'] == true) {
		register_error($result['reason']);
		forward($forward_url);
	}
} else {
	// if upgrading from < 1.8.0, check for the core view 'welcome' and bail if it's found.
	// see https://github.com/elgg/elgg/issues/3064
	// we're not checking the view itself because it's likely themes will override this view.
	// we're only concerned with core files.
	$welcome = dirname(__FILE__) . '/views/default/welcome.php';
	if (file_exists($welcome)) {
		elgg_set_viewtype('failsafe');
		// can't have pretty messages because we don't know the state of the views.
		$content = elgg_echo('upgrade:unable_to_upgrade_info');
		$title = elgg_echo('upgrade:unable_to_upgrade');
		
		echo elgg_view_page($title, $content);
		exit;
	}

	$vars = array(
		'forward' => $forward_url
	);

	echo elgg_view_page(elgg_echo('upgrading'), '', 'upgrade', $vars);
	exit;
}

forward($forward_url);
