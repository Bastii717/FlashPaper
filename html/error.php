<?php defined('_DIRECT_ACCESS_CHECK') or exit(); ?>
			<section class="narrow">
				<span class="label">// error</span>
				<h1>Something broke</h1>
				<p class="sub"><span class="err-msg"><?php echo htmlspecialchars($error_message) ?></span></p>
				<p class="sub">If you were creating a secret, nothing was stored. If you were opening one, it may already be deleted.</p>
				<div class="btns"><a class="btn-s" href="./">Back to start</a></div>
			</section>
