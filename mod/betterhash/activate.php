<?php

$dbprefix = elgg_get_config('dbprefix');
get_data("alter table {$dbprefix}users_entity modify column `password` varchar(128) NOT NULL DEFAULT '';");
