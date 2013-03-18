<?php
/**
 * Elgg projects plugin
 *
 * @package ElggGroups
 */

elgg_register_event_handler('init', 'system', 'projects_init');

// Ensure this runs after other plugins
elgg_register_event_handler('init', 'system', 'projects_fields_setup', 10000);

/**
 * Initialize the projects plugin.
 */
function projects_init() {

	elgg_register_library('elgg:projects', elgg_get_plugins_path() . 'projects/lib/projects.php');

	// register project entities for search
	elgg_register_entity_type('group', 'project');

	// Set up the menu
	$item = new ElggMenuItem('projects', elgg_echo('projects'), 'projects/all');
	elgg_register_menu_item('site', $item);

	// Register a page handler, so we can have nice URLs
	elgg_register_page_handler('projects', 'projects_page_handler');

	// Register URL handlers for projects
	elgg_register_entity_url_handler('group', 'project', 'projects_url');
	elgg_register_plugin_hook_handler('entity:icon:url', 'group', 'projects_icon_url_override');

	// Register an icon handler for projects
	elgg_register_page_handler('projecticon', 'projects_icon_handler');

	// Register some actions
	$action_base = elgg_get_plugins_path() . 'projects/actions/projects';
	elgg_register_action("projects/edit", "$action_base/edit.php");
	elgg_register_action("projects/delete", "$action_base/delete.php");
	elgg_register_action("projects/featured", "$action_base/featured.php", 'admin');

	$action_base .= '/membership';
	elgg_register_action("projects/invite", "$action_base/invite.php");
	elgg_register_action("projects/join", "$action_base/join.php");
	elgg_register_action("projects/leave", "$action_base/leave.php");
	elgg_register_action("projects/remove", "$action_base/remove.php");
	elgg_register_action("projects/killrequest", "$action_base/delete_request.php");
	elgg_register_action("projects/killinvitation", "$action_base/delete_invite.php");
	elgg_register_action("projects/addtoproject", "$action_base/add.php");

	// Add some widgets
	elgg_register_widget_type('a_users_projects', elgg_echo('projects:widget:membership'), elgg_echo('projects:widgets:description'));

	// add project activity tool option
	add_group_tool_option('activity', elgg_echo('projects:enableactivity'), true);
	elgg_extend_view('projects/tool_latest', 'projects/profile/activity_module');

	// add link to owner block
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'projects_activity_owner_block_menu');

	// project entity menu
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'projects_entity_menu_setup');
	
	// project user hover menu	
	elgg_register_plugin_hook_handler('register', 'menu:user_hover', 'projects_user_entity_menu_setup');

	// delete and edit annotations for topic replies
	elgg_register_plugin_hook_handler('register', 'menu:annotation', 'projects_annotation_menu_setup');

	//extend some views
	elgg_extend_view('css/elgg', 'projects/css');
	elgg_extend_view('js/elgg', 'projects/js');

	// Access permissions
	elgg_register_plugin_hook_handler('access:collections:write', 'all', 'projects_write_acl_plugin_hook');
	//elgg_register_plugin_hook_handler('access:collections:read', 'all', 'projects_read_acl_plugin_hook');

	// Register profile menu hook
	elgg_register_plugin_hook_handler('profile_menu', 'profile', 'forum_profile_menu');
	elgg_register_plugin_hook_handler('profile_menu', 'profile', 'activity_profile_menu');

	// allow ecml in discussion and profiles
	elgg_register_plugin_hook_handler('get_views', 'ecml', 'projects_ecml_views_hook');
	elgg_register_plugin_hook_handler('get_views', 'ecml', 'projectprofile_ecml_views_hook');

	// Register a handler for create projects
	elgg_register_event_handler('create', 'project', 'projects_create_event_listener');

	// Register a handler for delete projects
	elgg_register_event_handler('delete', 'project', 'projects_delete_event_listener');
	
	elgg_register_event_handler('join', 'project', 'projects_user_join_event_listener');
	elgg_register_event_handler('leave', 'project', 'projects_user_leave_event_listener');
	elgg_register_event_handler('pagesetup', 'system', 'projects_setup_sidebar_menus');

	elgg_register_plugin_hook_handler('access:collections:add_user', 'collection', 'projects_access_collection_override');

	elgg_register_event_handler('upgrade', 'system', 'projects_run_upgrades');
}

