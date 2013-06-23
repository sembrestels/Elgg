<?php
/**
 * Better Hashed Passwords
 *
 * @package Lorea.Security
 * @subpackage BetterHash
 *
 */

elgg_register_event_handler('init', 'system', 'betterhash_init');

/**
 * Init betterhash plugin.
 */
function betterhash_init() {
	elgg_register_event_handler('create', 'user', 'betterhash_update_password');
	elgg_register_event_handler('update', 'user', 'betterhash_update_password');
	register_pam_handler('betterhash_login');
	elgg_register_plugin_hook_handler('usersettings:save', 'user', 'betterhash_users_settings_before', 100);
	elgg_register_plugin_hook_handler('usersettings:save', 'user', 'betterhash_users_settings_after', 900);
}

/**
 * Event triggered on user save. Remember login() also saves user each time.
 * 
 * @param string $event 'create' or 'update'.
 * @param string $object_type 'user'.
 * @param ElggUser $user User being saved.
 * @return boolean
 */
function betterhash_update_password($event, $object_type, ElggUser $user) {
	if (preg_match('/^\$6\$/', $user->password)) {
		// Already hashed using SHA-512.
		return true;
	}
	if (elgg_in_context('settings')) {
		// Skipping rehashing in settings save functions.
		return true;
	}
	
	$dbprefix = elgg_get_config('dbprefix');
	$better_hashed_password = betterhash_generate_user_password($user, $user->password);

	if ($object_type == 'user') {
		$query = "UPDATE {$dbprefix}users_entity
			SET password='$better_hashed_password'
			WHERE guid = $user->guid";

		if (!update_data($query)) {
			return false;
		}
	}
}

/**
 * Rehash the password for a user, currently uses SHA-512.
 *
 * @param ElggUser $user     The user this is being generated for.
 * @param string   $password Password in clear text
 *
 * @return string
 */
function betterhash_generate_user_password(ElggUser $user, $password) {
	return crypt($password, "\$6\$rounds=5000\$$user->salt\$");
}

/**
 * Event triggered before login
 * 
 * @param array $credentials Username and plain password
 * @return boolean
 */
function betterhash_login($credentials) {
	$user = get_user_by_username($credentials['username']);
	$password = $credentials['password'];

	$hashed_password = generate_user_password($user, $password);
	$rehashed_password = betterhash_generate_user_password($user, $hashed_password);

	if ($user->password == $rehashed_password) {
		return true;

	} elseif ($user->password == $hashed_password) {
		// Legacy hash support
		$user->password = $rehashed_password;
		$user->save();
	}
}

/**
 * Sets password hashed with md5 to make ($password == generate_user_password()) true
 * in users_settings_save()
 *
 * @see users_settings_save()
 *
 * @return void
 * @access private
 */
function betterhash_users_settings_before() {
	$current_password = get_input('current_password', null, false);
	$user_guid = get_input('guid');

	if (!$user_guid) {
		$user = elgg_get_logged_in_user_entity();
	} else {
		$user = get_entity($user_guid);
	}
	
	elgg_push_context('settings');

	$hashed_password = generate_user_password($user, $current_password);
	$rehashed_password = betterhash_generate_user_password($user, $hashed_password);

	if ($user->password == $rehashed_password) {
		// @todo This is dangerous and tricky
		$user->password = $hashed_password;
		$user->save();
	}

}

/**
 * Sets password rehashed with SHA-512
 *
 * @see users_settings_save()
 *
 * @return void
 * @access private
 */
function betterhash_users_settings_after() {
	elgg_pop_context();

	if ($user_guid = get_input('guid')) {
		get_entity($user_guid)->save();
	} else {
		elgg_get_logged_in_user_entity()->save();
	}
}
