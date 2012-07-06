<?php
/**
 * Elgg embed CSS - standard across all themes
 * 
 * @package embed
 */
?>
.embed-wrapper {
	width: 730px;
	min-height: 400px;
	margin: 20px 15px;
}
.embed-wrapper h2 {
	color: #333333;
	margin-bottom: 10px;
}
.embed-wrapper .elgg-item {
	cursor: pointer;
}

/* ***************************************
	EMBED TABBED PAGE NAVIGATION
*************************************** */
.embed-wrapper .elgg-tabs a:hover {
	color: #666;
}

.embed-wrapper p {
	color: #333;
}
.embed-item {
	padding-left: 5px;
	padding-right: 5px;
}
.embed-item:hover {
	background-color: #eee;
}

/**********************************
Search box
***********************************/
.elgg-embed-search {
	float: right;
	height: 23px;
	margin-bottom: -23px;
}
.elgg-embed-search input[type=text] {
	width: 190px;
}
.elgg-embed-search input[type=submit] {
	display: none;
}
.elgg-embed-search input[type=text] {
	-webkit-border-radius: 10px;
	-moz-border-radius: 10px;
	border-radius: 10px;
	
	border: 2px solid #CCCCCC;
	font-size: 12px;
	font-weight: bold;
	padding: 2px 4px 2px 26px;
	background: transparent url(<?php echo elgg_get_site_url(); ?>_graphics/elgg_sprites.png) no-repeat 2px -916px;
}
.elgg-embed-search input[type=text]:focus, .elgg-search input[type=text]:active {
	border: 2px solid #71b9f7;
}
