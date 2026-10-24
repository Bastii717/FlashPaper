<?php defined('_DIRECT_ACCESS_CHECK') or exit(); ?>
		</main>
		<footer class="fp-footer">
			<a href="https://github.com/Bastii717/FlashPaper">FlashPaper on GitHub</a><?php
			# commit the deployment was built from (set by the Docker image via
			# FLASHPAPER_COMMIT). may be unset on manual installs or when the
			# sanity check renders this footer before settings.php is loaded
			$commit = $settings['commit'] ?? '';
			if ( preg_match('/^[0-9a-f]{7,40}$/', $commit) ) {
				$short = htmlspecialchars(substr($commit, 0, 7));
				$full = htmlspecialchars($commit);
				echo " &middot; <a href=\"https://github.com/Bastii717/FlashPaper/commit/{$full}\"><code>{$short}</code></a>";
			}
			?>

		</footer>
	</body>
</html>
