elgg.provide('elgg.language_selector');

elgg.language_selector.set_language = function(lang_id) {
	elgg.language_selector.set_cookie("client_language", lang_id, 30);
	document.location.href = document.location.href;
}

elgg.language_selector.set_cookie = function(c_name,value, expiredays) {
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + expiredays);
	document.cookie = c_name + "=" + escape(value) + ";Path=/" + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString());
}
