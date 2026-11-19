<?php
	defined('_DIRECT_ACCESS_CHECK') or exit();

	# small view helpers. header.php and footer.php also render on the
	# sanity-check page, before settings.php is loaded, so all of them cope
	# with $s being null

	function fp_bool($v) {
		return filter_var($v, FILTER_VALIDATE_BOOLEAN);
	}

	# instance host for the prompt line: BASE_URL first, Host header as fallback.
	# the Host header is client-controlled, so the result is always escaped
	function fp_host($s) {
		$host = parse_url($s['base_url'] ?? '', PHP_URL_HOST);
		if ( empty($host) ) { $host = $_SERVER['HTTP_HOST'] ?? ''; }
		return htmlspecialchars((string)$host, ENT_QUOTES);
	}

	# cache-busting value for css/js: the build commit, or a constant on manual installs
	function fp_asset_v($s) {
		$commit = (string)($s['commit'] ?? '');
		return preg_match('/^[0-9a-f]{7,40}$/', $commit) ? substr($commit, 0, 7) : 'ui1';
	}

	# expiry sentence for the create page. empty when pruning is disabled
	function fp_expiry_text($s) {
		if ( ! fp_bool($s['prune']['enabled'] ?? false) ) { return ''; }
		$min = (int)($s['prune']['min_days'] ?? 0);
		$max = (int)($s['prune']['max_days'] ?? 0);
		if ( $min == $max ) {
			return "Unopened secrets are removed after {$min} day" . ($min == 1 ? '' : 's') . '.';
		}
		return "Unopened secrets are removed after {$min}–{$max} days.";
	}
?>
