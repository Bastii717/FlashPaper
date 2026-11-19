<?php defined('_DIRECT_ACCESS_CHECK') or exit(); ?>
			<section>
				<div class="prompt"><span class="u"><?php echo fp_host($settings) ?></span>:~$ <span class="cmd">flashpaper new</span></div>
				<h1><?php echo $settings['messages']['view_code_header'] ?></h1>
<?php if ( $settings['messages']['view_code_subheader'] != '' ) { ?>
				<p class="sub"><?php echo $settings['messages']['view_code_subheader'] ?></p>
<?php } ?>
				<div class="field">
					<input type="text" id="link" readonly value="<?php echo htmlspecialchars($message, ENT_QUOTES) ?>" aria-label="One-time link" spellcheck="false">
					<button class="btn-p" type="button" data-copy="#link" data-done="✓ Copied">Copy link</button>
				</div>
				<div class="list">
					<span class="label">// before you send it</span>
					<ul>
						<li><b>Don't open it yourself</b> — that uses it up.</li>
						<li>Send the context (whose login, which system) through a different channel than the link.</li>
						<li>If the recipient gets "not found", someone else may have opened it first.</li>
					</ul>
				</div>
				<div class="btns"><a class="btn-s" href="./">New secret</a></div>
			</section>
