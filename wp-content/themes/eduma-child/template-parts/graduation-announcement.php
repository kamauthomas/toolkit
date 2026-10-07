<?php
$announcement_assets = get_stylesheet_directory_uri() . '/assets/';
?>
<dialog class="toolkit-graduation" data-graduation-announcement data-campaign="2026-10-22" data-start="2026-10-22T06:00:00Z" data-end="2026-10-22T10:00:00Z" aria-labelledby="toolkit-graduation-title" aria-describedby="toolkit-graduation-intro">
	<div class="toolkit-graduation__poster">
		<div class="toolkit-graduation__wash" aria-hidden="true"></div>
		<button class="toolkit-graduation__dismiss" type="button" data-graduation-close aria-label="Close graduation announcement" autofocus><span aria-hidden="true">×</span></button>
		<header class="toolkit-graduation__brand">
			<img src="<?php echo esc_url( $announcement_assets . 'images/toolkit-logo.png' ); ?>" width="300" height="216" alt="Toolkit official logo">
			<div><strong>The Toolkit for Skills &amp; Innovation</strong><p>A leader in powering Africa with skilled, confident and productive youth.</p></div>
		</header>
		<img class="toolkit-graduation__photo" data-graduation-photo data-src="<?php echo esc_url( $announcement_assets . 'images/graduation/kmm-2143-jpg.webp' ); ?>" width="1800" height="1200" alt="Two Toolkit graduates celebrating their achievement" decoding="async">
		<div class="toolkit-graduation__copy">
			<p class="toolkit-graduation__class">Class of <svg viewBox="0 0 100 75" aria-hidden="true"><path d="M2 23 50 2l48 21-48 22zM20 35v20c17 18 43 18 60 0V35L50 49z" fill="currentColor"/><path d="M95 25v38" stroke="#ff6600" stroke-width="4"/><circle cx="95" cy="66" r="5" fill="#ff6600"/></svg></p>
			<h2 id="toolkit-graduation-title"><span class="toolkit-graduation__year">2026</span><span class="toolkit-graduation__heading">Graduation</span><span class="toolkit-graduation__ceremony">Ceremony</span></h2>
			<p id="toolkit-graduation-intro">Join us as we celebrate our graduates and the next generation of skilled, confident and productive youth.</p>
		</div>
		<div class="toolkit-graduation__bottom">
			<dl class="toolkit-graduation__details">
				<div><span class="toolkit-graduation__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 2v6m10-6v6M3 11h18m-14 4h2m3 0h2m3 0h2m-12 3h2m3 0h2"/></svg></span><div><dt>Date</dt><dd>Thursday,<br><time datetime="2026-10-22" class="toolkit-graduation__date">22nd October 2026</time></dd></div></div>
				<div><span class="toolkit-graduation__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 3"/></svg></span><div><dt>Time</dt><dd>9:00 AM<br>– 1:00 PM</dd></div></div>
				<div><span class="toolkit-graduation__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M19 10c0 5-7 12-7 12S5 15 5 10a7 7 0 0 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span><div><dt>Venue</dt><dd>The Toolkit Skills &amp; Innovation HUB<span>Karen–Kikuyu Highway<br>(Southern Bypass), Kikuyu</span></dd></div></div>
			</dl>
			<div class="toolkit-graduation__actions"><a class="toolkit-graduation__save" href="<?php echo esc_url( $announcement_assets . 'calendar/toolkit-graduation-2026.ics' ); ?>" download="toolkit-graduation-2026.ics"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 2v6m10-6v6M3 11h18m9 3v5m-2.5-2.5h5"/></svg> Save the date <span aria-hidden="true">›</span></a><button type="button" class="toolkit-graduation__close" data-graduation-close>Close</button><a class="toolkit-graduation__more" href="<?php echo esc_url( home_url( '/graduation/' ) ); ?>">Graduation details <span aria-hidden="true">→</span></a></div>
		</div>
		<div class="toolkit-graduation__ribbon" aria-hidden="true"></div>
	</div>
</dialog>
