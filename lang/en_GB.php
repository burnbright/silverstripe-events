<?php

/**
 * English (United Kingdom) language pack
 * @package modules: events
 * @subpackage i18n
 */

i18n::include_locale_file('modules: events', 'en_US');

global $lang;

if(array_key_exists('en_GB', $lang) && is_array($lang['en_GB'])) {
	$lang['en_GB'] = array_merge($lang['en_US'], $lang['en_GB']);
} else {
	$lang['en_GB'] = $lang['en_US'];
}


?>