/**
 * This function loads a set of default fields into the profile, then triggers
 * a hook letting other plugins to edit add and delete fields.
 *
 * Note: This is a system:init event triggered function and is run at a super
 * low priority to guarantee that it is called after all other plugins have
 * initialized.
 */
function projects_fields_setup() {

	$profile_defaults = array(
		'description' => 'longtext',
		'briefdescription' => 'text',
		'interests' => 'tags',
		//'website' => 'url',
	);

	$profile_defaults = elgg_trigger_plugin_hook('profile:fields', 'project', NULL, $profile_defaults);

	elgg_set_config('project', $profile_defaults);

	// register any tag metadata names
	foreach ($profile_defaults as $name => $type) {
		if ($type == 'tags') {
			elgg_register_tag_metadata_name($name);

			// only shows up in search but why not just set this in en.php as doing it here
			// means you cannot override it in a plugin
			add_translation(get_current_language(), array("tag_names:$name" => elgg_echo("projects:$name")));
		}
	}
}

/**
 * Configure the projects sidebar menu. Triggered on page setup
 *
 */
function projects_setup_sidebar_menus() {

	// Get the page owner entity
	$page_owner = elgg_get_page_owner_entity();

	if (elgg_in_context('project_profile')) {
		if (elgg_is_logged_in() && $page_owner->canEdit() && !$page_owner->isPublicMembership()) {
			$url = elgg_get_site_url() . "projects/requests/{$page_owner->getGUID()}";

			$count = elgg_get_entities_from_relationship(array(
				'type' => 'user',
				'relationship' => 'membership_request',
				'relationship_guid' => $page_owner->getGUID(),
				'inverse_relationship' => true,
				'count' => true,
			));

			if ($count) {
				$text = elgg_echo('projects:membershiprequests:pending', array($count));
			} else {
				$text = elgg_echo('projects:membershiprequests');
			}

			elgg_register_menu_item('page', array(
				'name' => 'membership_requests',
				'text' => $text,
				'href' => $url,
			));
		}
	}
	if (elgg_get_context() == 'projects' && !elgg_instanceof($page_owner, 'project')) {
		elgg_register_menu_item('page', array(
			'name' => 'projects:all',
			'text' => elgg_echo('projects:all'),
			'href' => 'projects/all',
		));

		$user = elgg_get_logged_in_user_entity();
		if ($user) {
			$url =  "projects/owner/$user->username";
			$item = new ElggMenuItem('projects:owned', elgg_echo('projects:owned'), $url);
			elgg_register_menu_item('page', $item);
			
			$url = "projects/member/$user->username";
			$item = new ElggMenuItem('projects:member', elgg_echo('projects:yours'), $url);
			elgg_register_menu_item('page', $item);

			$url = "projects/invitations/$user->username";
			$invitations = projects_get_invited_projects($user->getGUID());
			if (is_array($invitations) && !empty($invitations)) {
				$invitation_count = count($invitations);
				$text = elgg_echo('projects:invitations:pending', array($invitation_count));
			} else {
				$text = elgg_echo('projects:invitations');
			}

			$item = new ElggMenuItem('projects:user:invites', $text, $url);
			elgg_register_menu_item('page', $item);
		}
	}
}

/**
 * Groups page handler
 *
 * URLs take the form of
 *  All projects:           projects/all
 *  User's owned projects:  projects/owner/<username>
 *  User's member projects: projects/member/<username>
 *  Group profile:        projects/profile/<guid>/<title>
 *  New project:            projects/add/<guid>
 *  Edit project:           projects/edit/<guid>
 *  Group invitations:    projects/invitations/<username>
 *  Invite to project:      projects/invite/<guid>
 *  Membership requests:  projects/requests/<guid>
 *  Group activity:       projects/activity/<guid>
 *  Group members:        projects/members/<guid>
 *
 * @param array $page Array of url segments for routing
 * @return bool
 */
