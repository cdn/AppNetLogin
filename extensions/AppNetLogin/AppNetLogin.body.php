<?php
/**
 * AppNetLogin.body.php
 * Botched by Chris Neale
 * Written by David Raison, based on the guideline published by Dave Challis at http://blogs.ecs.soton.ac.uk/webteam/2010/04/13/254/
 * @license: LGPL (GNU Lesser General Public License) http://www.gnu.org/licenses/lgpl.html
 *
 * @file AppNetLogin.body.php
 * @ingroup AppNetLogin
 *
 * @author Chris Neale
 *
 */

class AppNetSigninUI {
	/**
	 * Add a sign in with AppNet button but only when a user is not logged in
	 */
	public function efAddSigninButton( &$out, &$skin ) {
		global $wgUser, $wgExtensionAssetsPath, $wgScriptPath;

		if ( !$wgUser->isLoggedIn() ) {
			$link = SpecialPage::getTitleFor( 'AppNetLogin', 'redirect' )->getLinkUrl(); 
			$out->addInlineScript('$j(document).ready(function(){
				$j("#pt-anonlogin, #pt-login").after(\'<li id="pt-appnetsignin">'
				.'<a class="adn-button" data-type="authorize_v2" data-width="145" data-height="22" href="' . $link  . '">'
				.'Authorize with App.net'.'</a></li>\');
				(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=\'//d2zh9g63fcvyrq.cloudfront.net/adn.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'adn-button-js\'));
			})');
		}
		return true;
	}
}
