<?php
/*
Plugin Name: wL Email Encrypter
Plugin URI: http://plugins.wlabs.de/plugins/wl-email-encrypter/
Description: Dieses Plugin verschl&uuml;sselt Email-Adressen, um sie vor Bots und Harvester zu sch&uuml;tzen.
Author: Artur Weigandt
Version: 0.5.0
Author URI: http://www.wlabs.de

*/

/*  Copyright 2009  Artur Weigandt  (email : art4@wlabs.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


//Plugin-Version
define('WLEE_LOCAL_VERSION', '0.5.0');

//Plugin-URL
define('WLEE_PLUGIN_URL', wlee_get_plugin_url());

// Add Options Page
add_action('admin_menu', 'wlee_add_pages');

//Wenn das Plugin installiert werden soll
register_activation_hook(__FILE__, 'wlee_install');

//Wenn das Plugin deinstalliert werden soll
register_deactivation_hook(__FILE__, 'wlee_deinstall');

//Optionen aus der db holen:
$wlee_options = wlee_get_options();

//Email-Adressen verschluesseln/verlinken
if ($wlee_options['encrypt'] || $wlee_options['autolink'])
{
	if ($wlee_options['encrypt'])
	{
		//js in den Header einbinden
		add_action('wp_head', 'wlee_include_script');
	}
	
	add_filter('the_content', 'wlee_check', 100);
	add_filter('the_content_rss', 'wlee_check', 100);
	add_filter('the_excerpt','wlee_check', 100);
	add_filter('the_excerpt_rss','wlee_check', 100);
	//Emails in Kommentaren verschluesseln
	add_filter('comment_text','wlee_check', 100);
	add_filter('get_comment_text','wlee_check', 100);
	add_filter('get_comment_excerpt','wlee_check', 100);
}

//add_action('after_plugin_row', 'wlee_check_update');

//All Functions starts here!

//Einstellungen im Admin Panel einbinden  
function wlee_add_pages()
{
	include('includes/wlee_options.php');
	
	add_options_page(__('wL Email Encrypter Optionen'), __('wL Email Encrypter'), 8, __FILE__, 'wlee_options_page');
}

//Plugin installieren
function wlee_install()
{
	//Standard-Einstellungen
	$wlee_defaults = array(
		'encrypt' => true,
		'autolink' => false,
	);
	
	$options = serialize($wlee_defaults);
	
	update_option('wlee_options', $options);
}

function wlee_deinstall()
{
	delete_option('wlee_options');
}

function wlee_get_plugin_url()
{
	$plugin_url = defined('WP_PLUGIN_URL') ? trailingslashit(WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__))) : trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));
	
	return $plugin_url;
}

//Text auf Email-Adressen überpruefen
function wlee_check($the_content)
{
	//Einfache Pruefung nach @, um die Performance zu erhoehen
	if (strpos($the_content, '@') === false)
	{
		return $the_content;
	}
	
	//
	// Suchen nach email-email
	// <a href="mailto:name@domain.com">name@domain.com</a>
	//
	$pattern = wlee_get_pattern('email', 'email');
	while (preg_match($pattern, $the_content, $regs, PREG_OFFSET_CAPTURE))
	{
		$mail = $regs[1][0];
		$text = $regs[2][0];
		
		$replacement = wlee_get_replacement($mail, $text);

		// gefundene Emails ersetzen
		$the_content = substr_replace($the_content, $replacement, $regs[0][1], strlen($regs[0][0]));
	}

	//
	// Suchen nach email-text
	// <a href="mailto:name@domain.com">Text</a>
	//
	$pattern = wlee_get_pattern('email', 'text');
	while(preg_match($pattern, $the_content, $regs, PREG_OFFSET_CAPTURE))
	{
		$mail = $regs[1][0];
		$text = $regs[2][0];
		
		$replacement = wlee_get_replacement($mail, $text, 'show');

		// Gefundene Emails ersetzen
		$the_content = substr_replace($the_content, $replacement, $regs[0][1], strlen($regs[0][0]));
	}

	//
	// Suchen nach emaillink-email (Email mit Betreff)
	// <a href="name@domain.com?subject=Betreff">name@domain.com</a>
	//
	$pattern = wlee_get_pattern('emaillink', 'email');
	while(preg_match($pattern, $the_content, $regs, PREG_OFFSET_CAPTURE))
	{
		$mail = $regs[1][0] . $regs[2][0];
		$text = $regs[3][0];
		
		//$mail = str_replace( '&amp;', '&', $mail );
		
		$replacement = wlee_get_replacement($mail, $text);

		// Gefundene Emails ersetzen
		$the_content = substr_replace($the_content, $replacement, $regs[0][1], strlen($regs[0][0]));
	}

	//
	// Suchen nach emaillink-text
	// <a href="mailto:name@domain.com?subject=Betreff">Text</a>
	//
	$pattern = wlee_get_pattern('emaillink', 'text');
	while(preg_match($pattern, $the_content, $regs, PREG_OFFSET_CAPTURE))
	{
		$mail = $regs[1][0] . $regs[2][0];
		$text = $regs[3][0];
		
		//$mail = str_replace('&amp;', '&', $mail);

		$replacement = wlee_get_replacement($mail, $text, 'show');

		// Gefundene Emails ersetzen
		$the_content = substr_replace($the_content, $replacement, $regs[0][1], strlen($regs[0][0]));
	}
	
	//
	// Suche nach einfacher Email
	// name@domain.com
	//
	$pattern = '~' . wlee_get_pattern('email') . '~i';//([^a-z0-9]|$)~i';
	while (preg_match($pattern, $the_content, $regs, PREG_OFFSET_CAPTURE))
	{
		$mail = $regs[1][0];
		$replacement = wlee_get_replacement($mail, '');
		
		// Gefundene Emails ersetzen
		$the_content = substr_replace($the_content, $replacement, $regs[1][1], strlen($mail));
	}
	
	return $the_content;
}

//generiert Such-Pattern fuer wlee_check()
function wlee_get_pattern($val1, $val2 = '')
{
	$email_reg = '([\w\.\-]+\@(?:[a-z0-9\.\-]+\.)+(?:[a-z0-9\-]{2,4}))';
	$link_reg = '([?&][\x20-\x7f][^"<>]+)';
	$text_reg = '([\x20-\x7f][^<>]+)';
	
	
	$reg_store = array(
		'email' => $email_reg,
		'emaillink' => $email_reg . $link_reg,
		'text' => $text_reg,
	);
	
	if($val2 == '')
	{
		$pattern = $reg_store[$val1];
	}
	else
	{
		$link = $reg_store[$val1];
		$text = $reg_store[$val2];
		
		$pattern = '~(?:<a [\w "\'=\@\.\-]*href\s*=\s*"mailto:' . $link . '"[\w "\'=\@\.\-]*)>' . $text . '</a>~i';
	}
	
	return $pattern;
}

// Generiert den Replacment-Text für Emails
function wlee_get_replacement($email, $text, $show = 'hide')
{
	global $wlee_options;
	
	$autolink = $wlee_options['autolink'];
	$encrypt = $wlee_options['encrypt'];
	
	//Wenn nicht verschlüsselt werden soll, auf verlinkung pruefen
	if(!$encrypt)
	{
		if($text == '')
		{
			$link_email = ($autolink) ? '<a href="mailto:' . $email . '">' . $email . '</a>' : $email;
		}
		else
		{
			$link_email = '<a href="mailto:' . $email . '">' . $text . '</a>';
		}
		
		return $link_email;
	}
	
	//Encrypt Email
	$email_code = wlee_encrypt($email);
	
	//Encrypt Text
	$text_code = wlee_encrypt($text);
	
	if ($text == '')
	{
		// Sollen nicht verlinkte Emailsadressen verlinkt werden?
		$link_email = ($autolink) ? 'document.write(\'<a href="\' + wlee_decrypt("' . wlee_encrypt('mailto:') . '") + wlee_decrypt("' . $email_code . '") + \'">\' + wlee_decrypt("' . $email_code . '") + \'</a>\');' : 'document.write(wlee_decrypt("' . $email_code . '"));';
	}
	else
	{
		$link_email = 'document.write(\'<a href="\' + wlee_decrypt("' . wlee_encrypt('mailto:') . '") + wlee_decrypt("' . $email_code . '") + \'">\' + wlee_decrypt("' . $text_code . '") + \'</a>\');';
	}
	
	$encrypt_email = '<script language="JavaScript" type="text/javascript">
	<!--
	' . $link_email . '
	document.write(\'<span style="display: none;">\');
	//-->
	</script>
	' . (( $show == 'show' ) ? $text . ' ' : '') . '(' . __("Aktiviere JavaScript, um die Email-Adresse zu sehen") . ')
	<script language="JavaScript" type="text/javascript">
	<!--
	document.write(\'</span>\');
	//-->
	</script>';
	
	return $encrypt_email;
}

// Codiert eine Zeichenkette
function wlee_encrypt($text)
{
	include('includes/wlee_codes.php');
	
	$text_split = str_split($text, 1);
	
	$code = '';
	for($i = 0; $i < count($text_split); $i++)
	{
		$prefix = ($i > 0) ? '.': '';
		
		$id = $text_split[$i];
		
		$code .= $prefix . $ansi_codes[$id];
	}
	
	return $code;
}

//js-file im Header verlinken
function wlee_include_script()
{
	echo "<script type=\"text/javascript\" src=\"" . WLEE_PLUGIN_URL . "js/wlee.js\"></script>\n";
}

//Schreibt Optionen in db
function wlee_set_options($array)
{
	$options = serialize($array);
	
	update_option('wlee_options', $options);
}

//Liest Optionen aus db
function wlee_get_options()
{
	$options = get_option('wlee_options');
	
	if(!is_array($options))
	{
		$options = unserialize(get_option('wlee_options'));
	}
	
	return $options;
}

//Fragt den Update-Server nach Updates ab
function wlee_get_version()
{
	$checkfile_url = "http://plugins.wlabs.de/files/wl-email-encrypter.chk";
	
	$status = array();
	$checkfile = wp_remote_fopen($checkfile_url);
			
	if($checkfile)
	{	
		$file_status = explode('[:]', $checkfile);
		
		$status = array(
			'version' => $file_status[1],
			'changes_url' => $file_status[3],
			'download_link' => $file_status[5],
		);
	}
	
	return $status;
}

//Zeigt in der Plugin-Administration ein eventuelles Update an 
function wlee_check_update($plugin)
{
	if(strpos($plugin, 'wl-email-encrypter.php') !== false)
 	{
		$message = wlee_update_message();
		
		if(!empty($message))
		{
			echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">' . $message . '</div></td></tr>';
		}
	}
}

//Generiert eine Nachricht, wenn Updates vorliegen
function wlee_update_message($page = 'options-page')
{
	$wlee_status = wlee_get_version();
	
	if(version_compare(strval($wlee_status['version']), strval(WLEE_LOCAL_VERSION), '>') == 1)
	{
		$message = __('Eine neue Version von wL Email Encrypter ist erschienen. %sVersion %s jetzt herunterladen%s.');
		
		$message = sprintf($message, '<a href="' . $wlee_status['changes_url'] . '">', $wlee_status['version'], '</a>');
		
		return $message;
	}
	
	return '';
}

?>