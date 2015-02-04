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
	'TemplateForbiddenFunctionsCheck',
	'ThemecolorsCheck',
	'ThemeNameCheck',
	'ThemePostPaginationCheck',
	'ThemeTagCheck',
	'TitleCheck',
	'VCMergeConflictCheck',
	'WidgetsCheck',
	'JavaScriptLintCheck',
), array(
	'PHPAnalyzer',
	'CustomResourceAnalyzer',
	'ThemeAnalyzer',
) );

VIP_Scanner::get_instance()->register_review( 'VIP Theme Review', array(
	'DeprecatedConstantsCheck',
	'DeprecatedFunctionsCheck',
	'DeprecatedParametersCheck',
	'VIPWhitelistCheck',
	'VIPRestrictedClassesCheck',
	'VIPRestrictedPatternsCheck',
	'VIPRestrictedCommandsCheck',
	'VIPInitCheck',
	'VIPParametersCheck',
	'PHPShortTagsCheck',
	'PHPClosingTagsCheck',
	'VCMergeConflictCheck',
	'WordPressCodingStandardsCheck',
	'ClamAVCheck', // Pass null to lookup the check normally
	'AdBustersCheck',
), array(
	'PHPAnalyzer',
	'CustomResourceAnalyzer',
	'ThemeAnalyzer',
) );
