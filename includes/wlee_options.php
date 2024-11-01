<?php

//Options-Page im Admin-Panel
function wlee_options_page()
{
	global $wlee_localversion, $wp_wlee_plugin_url;
	
	//Auf Updates prüfen
	$message = wlee_update_message();
		
	if(!empty($message))
	{
		echo '<div id="message" class="updated fade"><p>' . $message . '</p></div>';
	}
	
    // Wenn das Formular abgesendet wurde
	if (isset($_POST['submit'])) 
	{
		check_admin_referer('wl-email-encrypter');
		
		//Formular auslesen
		$wlee_autolink_post = !isset($_POST['wlee_autolink_check']) ? false: true;
		$wlee_encrypt_post = !isset($_POST['wlee_encrypt_check']) ? false: true;
		
		$wlee_post_options = array(
			'autolink' => $wlee_autolink_post,
			'encrypt' => $wlee_encrypt_post,
		);
		
		wlee_set_options($wlee_post_options);
		
		$msg_status = __('wL Email Encrypter Einstellungen gespeichert.');
							
		// Show message
		echo '<div id="message" class="updated fade"><p>' . $msg_status . '</p></div>';
		
	} 
	
	// Einstellungen aus der db holen
	$wlee_options_check = wlee_get_options();
	$wlee_autolink_check = ($wlee_options_check['autolink']) ? 'checked' : '';
	$wlee_encrypt_check = ($wlee_options_check['encrypt']) ? 'checked' : '' ;
	
	global $wp_version;	
	
	// Vorbereitungen Einstellungs-Seite
	$img_path = WLEE_PLUGIN_URL . '/images';	
    $action_url = $_SERVER['REQUEST_URI'];
	$website_url = 'http://plugins.wlabs.de/category/wl-email-encrypter/';
    $wpnonce = wp_create_nonce('wl-email-encrypter');

    // Configuration Page
	
	//CSS
	echo '<style type="text/css">
div#dbx-content a{
text-decoration:none;
}
</style>
';
	
	//Content
    echo <<<END
<div class="wrap" style="max-width:950px !important;">
	<h2>wL Email Encrypter</h2>
	
	<div id="poststuff" style="margin-top:10px;">
		<div id="sideblock" style="float:right;width:220px;margin-left:10px;"> 
			<h2>Informationen</h2>
			<div id="dbx-content" style="text-decoration:none;">
				<img src="$img_path/home.png"><a style="text-decoration:none;" href="$website_url" target="_BLANK"> wL Email Encrypter Webseite</a><br />
			</div>
		</div>
 	</div>
	
	<div id="mainblock" style="width:710px">
		<div class="dbx-content">
		<form name="wlee_form" action="$action_url" method="post">
			
			<!--<h2>Anwendung</h2>           
			<p>Hier steht die Anwendung.</p>-->

			<h2>Einstellungen</h2>
			
			<p><strong>Automatische Verlinkung</strong></p>
			<p>wL Email Encrypter kann automatisch jede Email-Adresse verlinken.</p>
			<p>Beim Anklicken einer verlinkten Email wird beim Besucher automatisch das Mailprogramm gestartet.</p>
			
			<div><input id="check1" type="checkbox" name="wlee_autolink_check" $wlee_autolink_check />
			<label for="check1">Email-Adressen automatisch verlinken</label></div>
			
			<p><strong>Email-Verschlüsselung</strong></p>
			<p>wL Email Encrypter kann automatisch jede Email-Adresse so verschlüsseln, damit Bots, Harvester und andere autmotisierte Programme Email-Adressen lesen und später für Spam-Zwecke nicht missbrauchen können. Der normale Blog-Besucher kann die Email-Adresse jedoch weiterhin normal sehen.</p>
			<p>Alle verlinkten Emailadressen werden ebenfalls verschlüsselt.</p>
			
			<div><input id="check2" type="checkbox" name="wlee_encrypt_check" $wlee_encrypt_check />
			<label for="check2">Email-Adressen verschlüsseln</label></div>
			
			<div class="submit">
				<input type="hidden" name="submit" value="1" /> 
				<input type="hidden" id="_wpnonce" name="_wpnonce" value="$wpnonce" />
				<input type="submit" name="Submit" value="Speichern" />
			</div>
			</form>
		</div>	
	 </div>
	</div>
</div>
END;
}


?>