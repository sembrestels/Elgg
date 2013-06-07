<?php

if (!isset($vars['entity']) || !$vars['entity']) {
	echo elgg_echo('projects:notfound');
	return true;
}

$project = $vars['entity'];
$owner = $project->getOwnerEntity();

if (!$owner) {
	// not having an owner is very bad so we throw an exception
	$msg = elgg_echo('InvalidParameterException:IdNotExistForGUID', array('project owner', $project->guid));
	throw new InvalidParameterException($msg);
}

$tags = array('value' => $project->interests);
?>

<div class="projects-gallery-item">	
	<p class="projects-gallery-photo"><?php echo elgg_view_entity_icon($project, 'medium') ?></p>
	<h2> <?php echo $project->name ?></h2>
	<div class="projects-gallery-tags" ><?php echo elgg_view("output/tags", $tags); ?></div>
	<div class="projects-gallery-info">
		<p class="projects-gallery-subtitle"><?php echo $project->briefdescription?></p>		
	</div>
	
</div>