function projects_page_handler($page) {

	// forward old profile urls
	if (is_numeric($page[0])) {
		$project = get_entity($page[0]);
		if (elgg_instanceof($project, 'project', '', 'ElggGroup')) {
			system_message(elgg_echo('changebookmark'));
			forward($project->getURL());
		}
	}
	
	elgg_load_library('elgg:projects');

	if (!isset($page[0])) {
		$page[0] = 'all';
	}

	elgg_push_breadcrumb(elgg_echo('projects'), "projects/all");

	switch ($page[0]) {
		case 'all':
			projects_handle_all_page();
			break;
		case 'search':
			projects_search_page();
			break;
		case 'owner':
			projects_handle_owned_page();
			break;
		case 'member':
			set_input('username', $page[1]);
			projects_handle_mine_page();
			break;
		case 'invitations':
			set_input('username', $page[1]);
			projects_handle_invitations_page();
			break;
		case 'add':
			projects_handle_edit_page('add');
			break;
		case 'edit':
			projects_handle_edit_page('edit', $page[1]);
			break;
		case 'profile':
			projects_handle_profile_page($page[1]);
			break;
		case 'activity':
			projects_handle_activity_page($page[1]);
			break;
		case 'members':
			projects_handle_members_page($page[1]);
			break;
		case 'invite':
			projects_handle_invite_page($page[1]);
			break;
		case 'requests':
			projects_handle_requests_page($page[1]);
			break;
		default:
			return false;
	}
	return true;
}

/**
 * Handle project icons.
 *
 * @param array $page
 * @return void
 */
function projects_icon_handler($page) {

	// The username should be the file we're getting
	if (isset($page[0])) {
		set_input('project_guid', $page[0]);
	}
	if (isset($page[1])) {
		set_input('size', $page[1]);
	}
	// Include the standard profile index
	$plugin_dir = elgg_get_plugins_path();
	include("$plugin_dir/projects/icon.php");
	return true;
}

/**
 * Populates the ->getUrl() method for project objects
 *
 * @param ElggEntity $entity File entity
 * @return string File URL
 */
function projects_url($entity) {
	$title = elgg_get_friendly_title($entity->name);

	return "projects/profile/{$entity->guid}/$title";
}

/**
 * Override the default entity icon for projects
 *
 * @return string Relative URL
 */
function projects_icon_url_override($hook, $type, $returnvalue, $params) {
	/* @var ElggGroup $project */
	$project = $params['entity'];
	$size = $params['size'];

	$icontime = $project->icontime;
	// handle missing metadata (pre 1.7 installations)
	if (null === $icontime) {
		$file = new ElggFile();
		$file->owner_guid = $project->owner_guid;
		$file->setFilename("projects/" . $project->guid . "large.jpg");
		$icontime = $file->exists() ? time() : 0;
		create_metadata($project->guid, 'icontime', $icontime, 'integer', $project->owner_guid, ACCESS_PUBLIC);
	}
	if ($icontime) {
		// return thumbnail
		return "projecticon/$project->guid/$size/$icontime.jpg";
	}

	return "mod/projects/graphics/default{$size}.gif";
}

/**
 * Add owner block link
 */
function projects_activity_owner_block_menu($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'project')) {
		if ($params['entity']->activity_enable != "no") {
			$url = "projects/activity/{$params['entity']->guid}";
			$item = new ElggMenuItem('activity', elgg_echo('projects:activity'), $url);
			$return[] = $item;
		}
	}

	return $return;
}

/**
 * Add links/info to entity menu particular to project entities
 */
