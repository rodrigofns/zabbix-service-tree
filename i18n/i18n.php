<?php
/**
 * Hygienic internationalization routines for PHP + JavaScript.
 * Date: July 29, 2013.
 * Source: https://github.com/rodrigocfd/php-js-i18n
 *
 * Copyright (c) 2013 Rodrigo Cesar de Freitas Dias
 * Released under the MIT license, see license.txt for details.
 */

$i18n_data = array(); // global i18n dictionary for PHP
function I($text) { // global i18n function for PHP
	global $i18n_data;
	return $i18n_data[$text];
}

/**
 * Sets up the i18n configuration.
 * @param string $srcLang    Name of the source file, with the keys which go in the I() calls.
 * @param string $destLang   File with the replacement strings.
 * @param bool   $generateJs Pass true if the PHP page has JavaScript strings to translate.
 */
function i18n_set_map($srcLang, $destLang=null, $generateJs=true) {
	// Note: if same string appears twice in source
	//  file, the last one will take place.
	global $i18n_data;
	$src = file_get_contents(dirname(__FILE__).'/'.$srcLang);
	$src = preg_split("\r?\n?", $src);
	if($destLang === null) { // no translation, keep original strings
		$dest = $src;
	} else { // load second file for dictionary mapping
		$dest = file_get_contents(dirname(__FILE__).'/'.$destLang);
		$dest = preg_split("\r?\n?", $dest);
	}
	if(count($src) !== count($dest)) return false;
	for($i = 0, $count = count($src); $i < $count; ++$i)
		if($src[$i] != '' && $src[$i][0] !== '#') // skip blank and comment lines
			$i18n_data[$src[$i]] = $dest[$i]; // our dictionary
	if(!$generateJs) return;

	?><script>
	var i18n_data = []; // global i18n dictionary for JS
	<?php foreach($i18n_data as $key => $val) {
		printf('i18n_data["%s"]="%s";',
			str_replace('"', '\"', $key), str_replace('"', '\"', $val) );
	} ?>
	function I(text) { // global i18n function for JS
		return i18n_data[text];
	}
	</script><?php
}