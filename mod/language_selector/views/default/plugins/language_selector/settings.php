<?php
$yesno_options = array(
	"yes" => elgg_echo("option:yes"),
	"no" => elgg_echo("option:no")
);

$noyes_options = array_reverse($yesno_options);

?>
<div>
	<label><?php echo elgg_echo("language_selector:admin:settings:min_completeness"); ?></label><br />
	<?php echo elgg_view("input/text", array("name" => "params[min_completeness]", "value" => $vars['entity']->min_completeness, "size" => 2, "maxlength" => 2)); ?>
</div>
<div>
	<label><?php echo elgg_echo('language_selector:admin:settings:show_in_header'); ?></label><br />
	<?php echo elgg_view("input/dropdown", array("name" => "params[show_in_header]", "options_values" => $yesno_options, "value" => $vars['entity']->show_in_header)); ?>
</div>
<div>
	<label><?php echo elgg_echo('language_selector:admin:settings:autodetect'); ?></label><br />
	<?php echo elgg_view("input/dropdown", array("name" => "params[autodetect]", "options_values" => $yesno_options, "value" => $vars['entity']->autodetect)); ?>
</div>