function projects_entity_menu_setup($hook, $type, $return, $params) {
	if (elgg_in_context('widgets')) {
		return $return;
	}

	$entity = $params['entity'];
	$handler = elgg_extract('handler', $params, false);
	if ($handler != 'projects') {
		return $return;
	}

	foreach ($return as $index => $item) {
		if (in_array($item->getName(), array('access', 'likes', 'edit', 'delete'))) {
			unset($return[$index]);
		}
	}

	// membership type
	$membership = $entity->membership;
	if ($membership == ACCESS_PUBLIC) {
		$mem = elgg_echo("projects:open");
	} else {
		$mem = elgg_echo("projects:closed");
	}
	$options = array(
		'name' => 'membership',
		'text' => $mem,
		'href' => false,
		'priority' => 100,
	);
	$return[] = ElggMenuItem::factory($options);

	// number of members
	$num_members = get_group_members($entity->guid, 10, 0, 0, true);
	$members_string = elgg_echo('projects:member');
	$options = array(
		'name' => 'members',
		'text' => $num_members . ' ' . $members_string,
		'href' => false,
		'priority' => 200,
	);
	$return[] = ElggMenuItem::factory($options);

	// feature link
	if (elgg_is_admin_logged_in()) {
		if ($entity->featured_project == "yes") {
			$url = "action/projects/featured?project_guid={$entity->guid}&action_type=unfeature";
			$wording = elgg_echo("projects:makeunfeatured");
		} else {
			$url = "action/projects/featured?project_guid={$entity->guid}&action_type=feature";
			$wording = elgg_echo("projects:makefeatured");
		}
		$options = array(
			'name' => 'feature',
			'text' => $wording,
			'href' => $url,
			'priority' => 300,
			'is_action' => true
		);
		$return[] = ElggMenuItem::factory($options);
	}

	return $return;
}

/**
 * Add a remove user link to user hover menu when the page owner is a project
 */
function projects_user_entity_menu_setup($hook, $type, $return, $params) {
	if (elgg_is_logged_in()) {
		$project = elgg_get_page_owner_entity();
		
		// Check for valid project
		if (!elgg_instanceof($project, 'project')) {
			return $return;
		}
	
		$entity = $params['entity'];
		
		// Make sure we have a user and that user is a member of the project
		if (!elgg_instanceof($entity, 'user') || !$project->isMember($entity)) {
			return $return;
		}

		// Add remove link if we can edit the project, and if we're not trying to remove the project owner
		if ($project->canEdit() && $project->getOwnerGUID() != $entity->guid) {
			$remove = elgg_view('output/confirmlink', array(
				'href' => "action/projects/remove?user_guid={$entity->guid}&project_guid={$project->guid}",
				'text' => elgg_echo('projects:removeuser'),
			));

			$options = array(
				'name' => 'removeuser',
				'text' => $remove,
				'priority' => 999,
			);
			$return[] = ElggMenuItem::factory($options);
		} 
	}

	return $return;
}

/**
 * Add edit and delete links for forum replies
 */
function projects_annotation_menu_setup($hook, $type, $return, $params) {
	if (elgg_in_context('widgets')) {
		return $return;
	}
	
	$annotation = $params['annotation'];

	if ($annotation->name != 'project_topic_post') {
		return $return;
	}

	if ($annotation->canEdit()) {
		$url = elgg_http_add_url_query_elements('action/discussion/reply/delete', array(
			'annotation_id' => $annotation->id,
		));

		$options = array(
			'name' => 'delete',
			'href' => $url,
			'text' => "<span class=\"elgg-icon elgg-icon-delete\"></span>",
			'confirm' => elgg_echo('deleteconfirm'),
			'encode_text' => false
		);
		$return[] = ElggMenuItem::factory($options);

		$url = elgg_http_add_url_query_elements('discussion', array(
			'annotation_id' => $annotation->id,
		));

		$options = array(
			'name' => 'edit',
			'href' => "#edit-annotation-$annotation->id",
			'text' => elgg_echo('edit'),
			'encode_text' => false,
			'rel' => 'toggle',
		);
		$return[] = ElggMenuItem::factory($options);
	}

	return $return;
}

