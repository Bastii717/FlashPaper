<?php
	defined('_DIRECT_ACCESS_CHECK') or exit();
	require_once(__DIR__ . '/../includes/ui.php');

	# commit the deployment was built from (set by the Docker image via
	# FLASHPAPER_COMMIT). may be unset on manual installs or when the
	# sanity check renders this footer before settings.php is loaded
	$fp_s = $settings ?? [];
	$fp_commit = (string)($fp_s['commit'] ?? '');
?>
		</main>
		<footer>
			<div class="in">
				<a href="./">flashpaper</a><span aria-hidden="true">·</span><a href="https://github.com/Bastii717/FlashPaper">source on GitHub</a><?php
				if ( preg_match('/^[0-9a-f]{7,40}$/', $fp_commit) ) {
					$short = htmlspecialchars(substr($fp_commit, 0, 7));
					$full = htmlspecialchars($fp_commit);
					echo "<span aria-hidden=\"true\">·</span><a href=\"https://github.com/Bastii717/FlashPaper/commit/{$full}\">{$short}</a>";
				}
				?>

			</div>
		</footer>
		<script src="./js/flashpaper.js?v=<?php echo fp_asset_v($fp_s) ?>" defer></script>
	</body>
</html>
