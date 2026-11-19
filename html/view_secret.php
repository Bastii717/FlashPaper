<?php defined('_DIRECT_ACCESS_CHECK') or exit(); ?>
			<section class="narrow">
				<span class="status"><span class="dot"></span>deleted from server</span>
				<h1><?php echo $settings['messages']['view_secret_header'] ?></h1>
<?php if ( $settings['messages']['view_secret_subheader'] != '' ) { ?>
				<p class="sub"><?php echo $settings['messages']['view_secret_subheader'] ?></p>
<?php } ?>
<?php # $message is already escaped. the newline after <pre> is eaten by the HTML parser, so a leading newline in the secret survives ?>
				<pre class="secret" id="secret-text" data-clearable>
<?php echo $message ?></pre>
				<div class="cleared" data-cleared hidden>// cleared from screen. Reloading won't bring it back.</div>
				<div class="btns" data-clearable>
					<button class="btn-p" type="button" data-copy="#secret-text" data-done="✓ Copied">Copy secret</button>
					<button class="btn-s" type="button" data-clear>Clear screen</button>
				</div>
				<div class="list">
					<ul>
						<li>Store it somewhere safe now, e.g. your password manager. Reloading won't show it again.</li>
					</ul>
				</div>
			</section>