/**
 * Groups created so create an access list for it
 */
function projects_create_event_listener($event, $object_type, $object) {
	$ac_name = elgg_echo('projects:project') . ": " . $object->name;
	$project_id = create_access_collection($ac_name, $object->guid);
	if ($project_id) {
		$object->group_acl = $project_id;
	} else {
		// delete project if access creation fails
		return false;
	}

	return true;
}

/**
 * Hook to listen to read access control requests and return all the projects you are a member of.
 */
function projects_read_acl_plugin_hook($hook, $entity_type, $returnvalue, $params) {
	//error_log("READ: " . var_export($returnvalue));
	$user = elgg_get_logged_in_user_entity();
	if ($user) {
		// Not using this because of recursion.
		// Joining a project automatically add user to ACL,
		// So just see if they're a member of the ACL.
		//$membership = get_users_membership($user->guid);

		$members = get_members_of_access_collection($project->group_acl);
		print_r($members);
		exit;

		if ($membership) {
			foreach ($membership as $project)
				$returnvalue[$user->guid][$project->group_acl] = elgg_echo('projects:project') . ": " . $project->name;
			return $returnvalue;
		}
	}
}

/**
 * Return the write access for the current project if the user has write access to it.
 */
function projects_write_acl_plugin_hook($hook, $entity_type, $returnvalue, $params) {
	$page_owner = elgg_get_page_owner_entity();
	$user_guid = $params['user_id'];
	$user = get_entity($user_guid);
	if (!$user) {
		return $returnvalue;
	}

	// only insert project access for current project
	if ($page_owner instanceof ElggGroup) {
		if ($page_owner->canWriteToContainer($user_guid)) {
			$returnvalue[$page_owner->group_acl] = elgg_echo('projects:project') . ': ' . $page_owner->name;

			unset($returnvalue[ACCESS_FRIENDS]);
		}
	} else {
		// if the user owns the project, remove all access collections manually
		// this won't be a problem once the project itself owns the acl.
		$projects = elgg_get_entities_from_relationship(array(
					'relationship' => 'member',
					'relationship_guid' => $user_guid,
					'inverse_relationship' => FALSE,
					'limit' => false
				));

		if ($projects) {
			foreach ($projects as $project) {
				unset($returnvalue[$project->group_acl]);
			}
		}
	}

	return $returnvalue;
}

/**
 * Groups deleted, so remove access lists.
 */
function projects_delete_event_listener($event, $object_type, $object) {
	delete_access_collection($object->group_acl);

	return true;
}

/**
 * Listens to a project join event and adds a user to the project's access control
 *
 */
function projects_user_join_event_listener($event, $object_type, $object) {

	$project = $object['project'];
	$user = $object['user'];
	$acl = $project->group_acl;

	add_user_to_access_collection($user->guid, $acl);

	return true;
}

/**
 * Make sure users are added to the access collection
 */
function projects_access_collection_override($hook, $entity_type, $returnvalue, $params) {
	if (isset($params['collection'])) {
		if (elgg_instanceof(get_entity($params['collection']->owner_guid), 'project')) {
			return true;
		}
	}
}

/**
 * Listens to a project leave event and removes a user from the project's access control
 *
 */
function projects_user_leave_event_listener($event, $object_type, $object) {

	$project = $object['project'];
	$user = $object['user'];
	$acl = $project->group_acl;

	remove_user_from_access_collection($user->guid, $acl);

	return true;
}

/**
 * Grabs projects by invitations
 * Have to override all access until there's a way override access to getter functions.
 *
 * @param int  $user_guid    The user's guid
 * @param bool $return_guids Return guids rather than ElggGroup objects
 *
 * @return array ElggGroups or guids depending on $return_guids
 */
