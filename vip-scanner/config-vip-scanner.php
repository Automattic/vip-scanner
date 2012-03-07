<?php

// TODO: Not sure if this is the best place or way to handle this...

VIP_Scanner::get_instance()->register_review( 'WP.org Theme Review', array(
	'ThemeArtisteerCheck',
	'BadThingsCheck',
	'ThemeBasicChecks',
	'ThemeCommentPaginationCheck',
	'ThemeContentWidthCheck',
	'ThemeStyleOptionalCheck',
	'ThemeCustomizeCheck',
	'DeprecatedCheck',
	'DirectoriesCheck',
	'ThemeEditorStyleCheck',
	'ThemeFilesCheck',
	'GravatarCheck',
	'ThemeIncludeCheck',
	'HardcodedLinksCheck',
	'MalwareCheck',
	'MoreDeprecatedCheck',
	'ThemeNavMenuCheck',
	'NonPrintableCheck',
	'PHPShortTagsCheck',
	'ThemePostFormatCheck',
	'ThemePostPaginationCheck',
	'ThemePostThumbnailCheck',
	'GetOptionDeprecated',
	'ThemeStyleRequiredCheck',
	'ThemeStyleSuggestedCheck',
	'BloginfoDeprecatedCheck',
	'ThemeTagCheck',
	'ThemeSupportCheck',
	'TimeDateCheck',
	'TimThumbCheck',
	'WormCheck',
) );

VIP_Scanner::get_instance()->register_review( 'Undefined Function Check', array(
	'UndefinedFunctionCheck',
) );

VIP_Scanner::get_instance()->register_review( 'WP.com Theme Review', array(
	'EscapingCheck',
	'InternationalizedStringCheck',
	'TheamPubFileCheck',
	'TheamPubIndividualFiles',
	'ThemecolorsCheck',
	#'WordPressCodingStandards', // Coming soon :)
) );
