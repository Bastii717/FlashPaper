<?php
	defined('_DIRECT_ACCESS_CHECK') or exit();

	$settings = [
		'site_title' => 'FlashPaper :: Self-Destructing Message',
		'site_logo' => 'img/logo.svg',
		'display_logo' => 'true', # true/false
		'display_title' => 'false', # true/false  
		'return_full_url' => true,
		'base_url' => '', # https://example.com/flashpaper
		'max_secret_length' => 3000,
		'announcement' => '',
		'commit' => '', # git commit this deployment was built from. shown in the footer when set
		'prune' => [
			'enabled' => true,
			'min_days' => 365,
			'max_days' => 730
		],
		'messages' => [
			'error_secret_too_long' => 'Too long: max {max} characters.',

			'submit_secret_header' => 'New one-time secret',
			'submit_secret_subheader' => 'Paste it, get a link. The link works once, then the secret is deleted.',
			'submit_secret_button' => 'Create link',

			'view_code_header' => 'Link ready',
			'view_code_subheader' => 'Send it to one person. The first time it\'s opened, the secret is deleted from the server.',

			'confirm_view_secret_header' => 'Open secret?',
			'confirm_view_secret_button' => 'Reveal secret',

			'view_secret_header' => 'Your secret',
			'view_secret_subheader' => 'Deleted from the server. This page is the only copy left.',
		]
	];
?>
