<?php

// Include the form config
include_once( VIP_SCANNER_DIR . '/config-vip-scanner-wpcom-form.php' );

// TODO: Not sure if this is the best place or way to handle this...

VIP_Scanner::get_instance()->register_review( 'Undefined Function Check', array(
	'UndefinedFunctionCheck',
) );

VIP_Scanner::get_instance()->register_review( 'WP.com Theme Review', array(
	'AtPackageCheck',
	'BodyClassCheck',
	'CDNCheck',
	'CheckedSelectedDisabledCheck',
	'CommentsCheck',
	'CustomizerCheck',
	'DeprecatedConstantsCheck',
	'DeprecatedFunctionsCheck',
	'DeprecatedParametersCheck',
	'EscapingCheck',
	'FirstPostPromptCheck',
	'ForbiddenConstantsCheck',
	'ForbiddenFunctionsCheck',
	'ForbiddenGoogleCheck',
	'ForbiddenLibrariesCheck',
	'ForbiddenPHPFunctionsCheck',
	'HeaderCheck',
	'HooksCheck',
	'InternationalizedStringCheck',
	'jQueryCheck',
	'JetpackCheck',
	'LanguagePacksCheck',
	'MasonryCheck',
	'PostThumbnailsCheck',
	'ScreenshotCheck',
	'StyleHeadersCheck',
	'ThemecolorsCheck',
	'ThemeContentWidthCheck',
	'ThemeNameCheck',
	'ThemePostPaginationCheck',
	'ThemeStyleRequiredCheck',
	'ThemeTagCheck',
	'TimeDateCheck',
	'TitleCheck',
	'VCMergeConflictCheck',
	'WidgetsCheck',
	'JavaScriptLintCheck',
), array(
	'CodeAnalyzer',
	'ResourceAnalyzer',
	'ThemeAnalyzer',
) );

VIP_Scanner::get_instance()->register_review( 'VIP Theme Review', array(
	'CheckedSelectedDisabledCheck',
	'DeprecatedConstantsCheck',
	'DeprecatedFunctionsCheck',
	'DeprecatedParametersCheck',
	'jQueryCheck',
	'VIPWhitelistCheck',
	'VIPRestrictedClassesCheck',
	'VIPRestrictedPatternsCheck',
	'VIPRestrictedCommandsCheck',
	'VIPInitCheck',
	'VIPParametersCheck',
	'PHPShortTagsCheck',
	'PHPClosingTagsCheck',
	'StyleHeadersCheck',
	'VCMergeConflictCheck',
	'WordPressCodingStandardsCheck',
	'ClamAVCheck', // Pass null to lookup the check normally
	'AdBustersCheck',
), array(
	'CodeAnalyzer',
	'ResourceAnalyzer',
	'ThemeAnalyzer',
) );
