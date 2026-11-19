<?php
	defined("_DIRECT_ACCESS_CHECK") or exit();

	$settings = [
		"site_title" => getenv('SITE_TITLE') ?: "FlashPaper :: Self-Destructing Message",
		"site_logo" => getenv('SITE_LOGO') ?: "img/logo.svg",
		"display_logo" => filter_var(getenv('DISPLAY_LOGO') ?: "true", FILTER_VALIDATE_BOOLEAN),
		"display_title" => filter_var(getenv('DISPLAY_TITLE') ?: "false", FILTER_VALIDATE_BOOLEAN),
		"return_full_url" => filter_var(getenv('RETURN_FULL_URL') ?: "true", FILTER_VALIDATE_BOOLEAN),
		"base_url" => getenv('BASE_URL') ?: "",
		"max_secret_length" => (int)(getenv('MAX_SECRET_LENGTH') ?: 3000),
		"announcement" => getenv('ANNOUNCEMENT') ?: "",
		"commit" => getenv('FLASHPAPER_COMMIT') ?: "", # git commit the image was built from, shown in the footer
		'prune' => [
			'enabled' => filter_var(getenv('PRUNE_ENABLED') ?: "true", FILTER_VALIDATE_BOOLEAN),
			'min_days' => (int)(getenv('PRUNE_MIN_DAYS') ?: 365),
			'max_days' => (int)(getenv('PRUNE_MAX_DAYS') ?: 730),
		],
		"messages" => [
			"error_secret_too_long" => getenv('MESSAGES_ERROR_SECRET_TOO_LONG') ?: "Too long: max {max} characters.",

			"submit_secret_header" => getenv('MESSAGES_SUBMIT_SECRET_HEADER') ?: "New one-time secret",
			"submit_secret_subheader" => getenv('MESSAGES_SUBMIT_SECRET_SUBHEADER') ?: "Paste it, get a link. The link works once, then the secret is deleted.",
			"submit_secret_button" => getenv('MESSAGES_SUBMIT_SECRET_BUTTON') ?: "Create link",

			"view_code_header" => getenv('MESSAGES_VIEW_CODE_HEADER') ?: "Link ready",
			"view_code_subheader" => getenv('MESSAGES_VIEW_CODE_SUBHEADER') ?: "Send it to one person. The first time it's opened, the secret is deleted from the server.",

			"confirm_view_secret_header" => getenv('MESSAGES_CONFIRM_VIEW_SECRET_HEADER') ?: "Open secret?",
			"confirm_view_secret_button" => getenv('MESSAGES_CONFIRM_VIEW_SECRET_BUTTON') ?: "Reveal secret",

			"view_secret_header" => getenv('MESSAGES_VIEW_SECRET_HEADER') ?: "Your secret",
			"view_secret_subheader" => getenv('MESSAGES_VIEW_SECRET_SUBHEADER') ?: "Deleted from the server. This page is the only copy left.",
		]
	];
?>
