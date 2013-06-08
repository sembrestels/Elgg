<?php
/**
 * Projects CSS
 * 
 * @package Coopfunding
 * @subpackage Projects
 */

?>
.projects-profile > .elgg-image {
	width: 100%;
}
.projects-stats {
	background: #eeeeee;
	padding: 5px;
	margin-top: 10px;
	
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
}
.projects-owner-block h3 {
	font-size: 1.7em;
	line-height: 1.5em;
}
.projects-owner-block .elgg-description {
	font-size: 1.2em;
}
#projects-tools > li {
	width: 48%;
	min-height: 200px;
	margin-bottom: 40px;
}

#projects-tools > li:nth-child(odd) {
	margin-right: 4%;
}

.projects-widget-viewall {
	float: right;
	font-size: 85%;
}

.projects-latest-reply {
	float: right;
}

.elgg-gallery > .elgg-item, .search-list > .elgg-item {
	display: inline-block;
	width: 33%;
	vertical-align: top;
}

@media screen and (max-width: 480px) {
	.elgg-gallery > .elgg-item, .search-list > .elgg-item {
		width: 100%;
	}
}

.projects-gallery-info {
	padding: 10px;	
}

.projects-gallery-item {
	text-align: center;
	padding: 10px 4px;
	margin-bottom: 7px;	
}

.projects-gallery-photo img {
	width: 100%
}

.projects-gallery-item:hover img {
	-webkit-filter: hue-rotate(90deg);
}
