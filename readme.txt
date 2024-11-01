=== wL Email Encrypter ===
Contributors: wLabs
Donate link: 
Tags: comments, spam, protect, encrypt, email, hide, emails, javascript, easy
Requires at least: 2.9
Tested up to: 3.0.5
Stable tag: 1.0.5

This plugin encrypted e-mail addresses to protect and hide them from bots and harvesters.

== Description ==

wL Email Encrypter scans pages, articles, comments or RSS feeds for email addresses and encrypts them using JavaScript. This protects the email addresses from bots and harvesters, because they don't recognize the encoded emails.

A visitor who has not activated JavaScript, gets displayed a userdefined message about it.

wL Email Encrypter also recognizes with mailto: email addresses linked with subject information and others, and protect this information also.

== Installation ==

1. Upload the `wl-email-encrypter` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Check the options page for your personal settings

== Frequently Asked Questions ==

= What languages are available? =

So far, only German is available, but in future, other languages will be supported.

= What emails will be encrypted? =

wL Email Encrypter scans each page, post, comment and rss-feed for all emails, also linked emails with subjects like `mail@domain.com?subject=text` and encrypted them. No shortcodes or something else required.

= What will visitors see without Javascript? =

Visitors without or with disabled JavaScript will see a userdefined message that JavaScript is required. In future there will be an optional HTML solution like this: `mail [at] domain [dot] com`

== Screenshots ==

1. Options-Page
2. Code-Example for an encrypted email-adress

== Changelog ==

= 0.5.1 =
* Added: Userdefined alternative message
* New design for the options page
* old update function removed

= 0.5 =
* New scan function
* better performance
* uninstall function added
* bug fixed at the options-page

= 0.4 =
* New Plugin-Name

= 0.3.1 =
* bug fixed

= 0.3 =
* homepage-link added
* 2 bugs fixed

= 0.2 =
* new: Update function
* bug fixed

= 0.1 =
* first version

== Upgrade Notice ==

= 0.5.1 =
New option for an userdefined alternative message added

= 0.5 =
New functions with better performance were added and a bug was fixed.

= 0.4 =
The Plugin has been renamed.

= 0.3.1 =
A bug was fixed.

= 0.3 =
Homepage-link were added and 2 bugs were fixed.

= 0.2 =
An update function were added and a bug was fixed.

= 0.1 =
first version