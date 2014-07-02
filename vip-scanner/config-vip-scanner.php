<?php

// Include the form config
include_once( VIP_SCANNER_DIR . '/config-vip-scanner-wpcom-form.php' );

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
	'PHPClosingTagsCheck',
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
	'VCMergeConflictCheck',
), array(
	'PHPAnalyzer',
	'CustomResourceAnalyzer',
	'ThemeAnalyzer',
) );

VIP_Scanner::get_instance()->register_review( 'Undefined Function Check', array(
	'UndefinedFunctionCheck',
) );

VIP_Scanner::get_instance()->register_review( 'WP.com Theme Review', array(
	'BodyClassCheck',
	'EscapingCheck',
	'ForbiddenFunctionsCheck',
	'InternationalizedStringCheck',
	'ScreenshotCheck',
	'TheamPubFileCheck',
	'TheamPubIndividualFiles',
	'ThemeCommentPaginationCheck',
	'ThemecolorsCheck',
	'VCMergeConflictCheck',
	'PHPClosingTagsCheck',
	'WordPressCodingStandardsCheck',
), array(
	'PHPAnalyzer',
	'CustomResourceAnalyzer',
	'ThemeAnalyzer',
) );

VIP_Scanner::get_instance()->register_review( 'VIP Theme Review', array(
	'VIPWhitelistCheck',
	'VIPRestrictedPatternsCheck',
	'VIPRestrictedCommandsCheck',
	'VIPInitCheck',
	'PHPShortTagsCheck',
	'PHPClosingTagsCheck',
	'VCMergeConflictCheck',
	'WordPressCodingStandardsCheck',
	'ClamAVCheck', // Pass null to lookup the check normally
), array(
	'PHPAnalyzer',
	'CustomResourceAnalyzer',
	'ThemeAnalyzer',
) );