function projects_get_invited_projects($user_guid, $return_guids = FALSE) {
	$ia = elgg_set_ignore_access(TRUE);
	$projects = elgg_get_entities_from_relationship(array(
		'relationship' => 'invited',
		'relationship_guid' => $user_guid,
		'inverse_relationship' => TRUE,
		'limit' => 0,
	));
	elgg_set_ignore_access($ia);

	if ($return_guids) {
		$guids = array();
		foreach ($projects as $project) {
			$guids[] = $project->getGUID();
		}

		return $guids;
	}

	return $projects;
}

/**
 * Join a user to a project, add river event, clean-up invitations
 *
 * @param ElggGroup $project
 * @param ElggUser  $user
 * @return bool
 */
function projects_join_project($project, $user) {

	// access ignore so user can be added to access collection of invisible project
	$ia = elgg_set_ignore_access(TRUE);
	$result = $project->join($user);
	elgg_set_ignore_access($ia);
	
	if ($result) {
		// flush user's access info so the collection is added
		get_access_list($user->guid, 0, true);

		// Remove any invite or join request flags
		remove_entity_relationship($project->guid, 'invited', $user->guid);
		remove_entity_relationship($user->guid, 'membership_request', $project->guid);

		add_to_river('river/relationship/member/create', 'join', $user->guid, $project->guid);

		return true;
	}

	return false;
}

/**
 * Function to use on projects for access. It will house private, loggedin, public,
 * and the project itself. This is when you don't want other projects or access lists
 * in the access options available.
 *
 * @return array
 */
function project_access_options($project) {
	$access_array = array(
		ACCESS_PRIVATE => 'private',
		ACCESS_LOGGED_IN => 'logged in users',
		ACCESS_PUBLIC => 'public',
		$project->group_acl => elgg_echo('projects:acl', array($project->name)),
	);
	return $access_array;
}

function activity_profile_menu($hook, $entity_type, $return_value, $params) {

	if ($params['owner'] instanceof ElggGroup) {
		$return_value[] = array(
			'text' => elgg_echo('Activity'),
			'href' => "projects/activity/{$params['owner']->getGUID()}"
		);
	}
	return $return_value;
}

/**
 * Parse ECML on project discussion views
 */
function projects_ecml_views_hook($hook, $entity_type, $return_value, $params) {
	$return_value['forum/viewposts'] = elgg_echo('projects:ecml:discussion');

	return $return_value;
}

/**
 * Parse ECML on project profiles
 */
function projectprofile_ecml_views_hook($hook, $entity_type, $return_value, $params) {
	$return_value['projects/projectprofile'] = elgg_echo('projects:ecml:projectprofile');

	return $return_value;
}



/**
 * Discussion
 *
 */

elgg_register_event_handler('init', 'system', 'discussion_init');

/**
 * Initialize the discussion component
 */
function discussion_init() {

	elgg_register_library('elgg:discussion', elgg_get_plugins_path() . 'projects/lib/discussion.php');

	elgg_register_page_handler('discussion', 'discussion_page_handler');
	elgg_register_page_handler('forum', 'discussion_forum_page_handler');

	elgg_register_entity_url_handler('object', 'projectforumtopic', 'discussion_override_topic_url');

	// commenting not allowed on discussion topics (use a different annotation)
	elgg_register_plugin_hook_handler('permissions_check:comment', 'object', 'discussion_comment_override');
	
	$action_base = elgg_get_plugins_path() . 'projects/actions/discussion';
	elgg_register_action('discussion/save', "$action_base/save.php");
	elgg_register_action('discussion/delete', "$action_base/delete.php");
	elgg_register_action('discussion/reply/save', "$action_base/reply/save.php");
	elgg_register_action('discussion/reply/delete', "$action_base/reply/delete.php");

	// add link to owner block
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'discussion_owner_block_menu');

	// Register for search.
	elgg_register_entity_type('object', 'projectforumtopic');

	// because replies are not comments, need of our menu item
	elgg_register_plugin_hook_handler('register', 'menu:river', 'discussion_add_to_river_menu');

	// add the forum tool option
	add_group_tool_option('forum', elgg_echo('projects:enableforum'), true);
	elgg_extend_view('projects/tool_latest', 'discussion/project_module');

	// notifications
	register_notification_object('object', 'projectforumtopic', elgg_echo('discussion:notification:topic:subject'));
	elgg_register_plugin_hook_handler('notify:entity:message', 'object', 'projectforumtopic_notify_message');
	elgg_register_event_handler('create', 'annotation', 'discussion_reply_notifications');
	elgg_register_plugin_hook_handler('notify:annotation:message', 'project_topic_post', 'discussion_create_reply_notification');
}

