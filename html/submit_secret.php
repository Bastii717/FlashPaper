<?php
	defined('_DIRECT_ACCESS_CHECK') or exit();

	$max = (int)$settings['max_secret_length'];
	$expiry = fp_expiry_text($settings);
	$current_template = $_GET['t'] ?? '';
?>
			<section>
				<div class="prompt"><span class="u"><?php echo fp_host($settings) ?></span>:~$ <span class="cmd">flashpaper new</span></div>
				<h1><?php echo $settings['messages']['submit_secret_header'] ?></h1>
<?php if ( $settings['messages']['submit_secret_subheader'] != '' ) { ?>
				<p class="sub"><?php echo $settings['messages']['submit_secret_subheader'] ?></p>
<?php } ?>
				<form class="card" action="" method="POST">
					<div class="card-h"><label for="secret">// secret</label><span class="r" id="secret-count"><?php echo strlen(str_replace("\r\n", "\n", $template_text)) . '/' . $max ?></span></div>
					<textarea id="secret" name="secret" data-count="#secret-count" data-max="<?php echo $max ?>" placeholder="Paste a password, token, or anything else…" spellcheck="false" autocomplete="off" autofocus><?php echo htmlspecialchars($template_text, ENT_QUOTES) ?></textarea>
					<div class="card-f">
						<button class="btn-p" name="submit" type="submit"><?php echo $settings['messages']['submit_secret_button'] ?></button>
						<span data-toolong role="alert"<?php echo $too_long ? '' : ' hidden' ?>><span class="err"><?php echo too_long_message($settings) ?></span></span>
						<span class="grow"></span>
						<select data-template-nav aria-label="Template">
							<option value="./">template: none</option>
							<?php
								$templates = glob('templates/*.txt');
								foreach ($templates as $t) {
									$filename = basename($t, '.txt');
									$url_filename = urlencode($filename);
									$selected = ( $filename === $current_template ) ? ' selected' : '';
									echo "<option value=\"?t={$url_filename}\"{$selected}>" . htmlspecialchars($filename) . "</option>";
								}
							?>
						</select>
					</div>
				</form>
				<div class="facts" id="how">
					<div class="fact"><div class="k">// works once</div><p>The first open deletes it from the server. Nobody can open it twice.</p></div>
					<div class="fact"><div class="k">// key in the link</div><p>Stored encrypted (AES-256). The key is part of the link, so whoever has the link can open it.</p></div>
<?php if ( $expiry !== '' ) { ?>
					<div class="fact"><div class="k">// expires</div><p><?php echo $expiry ?></p></div>
<?php } ?>
				</div>
				<p class="trust">This server decrypts the secret when it's opened. Use an instance you trust.</p>
			</section>
