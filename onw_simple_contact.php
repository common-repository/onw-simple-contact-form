<?php
/*
Plugin Name: ONW Simple Contact Form
Plugin URI: http://www.olympianetworks.com/projects/onw-simple-contact-form/
Description: This Plugin creates a simple contact form using shortcode in posts or pages.
Version: 2.0.1
Author: John P. Bloch
Author URI: http://www.olympianetworks.com/about-us/developers/john-p-bloch/
Text Domain: onw-simple-contact-form
License: GPL2
*/

/*  Copyright 2009  John P. Bloch  (email : jbloch@olympianetworks.com)

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



if(!function_exists('is_super_admin')){
	function is_super_admin(){
		return true;
	}
}

if(!function_exists('is_multisite')){
	function is_multisite(){
		return false;
	}
}

class ONW_SCF {

	var $dir;
	var $plug_dir;
	var $url;
	var $admin_opt_page = 'onwscf';
	var $admin_opt_url;
	var $email = '';
	var $subject = '';
	var $recaptcha = false;
	var $recaptcha_theme = 'red';
	var $recaptcha_public = false;
	var $recaptcha_private = false;
	var $top_text = '';
	var $legend_text = '';
	var $name_text = '';
	var $email_text = '';
	var $message_text = '';
	var $textarea_size = array( 'width' => 50, 'height' => 10 );
	var $fieldset = false;
	var $cap_edit = 'manage_options';

	function ONW_SCF(){
		$this->__construct();
	}

	function __construct(){
		$this->dir = dirname(__FILE__);
		$this->plug_dir = basename($this->dir);
		$this->url = WP_PLUGIN_URL . '/' . $this->plug_dir;
		$options = get_option('onw-simple-contact');
		if( !$options ){
			$options = $this->update(true,true);
		} elseif(isset($options['labels'])) {
			$options = $this->back_compat_1($options);
		}
		$this->email = isset($options['email']) ? $options['email'] : $this->email;
		$this->subject = isset($options['subject']) ? $options['subject'] : $this->subject;
		$this->recaptcha = isset($options['recaptcha']) ? $options['recaptcha'] : $this->recaptcha;
		$this->recaptcha_theme = isset($options['recaptcha_theme']) ? $options['recaptcha_theme'] : $this->recaptcha_theme;
		$this->recaptcha_public = isset($options['recaptcha_public']) ? $options['recaptcha_public'] : $this->recaptcha_public;
		$this->recaptcha_private = isset($options['recaptcha_private']) ? $options['recaptcha_private'] : $this->recaptcha_private;
		if(function_exists('get_site_option')){
			if(is_multisite()){
				$site_opts = get_site_option('onw-simple-contact-recaptcha');
				$this->recaptcha = isset($site_opts['recaptcha']) ? $site_opts['recaptcha'] : $this->recaptcha;
				$this->recaptcha_public = isset($site_opts['recaptcha_public']) ? $site_opts['recaptcha_public'] : $this->recaptcha_public;
				$this->recaptcha_private = isset($site_opts['recaptcha_private']) ? $site_opts['recaptcha_private'] : $this->recaptcha_private;
			}
		}
		$this->top_text = isset($options['top_text']) ? $options['top_text'] : $this->top_text;
		$this->legend_text = isset($options['legend_text']) ? $options['legend_text'] : $this->legend_text;
		$this->name_text = isset($options['name_text']) ? $options['name_text'] : $this->name_text;
		$this->email_text = isset($options['email_text']) ? $options['email_text'] : $this->email_text;
		$this->message_text = isset($options['message_text']) ? $options['message_text'] : $this->message_text;
		$this->textarea_size = isset($options['textarea_size']) ? array_map( 'absint', $options['textarea_size'] ) : $this->textarea_size;
		$this->fieldset = isset($options['fieldset']) ? $options['fieldset'] : false;
	}

	private function update($return = false,$install = false){
		if(empty($this->email)){
			$this->email = get_bloginfo('admin_email');
		}
		$sub = $install ? sprintf(__('%s has sent you a message','onw-simple-contact-form'),'%email%') : $this->subject;
		$tt = $install ? __('Please use the form below to send an email to the site administrator.','onw-simple-contact-form') : $this->top_text;
		$lt = $install ? __('Send an Email','onw-simple-contact-form') : $this->legend_text;
		/* translators: This refers to the user's name */
		$nt = $install ? _x('Name:','noun','onw-simple-contact-form') : $this->name_text;
		/* translators: This refers to the user's email address */
		$et = $install ? _x('Email:','noun','onw-simple-contact-form') : $this->email_text;
		/* translators: This refers to the form's message field */
		$mt = $install ? __('Message:','onw-simple-contact-form') : $this->message_text;
		if(function_exists('update_site_option')){
			if(!is_multisite()){
				$options = array(
					'email' => $this->email,
					'subject' => $sub,
					'recaptcha' => $this->recaptcha,
					'recaptcha_theme' => $this->recaptcha_theme,
					'recaptcha_public' => $this->recaptcha_public,
					'recaptcha_private' => $this->recaptcha_private,
					'top_text' => $tt,
					'legend_text' => $lt,
					'name_text' => $nt,
					'email_text' => $et,
					'message_text' => $mt,
					'textarea_size' => $this->textarea_size,
					'fieldset' => $this->fieldset
				);
				$update = update_option('onw-simple-contact',$options);
			} else {
				$options = array(
					'email' => $this->email,
					'subject' => $sub,
					'recaptcha_theme' => $this->recaptcha_theme,
					'top_text' => $tt,
					'legend_text' => $lt,
					'name_text' => $nt,
					'email_text' => $et,
					'message_text' => $mt,
					'textarea_size' => $this->textarea_size,
					'fieldset' => $this->fieldset
				);
				$update = update_option('onw-simple-contact',$options);
				$re_opts = array(
					'recaptcha' => $this->recaptcha,
					'recaptcha_public' => $this->recaptcha_public,
					'recaptcha_private' => $this->recaptcha_private
				);
				if(is_super_admin()){
					$supdate = update_site_option('onw-simple-contact-recaptcha',$re_opts);
					$update = ($update && $supdate);
				}
				$options = array_merge($options, $re_opts);
			}
		} else {
			$options = array(
				'email' => $this->email,
				'subject' => $sub,
				'recaptcha' => $this->recaptcha,
				'recaptcha_theme' => $this->recaptcha_theme,
				'recaptcha_public' => $this->recaptcha_public,
				'recaptcha_private' => $this->recaptcha_private,
				'top_text' => $tt,
				'legend_text' => $lt,
				'name_text' => $nt,
				'email_text' => $et,
				'message_text' => $mt,
				'textarea_size' => $this->textarea_size,
				'fieldset' => $this->fieldset
			);
			$update = update_option('onw-simple-contact',$options);
		}
		if($return)
			return $update ? $options : $update;
	}

	private function back_compat_1($opts){
		$no = array();
		$no['email'] = $opts['to_email'];
		$no['subject'] = $opts['def_subj'];
		$no['recaptcha'] = $opts['recaptcha'] == 'off' ? false : true;
		$no['recaptcha_theme'] = $opts['recaptcha_theme'];
		$no['recaptcha_public'] = $opts['recaptcha_pubkey'];
		$no['recaptcha_private'] = $opts['recaptcha_prikey'];
		$no['top_text'] = $opts['labels']['top_text'];
		$no['legend_text'] = $opts['labels']['fieldset'];
		$no['name_text'] = $opts['labels']['name'];
		$no['email_text'] = $opts['labels']['email'];
		$no['message_text'] = $opts['labels']['message'];
		$no['fieldset'] = $opts['fieldset'] == 'yes' ? true : false;
		$no['textarea_size'] = array_map('absint',$opts['box_size']);
		return $no;
	}

	function menu(){
		/* translators: This is the plugin's name (as seen in the menu and administration page) */
		$name = __('ONW Simple Contact','onw-simple-contact-form');
		add_options_page( $name, $name, $this->cap_edit, 'onwscf', array($this,'menu_add') );
	}

	function menu_add(){

		// Set an array with the possible values for the recaptcha theme
		$recaptcha_theme_vals = array( 'red', 'white', 'blackglass', 'clean' );
		$recaptcha_theme_display_vals = array(
			__('Red','onw-simple-contact-form'),
			__('White','onw-simple-contact-form'),
			__('Black Glass','onw-simple-contact-form'),
			_x('Clean','adjective','onw-simple-contact-form')
		);
		
		$alert = false;

		if( isset($_POST['onw_scf_submit']) && $_POST['onw_scf_submit'] == 'onw_scf_submit' ){ // if the configuration form was submitted

			// update the options with the new values
			$this->email = $_POST['to_email'];
			$this->subject = $_POST['def_subj'];
			if(is_super_admin()):
				$this->recaptcha = ( isset($_POST['recaptcha']) && $_POST['recaptcha'] == 'on' )? true: false;
				$this->recaptcha_public = $_POST['recaptcha_pubkey'];
				$this->recaptcha_private = $_POST['recaptcha_prikey'];
			endif;

			// And update
			$this->update();
			
			$alert = true;

		} elseif( isset($_POST['onw_scf_submit']) && $_POST['onw_scf_submit'] == 'onw_scf_submit2' ){ // If the display form was submitted

			// Set the value for the fieldset toggle
			$this->fieldset = ( isset($_POST['display-fieldset']) && $_POST['display-fieldset'] == 'yes' )? true: false;

			$this->recaptcha_theme = ( in_array($_POST['recaptcha_theme'],$recaptcha_theme_vals) )? $_POST['recaptcha_theme']: 'red';

			// Compile the options updates into an array
			$this->top_text = $_POST['upper-text-label'];
			$this->legend_text = empty($_POST['fieldset-legend']) ? '' : $_POST['fieldset-legend'];
			$this->name_text = $_POST['name-label'];
			$email->email_text = $_POST['email-label'];
			$this->message_text = $_POST['message-label'];
			$this->textarea_size = array (
				'width' => absint($_POST['m-box-width']),
				'height' => absint($_POST['m-box-height'])
			);

			// Update the options
			$this->update();
			
			$alert = true;

		}
		/*
		@to_do add update functionality to change managing capabilities
		*/

		// Write the Header HTML
		?><div class="wrap">
		<h2>ONW Simple Contact Form</h2><?php
		if($alert){
			?><div id="setting-error-settings_updated" class="updated settings-error"><p><strong><?php
			_e('Settings saved.', 'onw-simple-contact-form');
			?></strong></p></div><?php
		}
		?><ul class="subsubsub">
			<li>
				<a href="<?php echo $this->admin_opt_url; ?>"<?php if( !isset($_GET['sp']) || $_GET['sp'] != 'display' ){ echo ' class="current"'; } ?>>
				<?php _e('Configuration','onw-simple-contact-form'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo $this->admin_opt_url; ?>&sp=display"<?php if( isset($_GET['sp']) && $_GET['sp'] == 'display' ){ echo ' class="current"'; } ?>>
				<?php echo _x('Display','noun','onw-simple-contact-form'); ?>
				</a>
			</li>
		</ul>

		<?php

		// If you're looking at the display options page, display the proper page
		if( isset($_GET['sp']) && $_GET['sp'] == 'display' ):
		?>

		<form name="form1" method="post" action="<?php echo $this->admin_opt_url; ?>&sp=display&updated=true">
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row">
						<label for="display-fieldset">
						<?php
						/* translators: the fieldset is a box surrounding the contact form. */
						_e('Would you like to display the Fieldset?','onw-simple-contact-form'); ?>
						</label>
					</th>
					<td>
						<input type="checkbox" name="display-fieldset" id="display-fieldset" value="yes"<?php if($this->fieldset) { echo ' checked="checked"'; } ?> onclick="javascript:if(this.checked){document.getElementById('fieldset-legend').disabled = false; } else { document.getElementById('fieldset-legend').disabled = true; };" />&nbsp;&nbsp;<span class="description">
						<?php _e('The fieldset is the box around the whole form.','onw-simple-contact-form'); ?>
						</span><br />
						<label for="fieldset-legend">
						<?php
						/* translators: The fieldset text is the 'legend', the text that appears as part of the fieldset */
						_e('Fieldset Text:','onw-simple-contact-form'); ?>
						</label>&nbsp;&nbsp;<input type="text" name="fieldset-legend" id="fieldset-legend" size="40" value="<?php echo $this->legend_text; ?>"<?php if( !$this->fieldset ){ echo ' disabled="disabled"'; } ?> />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="recaptcha_theme">
						<?php _e('Recaptcha Color Scheme:','onw-simple-contact-form'); ?>
						</label>
					</th>
					<td>
						<select name="recaptcha_theme" id="recaptcha_theme">
						<?php
						foreach($recaptcha_theme_vals as $k => $rtv){
						$default_selected = ( $rtv == $this->recaptcha_theme )? " selected='selected'": "";
						echo "
						<option value='$rtv'$default_selected>".$recaptcha_theme_display_vals[$k]."</option>";
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label>
						<?php _e('Input Field Labels','onw-simple-contact-form'); ?>
						</label>
					</th>
					<td>
						<label for="upper-text-label">
						<?php
						/* translators: This refers to the text that appears above the form */
						_e('Upper Text:','onw-simple-contact-form'); ?>
						</label> <input type="text" name="upper-text-label" id="upper-text-label" size="70" value="<?php echo $this->top_text; ?>" /><br />
						<label for="name-label">
						<?php echo _x('Name:','noun','onw-simple-contact-form'); ?>
						</label> <input type="text" name="name-label" id="name-label" size="70" value="<?php echo $this->name_text; ?>" /><br />
						<label for="email-label">
						<?php _e('Email Address:','onw-simple-contact-form'); ?>
						</label> <input type="text" name="email-label" id="email-label" size="70" value="<?php echo $this->email_text; ?>" /><br />
						<label for="message-label">
						<?php _e('Message:','onw-simple-contact-form'); ?>
						</label> <input type="text" name="message-label" id="message-label" size="70" value="<?php echo $this->message_text; ?>" /><br />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php _e('Message Box Size:','onw-simple-contact-form'); ?>
					</th>
					<td>
						<label for="m-box-width">
						<?php _e('Width:','onw-simple-contact-form'); ?>
						</label>
						<?php
						$width_input = "<input type='text' name='m-box-width' id='m-box-width' size='3' value='{$this->textarea_size['width']}' />";
						/* translators: The %s will be an input field with numbers. */
						printf( __('%s columns','onw-simple-contact-form'), $width_input );
						?> | <label for="m-box-height">
						<?php _e('Height:','onw-simple-contact-form'); ?>
						</label>
						<?php
						$height_input = "<input type='text' name='m-box-height' id='m-box-height' size='3' value='{$this->textarea_size['height']}' />";
						/* translators: The %s will be an input field with numbers. */
						printf( __('%s rows','onw-simple-contact-form'), $height_input );
						?>
					</td>
				</tr>
				</tbody>
			</table>
			<input type="hidden" name="onw_scf_submit" id="onw_scf_submit" value="onw_scf_submit2" />
			<p class="submit"><input type="submit" name="submit" id="submit" value="<?php
			_e('Update Display Settings','onw-simple-contact-form');
			?>" class="button-primary" /></p>
		</form>

		<?php
		/*
		@to_do add admin menu page for updating managing capabilities.
		*/

		// Otherwise, display the default configuration page.
		else:

		?>
		<form name="form1" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=onwscf&updated=true">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="to_email">
							<?php
							_e('Recipient Email Address:','onw-simple-contact-form');
							?>
							</label>
						</th>
						<td>
							<input type="text" name="to_email" id="to_email" value="<?php echo $this->email; ?>" size="40" />&nbsp;<br /><span class="description"><?php
							_e('The recipient email address is the default email address to which the form will send emails. By default, the recipient email address is the same as the admin email set in the General Settings tab, but you can change it here.  You can also specify a different email address in the shortcode itself.','onw-simple-contact-form');
							?></span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="def_subj"><?php
							_e('Default Subject','onw-simple-contact-form');
							?></label>
						</th>
						<td>
							<input type="text" name="def_subj" id="def_subj" value="<?php
							$onw_def_subj = stripslashes($this->subject);
							echo str_replace('"','&rdquo;',$onw_def_subj);
							?>" size="80" />&nbsp;<br /><span class="description"><?php
							_e('The default subject is the subject line in the email that forms will use unless another subject line is given in the shortcode.','onw-simple-contact-form');
							?><br />
							<br /><?php
							printf(__('To use the given name of the user (the name they provide in the form) use %s','onw-simple-contact-form'),'</span>%name%<span class="description">');
							?><br />
							<?php
							printf(__('To use the given email of the user, use %s','onw-simple-contact-form'),'</span>%email%<span class="description">');
							?></span>
						</td>
					</tr>
					<?php
					if(is_super_admin()):
					?>
					<tr>
						<th scope="row">
							<label for="recaptcha"><?php
							_e('Enable reCAPTCHA?','onw-simple-contact-form');
							?></label>
						</th>
						<td>
							<select name="recaptcha" id="recaptcha"<?php
							if( empty($this->recaptcha_public) || empty($this->recaptcha_private) ) {
								// if either one of the keys is empty, disable the dropdown
								echo ' disabled="disabled"';
							}
							?>>
							<?php
							if( !empty($this->recaptcha_public) && !empty($this->recaptcha_private) ){
							?>
								<option value="on"><?php
								_e('Enabled','onw-simple-contact-form');
								?></option>
							<?php
							}
							?>
								<option value="off"<?php if(!$this->recaptcha) { echo ' selected="selected"'; } ?>><?php
								_e('Disabled','onw-simple-contact-form');
								?></option>
							</select>&nbsp;<br /><span class="description"><?php
							printf( __( 'reCAPTCHA is a highly effective (and FREE!) bot check web application. It asks those filling out your form to type a slightly distorted word before it will process the form. %1$s Before you can enable reCAPTCHA, you have to go to %2$s and sign up for a free account. Then, enter the public key and private key from your account in the fields below. Once you have saved the keys here, you will be able to activate reCAPTCHA.','onw-simple-contact-form'), '<br />', '<a href="https://admin.recaptcha.net/accounts/signup/" target="_blank">reCaptcha.net</a>' );
							?></span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="recaptcha_pubkey"><?php
							_e('reCAPTCHA Public Key','onw-simple-contact-form');
							?></label>
						</th>
						<td>
							<input type="text" name="recaptcha_pubkey" id="recaptcha_pubkey" value="<?php echo $this->recaptcha_public; ?>" size="40" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="recaptcha_prikey"><?php
							_e('reCAPTCHA Private Key','onw-simple-contact-form');
							?></label>
						</th>
						<td>
							<input type="text" name="recaptcha_prikey" id="recaptcha_prikey" value="<?php echo $this->recaptcha_private; ?>" size="40" />
						</td>
					</tr>
					<?php
					endif;
					?>
				</tbody>
			</table>
			<input type="hidden" name="onw_scf_submit" id="onw_scf_submit" value="onw_scf_submit" />
			<p class="submit"><input type="submit" name="submit" id="submit" value="<?php
			_e('Update Settings','onw-simple-contact-form');
			?>" class="button-primary" /></p>
		</form>
		<?php
		endif;

		// Finish off the HTML
		?>
		</div><?php
	}

	function onw_scf_addbuttons() {

		global $wp_post_types, $_wp_post_type_features;

		$caps = array('edit_posts','edit_pages');

		if(is_array($wp_post_types)){
			if(!is_array($wp_post_types))
				$wp_post_types = array();
			foreach($wp_post_types as $n => $pt):
				if(isset($_wp_post_type_features[$n]['editor']) && $_wp_post_type_features[$n]['editor']){
					if(!in_array($pt->cap->edit_posts, $caps)){
						$caps[] = $pt->cap->edit_posts;
					}
				}
			endforeach;
		}

		$caps = array_filter( $caps, create_function('$a',' return current_user_can($a) ? $a : false; ') );

		// check user permission
		if ( empty($caps) )
			return;

		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {

			add_filter("mce_external_plugins", array($this,"onwscf_mce_plugin_load"));
			add_filter('mce_buttons', array($this,'onwscf_mce_register_button'));

		}

	}

	function onwscf_mce_plugin_load($plugin_array){

		// Set the url of the editor_plugin.js file
		$plug = WP_PLUGIN_URL . '/onw-simple-contact-form/js/editor_plugin.js';

		// Add it to the plugin array
		$plugin_array['onwsc'] = $plug;

		// Return the plugin array
		return $plugin_array;

	}

	function onwscf_mce_register_button($buttons){

		// Add the button's name to the array of buttons
		array_push($buttons, "separator", "onwscb");

		// Return the buttons array
		return $buttons;

	}

	function onw_form_output ($error = false, $errors = null) {

		// Set up error check and error values.
		if($error && is_wp_error($errors)){
			$ne = $errors->get_error_message('name');
			$name_extra = empty($ne) ? '' : $errors->get_error_message('name') . '<br />';
			$ee = $errors->get_error_message('email');
			$email_extra = empty($ee) ? '' : $errors->get_error_message('email') . '<br />';
			$me = $errors->get_error_message('message');
			$message_extra = empty($me) ? '' : $errors->get_error_message('message') . '<br />';
			$be = $errors->get_error_message('bot_check');
			$bot_extra = empty($be) ? '' : $errors->get_error_message('bot_check') . '<br />';
		} else {
			$name_extra = '';
			$email_extra = '';
			$message_extra = '';
			$bot_extra = '';
		}

		// Put labels and dimensions into their own variables for easier access
		$inp = array(
			'name' => '',
			'email' => '',
			'message' => ''
		);

		foreach($inp as $k => $v)
			$inp[$k] = isset($_POST[$k]) ? $_POST[$a] : "";

		// Put the form into a variable...
		$form_output = '';
		if(!empty($this->top_text))
			$form_output .= '<p>'.$this->top_text.'</p>';
		$form_output .= '
		<form id="onw_contact_form" name="onw_contact_form" method="post" action="#onw_contact_form">
		';

		// If the fieldset toggle is checked...
		if( $this->fieldset ){

			// Add the fieldset and legend
			$form_output .= '
			<fieldset>
			<legend>'.$this->legend_text.'</legend>';

		} // End fieldset IF

		$form_output .= '
		<p>'.
		__('All fields are required.','onw-simple-contact-form') .
		'</p>
		<p>'.$name_extra.'<label for="uName">'.$this->name_text.'</label> <input type="text" name="uName" id="uName" value="'.$inp['name'].'" /></p>
		<p>'.$email_extra.'<label for="uEmail">'.$this->email_text.'</label> <input type="text" name="uEmail" id="uEmail" value="'.$inp['email'].'" /></p>
		<p>'.$message_extra.'<label for="uMessage">'.$this->message_text.'</label><br />
		<textarea id="uMessage" name="uMessage" rows="'.$this->textarea_size['height'].'" cols="'.$this->textarea_size['width'].'">'.$inp['message'].'</textarea></p>';

		$honeypot = __('Do not fill out this form field:','onw-simple-contact-form');

		if( !$this->recaptcha ) { // if reCAPTCHA is off, use the basic check
			$form_output .= '<p id="bot-check">'.$bot_extra.'<img src="';
			$form_output .= $this->url;
			$form_output .= '/check.jpg" alt="" align="left" /><input type="text" name="uCheck" id="uCheck" size="2" value="" />
			<input type="hidden" name="uVal" id="uVal" value="true" />
			<div style="display:none;">'.$honeypot.'<input name="emailMessageBody" value="" /></div>
			<p><input type="submit" name="uSend" id="uSend" value="'.__('Send Email','onw-simple-contact-form').'" /></p>
			';

			// If the fieldset toggle is checked...
			if( $this->fieldset ){

				// Close the fieldset
				$form_output .= '
				</fieldset>
				';

			}

			$form_output .= '
			</form>';
		} else { // Otherwise, use reCAPTCHA

			$form_output .= '<p id="bot-check">'.$bot_extra.''.onw_recaptcha_get_html($this->recaptcha_public).'</p>
			<input type="hidden" name="uVal" id="uVal" value="true" />
			<div style="display:none;">'.$honeypot.'<input name="emailMessageBody" value="" /></div>
			<p><input type="submit" name="uSend" id="uSend" value="'.__('Send Email','onw-simple-contact-form').'" /></p>
			';

			// If the fieldset toggle is checked...
			if( $this->fieldset ){

				// Close the fieldset
				$form_output .= '
				</fieldset>
				';

			}
			$form_output .= '
			</form>';
		} // End reCAPTCHA if/elseif

		// And return that variable.
		return $form_output;
	} // End of onw_form_output function

	function form_js() {

		// Prepare the javascript and assign it to a variable...
		$js_output = '<script language="Javascript" type="text/javascript">';
		if($this->recaptcha){
			$js_output .= '
			var RecaptchaOptions = { theme: "' . $this->recaptcha_theme . '"';
			$lang = WPLANG;
			if(!empty($lang)){
				$supported = array(
					'nl_NL'=>'nl',
					'fr_FR'=>'fr',
					'de_DE'=>'de',
					'pt_PT'=>'pt',
					'pt_BR'=>'pt',
					'ru_RU'=>'ru',
					'es_ES'=>'es',
					'tr_TR'=>'tk'
				);
				if(in_array($lang, $supported)){
					$js_output .= ', lang: "' . $suported[$lang] . '"';
				}
			}
			$js_output .= ' };';
		}
		$js_output .= '
		</script>';

		// And return that variable.
		return $js_output;
	} // End of form_js() function

	function onw_form_validate(){

		// Pass the 'name' input into a variable
		$u_name = isset( $_POST['uName'] ) ? $_POST['uName'] : '';
		// Pass the 'email' input into a variable
		$u_email = isset( $_POST['uEmail'] ) ? $_POST['uEmail'] : '';
		// Pass the 'message' input into a variable
		$u_message = isset( $_POST['uMessage'] ) ? stripslashes($_POST['uMessage']) : '';

		// Set an error array
		$errors = array();

		if( !$this->recaptcha ) { // if reCAPTCHA is off, validate the basic check
			// And pass the robot check into a variable
			$u_check = isset( $_POST['uCheck'] ) ? $_POST['uCheck'] : 0;
			$u_check = intval( $u_check );
			if ( $u_check !== 11 )
				$errors['bot_check'] = '<span style="color:red;">'.__('Please prove you are not a robot by correctly answering the question.','onw-simple-contact-form').'</span>';
		} else { // Otherwise, validate the reCAPTCHA
			$resp = onw_recaptcha_check_answer($this->recaptcha_private, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
			if( !$resp->is_valid ) {
				$errors['bot_check'] = '<span style="color:red;">'.__('The reCAPTCHA was not answered correctly. Please try again.','onw-simple-contact-form').'</span>';
			}
		} // end of reCAPTCHA if

		// Sanitize the name and message
		$u_name = htmlspecialchars( $u_name, ENT_QUOTES );
		// If it's empty, add an error.
		if(empty($u_name))
			$errors['name'] = '<span style="color:red;">'.__('Please provide your name.','onw-simple-contact-form').'</span>';
		// Remove HTML tags with a regular expression
		$u_message = preg_replace( '/<[^>]+>/', '', $u_message );
		// If it's empty, add an error.
		if(empty($u_message))
			$errors['message'] = '<span style="color:red;">'.__('Please provide some text to be sent in the email.','onw-simple-contact-form').'</span>';

		// If the email address is not in the right format,
		if ( !is_email( $u_email ) )
		// End the function.
			$errors['email'] = '<span style="color:red;">'.__('Please provide a real email address.','onw-simple-contact-form').'</span>';
		if(empty($errors)){
			// Pass the inputs into another associative array...
			$onw_inputs = array('name'=>$u_name, 'email'=>$u_email, 'message'=>$u_message);
		} else {
			$onw_inputs = new WP_Error();
			foreach($errors as $k=>$e){
				$onw_inputs->add($k,$e);
			}
		}

		// And return it.
		return $onw_inputs;

	} // End of onw_form_validate() function

	function onw_compile_the_email($onw_inputs) {

		// First prepare the message body with the message, time sent,
		// and name/email of sender.
		$onw_body = $onw_inputs['message']."\n";
		/* translators: email sent date format, see http://php.net/date */
		$sent_date = date( __('h:i:s A e, l F j Y') );
		/* translators: String is formatted like this: "Sent {date and time} by {name} ({email address})" */
		$onw_body .= sprintf( __('Sent %1$s by %2$s (%3$s).','onw-simple-contact-form'), $sent_date, $onw_inputs['name'], $onw_inputs['email'] );

		$look_for = array('%name%','%email%');
		$replace_with = array($onw_inputs['name'],$onw_inputs['email']);
		$onw_subject = str_replace($look_for,$replace_with,$this->subject);

		// Prepare the 'from' header w/ the input email
		$onw_from = 'From: '.$onw_inputs['email'];

		// Prepare the 'to' variable. Uses the blog's admin email address
		if ( is_email($this->email) )
			$onw_to = $this->email;
		else
			$onw_to = get_bloginfo('admin_email');

		// Prepare the indexed array to be returned by inserting the variables...
		$return_info = array($onw_to, $onw_subject, $onw_body, $onw_from);

		// And return the array.
		return $return_info;
	} // End of onw_compile_the_email() function

	function onw_aggregate_function($atts){

		// Extract the attributes (To: Email)
		extract( shortcode_atts( array(
			'to_email' => $this->email,
			'onw_recaptcha' => $this->recaptcha,
			'subject' => $this->subject
		), $atts ) );

		$opts = array('on','off');

		if(in_array($onw_recaptcha,$opts)){
			if($this->recaptcha)
				$this->recaptcha = $onw_recaptcha == 'on' ? true : false;
		}

		$this->email = $to_email;

		$this->subject = $subject;

		// Check to see if the form has been submitted
		if ( !isset($_POST['uVal']) ) { // If it hasn't...

			// The output will be the javascript...
			$output = $this->form_js();
			// And the form itself.
			$output .= $this->onw_form_output();

		} else { // Otherwise...

			// Validate the form inputs,
			$onw_form_inputs = $this->onw_form_validate();
			if ( !is_wp_error($onw_form_inputs) ) {
				// If the honeypot hasn't caught any flies, send the email.
				if(isset($_POST['emailMessageBody']) && empty($_POST['emailMessageBody'])){
					// Compile the email data,
					$mail_inputs = $this->onw_compile_the_email($onw_form_inputs);
					// Replace all bare LF's with CRLF's,
					$mail_inputs = preg_replace("#(?<!\r)\n#si", "\r\n", $mail_inputs);
					// And send it.
					$onw_the_email = mail($mail_inputs[0], $mail_inputs[1], $mail_inputs[2], $mail_inputs[3]);
				}
				// Also, tell the user that the email has sent.
				$output = '<p>'.__('Your email has been sent.','onw-simple-contact-form').'</p>';
			} else {
				$output = $this->form_js();
				$have_errors = true;
				$output .= $this->onw_form_output($have_errors,$onw_form_inputs);
			}

		} // End of if() to see if form was submitted

		// Return the output to put into the story content.
		return $output;

	} // End of onw_aggregate_function()

}

function ONW_SCF_init_wrapper(){
	load_plugin_textdomain( 'onw-simple-contact-form', '', basename(dirname(__FILE__)) . '/language' );
	if(defined('WP_ADMIN') && WP_ADMIN){
		$onwscf = new ONW_SCF();
		$onwscf->admin_opt_url = get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=' . $onwscf->admin_opt_page;
		add_action('admin_menu',array($onwscf,'menu'));
		$onwscf->onw_scf_addbuttons();
	}
}

function ONW_SCF_shortcode_wrapper($atts){
	$onwscf = new ONW_SCF();
	if($onwscf->recaptcha)
		include_once( $onwscf->dir . '/recaptchalib.php' );
	return $onwscf->onw_aggregate_function($atts);
}

add_action('init','ONW_SCF_init_wrapper');
add_shortcode('onw_simple_contact_form','ONW_SCF_shortcode_wrapper');
?>