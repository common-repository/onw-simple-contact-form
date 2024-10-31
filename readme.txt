=== ONW Simple Contact Form ===
Contributors: JohnPBloch
Donate link: http://www.olympianetworks.com/donate/
Tags: contact, form, simple, email, multisite, contact form, email form
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 2.0.1

ONW Simple Contact Form is a basic form with reCAPTCHA for sending email from your blog.

== Description ==

ONW Simple Contact Form is a very simple form which you can add to any page or post (or any custom post type which uses the editor) using either the built in tinyMCE editor, or (for slightly more flexibility) a shortcode.  The form is not customizeable, although you can alter the appearance with CSS.

It has the option of using [reCAPTCHA](http://www.recaptcha.net), or a very primitive version if you don't want to use reCAPTCHA. The ONW reCAPTCHA integration *WILL NOT* cause any conflicting errors with other reCAPTCHA installations on your WordPress. It asks for a name, email address, and body text, and then emails all of it to an email you specify (if you don't give it an address, the form will default to the blog's administrative email address).

ONW Simple Contact Form has been fully tested with WordPress 3.0 Multisite as well. In a multisite environment, all settings except reCAPTCHA integration are handled on a blog by blog basis: the default email will be that of the administrative email for each blog, each blog may define custom label names, subjects, etc. However, only a Super Admin user may modify the reCAPTCHA information, and all changes to that information will be site-wide. That means a multisite install can register one global key with recaptcha.net for all subdomains of their site and all sites will automatically have full recaptcha support.

As of version 2.0, ONW Simple Contact Form also fully supports l10n. If you would like to submit a translation, the .pot file is in the `/languages/` directory of the plugin. In addition to the .pot file, a full translation should include translations for the tinyMCE button. Please see [Other Notes](http://wordpress.org/extend/plugins/onw-simple-contact-form/other_notes/) for more details.

== Installation ==

1. Upload the `/onw-simple-contact-form/` directory and all its files to the `/wp-content/plugins/` directory (or use the native plugin installer).
2. Activate the plugin through the 'Plugins' menu in your WordPress dashboard
3. Edit the post or page into which you want to place the contact form. Set the cursor to the spot you would like the form to be inserted.
4. Click the ONW Simple Contact Form button (top row of buttons, right side). The button looks like an opened envelope.
5. In the popup box, you can either set an email address and subject for the form, or you can leave the form blank and use the defaults (set under the options page).
6. You still have the option of adding the shortcode manually. The basic shortcode is `[onw_simple_contact_form]`. You can specify a non-default email address buy adding the `to_email` argument to the shortcode to define the recipient address. If you use this, the shortcode will look like `[onw_simple_contact_form to_email="youremail@example.com"]`. If you use anything other than an email address (or leave the argument out altogether) the email will send to the blog's admin address.
7. If you want to use reCAPTCHA, [sign up for a free account](https://admin.recaptcha.net/accounts/signup/). Then enter the public and private API keys in the appropriate fields in the ONW options page (if you already have a plugin listed above, this should already be done). Finally, select "enabled" on the form to turn reCAPTCHA on. Also, you have the option of overriding the settings in an individual post by adding the `onw_recaptcha` argument. You cannot set a reCAPTCHA override with the tinyMCE button. So if you wanted to disable reCAPTCHA for a certain form, you would write `[onw_simple_contact_form onw_recaptcha="off"]`.  The only values that the plugin wil recognize are 'on' and 'off'.  Anything else will cause the form to revert to the default settings (as set in the settings tab for ONW Simple Contact).
8. For the subject of the email, you will have a default setting, but can override the default with either the tinyMCE button or manually in the shortcode. Also, in the subject, the email will replace `%name%` with the user's name (as they entered it) and will replace `%email%` with the user's email (as they entered it). The shortcode argument for the subject is `subject`.

== Frequently Asked Questions ==

None yet.

== Screenshots ==

1. This is how you add the form to a page or post.
2. This is what the form looks like without reCAPTCHA enabled.
3. This is what the form looks like with reCAPTCHA enabled.
4. This is how you use the tinyMCE button (opened envelope).

== Changelog ==

= 2.0.1 =

* Fixed a bug that caused a major error

= 2.0 =

* Refactored entire plugin to increase efficiency
* Added l10n support (.pot file in languages directory)
* Added support for multisite as of WordPress 3.0 (**WPMU is not supported**)
* Added a 'honeypot' field to the form which will further reduce spam
* Removed support for other reCAPTCHA plugins (see [Other Notes](http://wordpress.org/extend/plugins/onw-simple-contact-form/other_notes/) for explanation)

= 1.9.1 =

* Fixed a small bug that caused an error on the update screen for the form's style.

= 1.9 =

* Added reCAPTCHA theme support. Users can now use the admin panel to change the look and feel of the form
* Added settings checks for other reCAPTCHA plugins to automatically copy api key and theme settings for onw-simple-contact-form

= 1.8 =

* Fixed a bug in the TinyMCE functions which sometimes caused the editor to disappear
* Added a 'display' tab to the admin page where admins can change the text in the form, as well as remove the fieldset or change the size of the message box.

= 1.7.2 =

* Fixed a problem with the plugin when ReCAPTCHA is not activated.
* Added `stripslashes()` to the email body to get rid of backslashes.

= 1.7.1 =

* Fixed a problem causing SMTP 451 errors (bare LF's in the email). You don't really need to update unless you're getting that error when using it.

= 1.7 =

* Added a custom tinyMCE button to automatically insert the shortcode.
* Minor bug fixes and cleaned up code / documentation.
* Tested through WordPress 2.9

= 1.6.1 =

* Fixed a bug in the reCAPTCHA integration.

= 1.6 =

* Updated the version of WordPress up to which the form has been tested. It has been tested up to the latest version of WordPress.
* Added functionality to allow the user to change the subject line of the email in the admin menu or in the shortcode.

= 1.5 =

* Fixed a bug that would have disabled spam checks entirely if user accidentally wrote neither `on` nor `off` in the `onw_recaptcha` argument of the shortcode.

= 1.4 =

* Added full reCAPTCHA integration (with the option of using it or not).
* ONW reCAPTCHA integration WILL NOT cause conflicts with other reCAPTCHA installations on your blog (such as the wp-recaptcha plugin).

= 1.3 =

* Replaced the `filter_var()` functions with other input validation and sanitization functions. This fixes a compatibility problem for users who do not have PHP 5 (or who have the `filter_var()` function disabled).

= 1.2.3 =
* Added the `get_bloginfo('wpurl')` function to the src of the image for spam checks. This standardizes the setting so that all permalinks types will be supported.

= 1.2.2 =
* Changed `src="/wp-content/..."` to `src="./wp-content/..."`

= 1.2.1 =
* Bug fix: Fixed the filepath to the bot-check file.

= 1.2 =
* Added the `to_email` argument to the shortcode handler.  You can now specify the address to send the email to.

= 1.1 =
* Added the bot check at the end of the form

= 1.0 =
* First stable version

== Other Notes ==

= L10n =

In addition to the normal .pot file in the languages directory, ONW Simple Contact Form has text in the tinyMCE dialog box that it uses. The file to be translated is `/js/langs/en_dlg.js`. You will need to open this file in a plain text editor (such as notepad, not a word processor like MS Word) and replace the English in the double quotes. Then rename the file to `<lang>_dlg.js` where `<lang>` is your language code.

Please send all translations to l10n@olympianetworks.com

= Cross Plugin Compatibility =

When somebody asked if I could adopt support for other reCAPTCHA plugins for WordPress, my initial reaction was 'Sure! that shouldn't be too hard!' So I slapped something together and got it out the door in v1.9. Since then, in developing v2.0, this has turned out to be nothing but a headache. Which normally wouldn't be enough to make me drop support for a feature. But it did make me ponder my reasons for putting it in in the first place and evaluate the worth of such a feature.

As of this development cycle I will no longer be maintaining cross-plugin compatibility with any reCAPTCHA plugins. reCAPTCHA is a service that does not change very often. Expecting to need to sync your settings between reCAPTCHA plugins is a contingency plan for something that will most likely never happen (reCAPTCHA changing anything about their API), and if it did ever happen, it would be of such little consequence that it's not worth planning for. Moreover, being unable to develop other plugins, I would rather not support something I have no control over. One change in any plugin's structure means adding more bloated code to ONW Simple Contact Form which will never be used.

The main reason I am dropping support for other plugins, however, is for the sake of simplicity in WordPress 3.0's Multisite environment. Because I modified the way the plugin data is stored for multisite installations, I can no longer offer this support in a feasible manner. So I cut it.

I hope you understand.