/**
 * Exists for backwards compatibility for Elgg 1.7
 */
function discussion_forum_page_handler($page) {
	switch ($page[0]) {
		case 'topic':
			header('Status: 301 Moved Permanently');
			forward("/discussion/view/{$page[1]}/{$page[2]}");
			break;
		default:
			return false;
	}
}

/**
 * Discussion page handler
 *
 * URLs take the form of
 *  All topics in site:    discussion/all
 *  List topics in forum:  discussion/owner/<guid>
 *  View discussion topic: discussion/view/<guid>
 *  Add discussion topic:  discussion/add/<guid>
 *  Edit discussion topic: discussion/edit/<guid>
 *
 * @param array $page Array of url segments for routing
 * @return bool
 */
function discussion_page_handler($page) {

	elgg_load_library('elgg:discussion');

	if (!isset($page[0])) {
		$page[0] = 'all';
	}

	elgg_push_breadcrumb(elgg_echo('discussion'), 'discussion/all');

	switch ($page[0]) {
		case 'all':
			discussion_handle_all_page();
			break;
		case 'owner':
			discussion_handle_list_page($page[1]);
			break;
		case 'add':
			discussion_handle_edit_page('add', $page[1]);
			break;
		case 'edit':
			discussion_handle_edit_page('edit', $page[1]);
			break;
		case 'view':
			discussion_handle_view_page($page[1]);
			break;
		default:
			return false;
	}
	return true;
}

/**
 * Override the discussion topic url
 *
 * @param ElggObject $entity Discussion topic
 * @return string
 */
function discussion_override_topic_url($entity) {
	return 'discussion/view/' . $entity->guid . '/' . elgg_get_friendly_title($entity->title);
}

/**
 * We don't want people commenting on topics in the river
 */
function discussion_comment_override($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'object', 'projectforumtopic')) {
		return false;
	}
}

/**
 * Add owner block link
 */
function discussion_owner_block_menu($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'project')) {
		if ($params['entity']->forum_enable != "no") {
			$url = "discussion/owner/{$params['entity']->guid}";
			$item = new ElggMenuItem('discussion', elgg_echo('discussion:project'), $url);
			$return[] = $item;
		}
	}

	return $return;
}

/**
 * Add the reply button for the river
 */
function discussion_add_to_river_menu($hook, $type, $return, $params) {
	if (elgg_is_logged_in() && !elgg_in_context('widgets')) {
		$item = $params['item'];
		$object = $item->getObjectEntity();
		if (elgg_instanceof($object, 'object', 'projectforumtopic')) {
			if ($item->annotation_id == 0) {
				$project = $object->getContainerEntity();
				if ($project && ($project->canWriteToContainer() || elgg_is_admin_logged_in())) {
					$options = array(
						'name' => 'reply',
						'href' => "#projects-reply-$object->guid",
						'text' => elgg_view_icon('speech-bubble'),
						'title' => elgg_echo('reply:this'),
						'rel' => 'toggle',
						'priority' => 50,
					);
					$return[] = ElggMenuItem::factory($options);
				}
			}
		}
	}

	return $return;
}

/**
 * Create discussion notification body
 *
 * @todo namespace method with 'discussion'
 *
 * @param string $hook
 * @param string $type
 * @param string $message
 * @param array  $params
 */
