<?php

/**
 * Internationalization file for the AppNetLogin extension.
 *
 * @file AppNetLogin.i18n.php
 *
 * @author Chris Neale
 */

$messages = array();

/** English
 * @author David Raison
 */
$messages['en'] = array(
	'appnetlogin' => 'Sign in using ADN',
	'appnetlogin-signup' => 'An App.net account is required to edit this wiki. You can create an account and sign in by clicking this button.<!-- You can register to this wiki using your ADN account -->',
	'appnetlogin-tietoaccount' => 'You are currently logged into this wiki as $1.<br/>You may sign in with ADN to tie your App.net account to your existing mediawiki account.',
	'appnetlogin-desc' => 'Register and log in to a mediawiki using your ADN account',
	'appnetlogin-alreadyloggedin' => 'You\'re already logged in.',
	'appnetlogin-couldnotconnect' => 'Could not connect to ADN. Refresh the page or try again later.'
);

/** Message Documentation
 * @author David Raison
 */
$messages['qqq'] = array(
	'appnetlogin' => 'Link of the special page',
	'appnetlogin-signup' => 'Explains users they can register and sign up to the wiki with their ADN account. Used on specialpage default.',
	'appnetlogin-tietoaccount' => 'Message displayed on the default specialpage when a logged in user visits it. (NOT in use)',
	'appnetlogin-desc' => 'Description of the extension, see setup file',
	'appnetlogin-alreadyloggedin' => 'Message displayed on the default specialpage. tietoaccount replacement.',
	'appnetlogin-couldnotconnect' => 'Tell the user the connection to the ADN oauth server did not work.'
);
