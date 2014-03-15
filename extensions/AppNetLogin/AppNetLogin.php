<?php
/**
 * AppNetLogin.php
 * Botched by Chris Neale
 * Written by David Raison, based on the guideline published by Dave Challis at http://blogs.ecs.soton.ac.uk/webteam/2010/04/13/254/
 * @license: LGPL (GNU Lesser General Public License) http://www.gnu.org/licenses/lgpl.html
 *
 * @file AppNetLogin.php
 * @ingroup AppNetLogin
 *
 * @author David Raison
 *
 * OAuth wrangling via AppDotNetPHP by jdolitsky et al
 * https://github.com/jdolitsky/AppDotNetPHP
 *
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is a MediaWiki extension, and must be run from within MediaWiki.' );
}

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'AppNetLogin',
	'version' => '0.00',
	'author' => array( 'Chris Neale', 'Dave Challis', '[http://www.mediawiki.org/wiki/User:Clausekwis David Raison]' ), 
	'url' => '',
	'descriptionmsg' => 'appnetlogin-desc'
);

// Create [an] ADN group(s)
$wgGroupPermissions['adn'] = $wgGroupPermissions['*'];
$wgGroupPermissions['adnpaid'] = $wgGroupPermissions['user'];
$wgGroupPermissions['user'] = $wgGroupPermissions['*'];

$wgAutoloadClasses['SpecialAppNetLogin'] = dirname(__FILE__) . '/SpecialAppNetLogin.php';
$wgAutoloadClasses['EZAppDotNet'] = dirname(__FILE__) . '/EZAppDotNet.php';
$wgAutoloadClasses['AppNetSigninUI'] = dirname(__FILE__) . '/AppNetLogin.body.php'; // js-added top-right button

$wgExtensionMessagesFiles['AppNetLogin'] = dirname(__FILE__) .'/AppNetLogin.i18n.php';
$wgExtensionAliasFiles['AppNetLogin'] = dirname(__FILE__) .'/AppNetLogin.alias.php';

$wgSpecialPages['AppNetLogin'] = 'SpecialAppNetLogin';
$wgSpecialPageGroups['AppNetLogin'] = 'login';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'efSetupAppNetLoginSchema'; // beware table prefix(es)

$tsu = new AppNetSigninUI;
$wgHooks['BeforePageDisplay'][] = array( $tsu, 'efAddSigninButton' );

$stl = new SpecialAppNetLogin;
$wgHooks['UserLoadFromSession'][] = array($stl,'efAppNetAuth');
$wgHooks['UserLogoutComplete'][] = array($stl,'efAppNetLogout');

function efSetupAppNetLoginSchema( $updater ) {
	$updater->addExtensionUpdate( array( 'addTable', 'adn_user',
		dirname(__FILE__) . '/schema/adn_user.sql', true ) );
	return true;
}