function projectforumtopic_notify_message($hook, $type, $message, $params) {
	$entity = $params['entity'];
	$to_entity = $params['to_entity'];
	$method = $params['method'];

	if (($entity instanceof ElggEntity) && ($entity->getSubtype() == 'projectforumtopic')) {
		$descr = $entity->description;
		$title = $entity->title;
		$url = $entity->getURL();
		$owner = $entity->getOwnerEntity();
		$project = $entity->getContainerEntity();

		return elgg_echo('projects:notification', array(
			$owner->name,
			$project->name,
			$entity->title,
			$entity->description,
			$entity->getURL()
		));
	}

	return null;
}

/**
 * Create discussion reply notification body
 *
 * @param string $hook
 * @param string $type
 * @param string $message
 * @param array  $params
 */
function discussion_create_reply_notification($hook, $type, $message, $params) {
	$reply = $params['annotation'];
	$method = $params['method'];
	$topic = $reply->getEntity();
	$poster = $reply->getOwnerEntity();
	$project = $topic->getContainerEntity();

	return elgg_echo('discussion:notification:reply:body', array(
		$poster->name,
		$topic->title,
		$project->name,
		$reply->value,
		$topic->getURL(),
	));
}

/**
 * Catch reply to discussion topic and generate notifications
 *
 * @todo this will be replaced in Elgg 1.9 and is a clone of object_notifications()
 *
 * @param string         $event
 * @param string         $type
 * @param ElggAnnotation $annotation
 * @return void
 */
function discussion_reply_notifications($event, $type, $annotation) {
	global $CONFIG, $NOTIFICATION_HANDLERS;

	if ($annotation->name !== 'project_topic_post') {
		return;
	}

	// Have we registered notifications for this type of entity?
	$object_type = 'object';
	$object_subtype = 'projectforumtopic';

	$topic = $annotation->getEntity();
	if (!$topic) {
		return;
	}

	$poster = $annotation->getOwnerEntity();
	if (!$poster) {
		return;
	}

	if (isset($CONFIG->register_objects[$object_type][$object_subtype])) {
		$subject = $CONFIG->register_objects[$object_type][$object_subtype];
		$string = $subject . ": " . $topic->getURL();

		// Get users interested in content from this person and notify them
		// (Person defined by container_guid so we can also subscribe to projects if we want)
		foreach ($NOTIFICATION_HANDLERS as $method => $foo) {
			$interested_users = elgg_get_entities_from_relationship(array(
				'relationship' => 'notify' . $method,
				'relationship_guid' => $topic->getContainerGUID(),
				'inverse_relationship' => true,
				'type' => 'user',
				'limit' => 0,
			));

			if ($interested_users && is_array($interested_users)) {
				foreach ($interested_users as $user) {
					if ($user instanceof ElggUser && !$user->isBanned()) {
						if (($user->guid != $poster->guid) && has_access_to_entity($topic, $user) && $topic->access_id != ACCESS_PRIVATE) {
							$body = elgg_trigger_plugin_hook('notify:annotation:message', $annotation->getSubtype(), array(
								'annotation' => $annotation,
								'to_entity' => $user,
								'method' => $method), $string);
							if (empty($body) && $body !== false) {
								$body = $string;
							}
							if ($body !== false) {
								notify_user($user->guid, $topic->getContainerGUID(), $subject, $body, null, array($method));
							}
						}
					}
				}
			}
		}
	}
}

/**
 * A simple function to see who can edit a project discussion post
 * @param the comment $entity
 * @param user who owns the project $project_owner
 * @return boolean
 */
function projects_can_edit_discussion($entity, $project_owner) {

	//logged in user
	$user = elgg_get_logged_in_user_guid();

	if (($entity->owner_guid == $user) || $project_owner == $user || elgg_is_admin_logged_in()) {
		return true;
	} else {
		return false;
	}
}

/**
 * Process upgrades for the projects plugin
 */
function projects_run_upgrades() {
	$path = elgg_get_plugins_path() . 'projects/upgrades/';
	$files = elgg_get_upgrade_files($path);
	foreach ($files as $file) {
		include "$path{$file}";
	}
}
