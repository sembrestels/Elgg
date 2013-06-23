<?php
?>
ul.elgg-menu-language-selector {
	position: absolute;
	right: 0px;
	top: 42px;
	color: black;
}
ul.elgg-menu-language-selector > li {
	display: inline-block;
}
ul.elgg-menu-language-selector > li:first-child:before {
    content: "";
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
}
ul.elgg-menu-language-selector > li:before {
	content: "|";
	padding-bottom: 0;
	padding-left: 6px;
	padding-right: 6px;
	padding-top: 0;
	color: white;
}
ul.elgg-menu-language-selector > li > a {
	color: white;
	display: inline-block;
}
