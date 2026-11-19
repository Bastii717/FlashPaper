<?php
	defined('_DIRECT_ACCESS_CHECK') or exit();
	require_once(__DIR__ . '/../includes/ui.php');

	# $settings is unset when the sanity check renders this page
	$fp_s = $settings ?? [];
	$fp_logo = (string)($fp_s['site_logo'] ?? '');
	$fp_show_logo = fp_bool($fp_s['display_logo'] ?? true);
	$fp_show_title = fp_bool($fp_s['display_title'] ?? false);
	$fp_custom_logo = ( $fp_logo !== '' && $fp_logo !== 'img/logo.svg' );
	$fp_announcement = (string)($fp_s['announcement'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<!--
		######################################################################################
		# Copyright (c) 2017-<?php echo date("Y"); ?> Andrew Paglusch                                            #
		# https://raw.githubusercontent.com/Bastii717/FlashPaper/master/LICENSE              #
		######################################################################################
		-->
		<meta charset="utf-8">
		<title><?php echo htmlspecialchars($fp_s['site_title'] ?? 'FlashPaper') ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="referrer" content="no-referrer">
		<script>
			// apply the stored theme before first paint. "themePreference" is the
			// key of the previous theme switch ('true' = dark); migrate it once
			(function () {
				var t = null;
				try {
					t = localStorage.getItem('fp-theme');
					if ( t !== 'dark' && t !== 'light' ) {
						t = null;
						var old = localStorage.getItem('themePreference');
						if ( old === 'true' || old === 'false' ) {
							t = ( old === 'true' ) ? 'dark' : 'light';
							localStorage.setItem('fp-theme', t);
						}
						localStorage.removeItem('themePreference');
					}
				} catch (e) { }
				if ( t ) { document.documentElement.setAttribute('data-theme', t); }
			})();
		</script>
		<link rel="stylesheet" href="./css/flashpaper.css?v=<?php echo fp_asset_v($fp_s) ?>">
	</head>
	<body>
		<script>
			if (location.protocol != 'https:') {
				document.currentScript.insertAdjacentHTML('afterend', '<div class="banner-http" role="alert"><div class="in">!! Plain HTTP — this connection is not encrypted. Do not paste anything sensitive.</div></div>');
			}
		</script>
		<nav class="top">
			<div class="in">
				<a class="brand" href="./"><?php
					if ( $fp_show_logo && $fp_custom_logo ) {
						echo '<img class="logo-img" src="' . htmlspecialchars($fp_logo, ENT_QUOTES) . '" alt="Logo">';
					} elseif ( $fp_show_logo ) {
						echo '<span class="wordmark">flashpaper<span class="cursor" aria-hidden="true"></span></span>';
					}
					if ( $fp_show_title ) {
						echo '<span class="site-title">' . htmlspecialchars($fp_s['site_title'] ?? '') . '</span>';
					}
				?></a>
				<div class="links">
					<a href="./#how" class="opt">how it works</a>
					<a href="./">new secret</a>
					<button type="button" class="theme" data-theme-toggle hidden aria-label="Switch colour theme (Shift+D)" title="Shift+D">☾ dark</button>
				</div>
			</div>
		</nav>
<?php
	# raw on purpose: the announcement is operator-controlled and may contain links
	if ( $fp_announcement !== '' ) {
		echo "\t\t<div class=\"banner-ann\" role=\"note\"><div class=\"in\"><b>NOTICE</b><span>{$fp_announcement}</span></div></div>\n";
	}
?>
		<main>
