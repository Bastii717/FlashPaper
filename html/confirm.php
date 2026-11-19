<?php defined('_DIRECT_ACCESS_CHECK') or exit(); ?>
<?php # the inline onsubmit guards against a double tap before js/flashpaper.js has loaded: a second POST would burn the secret ?>
			<section class="narrow">
				<span class="label">// one-time secret</span>
				<h1><?php echo $settings['messages']['confirm_view_secret_header'] ?></h1>
				<p class="sub">If this link is still valid, it shows a secret <b>exactly once</b>. It's deleted from the server the moment it's shown — copy it before you leave the page.</p>
				<form class="btns" method="post" action="./" onsubmit="var b = this.querySelector('button'); if (b) { b.disabled = true; }">
					<input type="hidden" name="k" value="<?php echo htmlspecialchars($_GET['k']) ?>">
					<button class="btn-p" type="submit"><?php echo $settings['messages']['confirm_view_secret_button'] ?></button>
				</form>
				<div class="list">
					<span class="label">// why the extra click?</span>
					<ul>
						<li>Chat apps and mail scanners open links to build previews. They don't click buttons, so they can't burn your secret.</li>
						<li>Not ready? Just close this page. Nothing happens until you click.</li>
					</ul>
				</div>
			</section>
