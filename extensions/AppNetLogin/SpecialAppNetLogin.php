<?php
/**
 * SpecialAppNetLogin.php
 * Botched together by Chris Neale, based on TwitterLogin
 * Written by David Raison, based on the guideline published by Dave Challis
 * at http://blogs.ecs.soton.ac.uk/webteam/2010/04/13/254/
 * @license: LGPL (GNU Lesser General Public License) http://www.gnu.org/licenses/lgpl.html
 *
 * @file SpecialAppNetLogin.php
 * @ingroup AppNetLogin
 *
 * @author Chris Neale
 *
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is a MediaWiki extension, and must be run from within MediaWiki.' );
}

class SpecialAppNetLogin extends SpecialPage {

	private $_consumerKey;
	private $_consumerSecret;
	private $_twUserTable = 'adn_user';

	public function __construct(){
		parent::__construct('AppNetLogin');
		global $wgConsumerKey, $wgConsumerSecret, $wgScriptPath;

		$this->_consumerKey = $wgConsumerKey;
		$this->_consumerSecret = $wgConsumerSecret;
	}

	// default method being called by a specialpage
	public function execute( $parameter ){
		$this->setHeaders();
		switch($parameter){
			case 'redirect':
				$this->_redirect();
			break;
			case 'callback':
				$this->_handleCallback();
			break;
			default:
				$this->_default();
			break;
		}

	}

	private function _default(){
		global $wgOut, $wgUser, $wgScriptPath, $wgExtensionAssetsPath;

		$wgOut->setPagetitle("AppNet Login");

		if ( !$wgUser->isLoggedIn() ) {
			$wgOut->addWikiMsg( 'appnetlogin-signup' );

			$adn_webflow = <<<ADN
<a href='{$this->getTitle( 'redirect' )->getFullURL()}' class='adn-button' data-type='authorize_v2' data-width="145" data-height="22" >Authorize with App.net</a>
<script>(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='//d2zh9g63fcvyrq.cloudfront.net/adn.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'adn-button-js'));</script>

ADN;
			$wgOut->addHTML($adn_webflow);
		} else {
			//$wgOut->addWikiText( wfMsg( 'appnetlogin-tietoaccount', $wgUser->getName() ) );
			$wgOut->addWikiMsg( 'appnetlogin-alreadyloggedin' );
		}
		return true;
	}

	protected function parseHeaders($response) {
		// take out the headers
		// return the body/content

		$response = explode("\n{",$response,2);
		$headers = $response[0];

		if (isset($response[1])) {
			$content = '{' . $response[1];
		}
		else {
			$content = null;
		}

	return $content;
	}


	private function _handleCallback(){
		global $wgScriptPath;
		session_start();

		// http://lab.cdn.cx/wiki/index.php?title=Special:AppNetLogin/callback&
		// error_description=resource+owner+denied+your+app+access&error=access_denied

		if (isset($_REQUEST['error'])) {
			header('Location: '.$wgScriptPath.'/index.php');
		}

		// set callback url
		$app_redirectUri = $this->getTitle( 'callback' )->getFullURL();

		$connection = new EZAppDotNet( $this->_consumerKey, $this->_consumerSecret );

		try {
			$connection->setSession(0, $app_redirectUri);
		} catch(AppDotNetException $x) {
			// 401 Unauthorized
			header('Location: '.$wgScriptPath.'/index.php');
		}

		$tokeninfo = json_decode($this->parseHeaders($connection->getLastResponse()), true);

//header('Content-type: text/plain'); print_r($tokeninfo); die;

		/**
		 * Save the access tokens. Normally these would be saved in a database for future use.
		 * Especially relevant if you'd want to read from or post to this user's timeline
		 */
//		$_SESSION['access_token'] = $access_token;

		$_u = $connection->getUser();

		$http = json_decode($this->parseHeaders($connection->getLastResponse()), true);

		/* If HTTP response is 200 continue otherwise send to connect page to retry */
		if ( $http['meta']['code'] == 200 ) {
			/* The user has been verified and the access tokens can be saved for future use */
			$_SESSION['status'] = 'verified';

			$_SESSION['adn_id']       = $_u['id'];
			$_SESSION['adn_username'] = $_u['username'];
			$_SESSION['adn_name']     = $_u['name'];
			$_SESSION['adn_paid']     = (key_exists('following', $tokeninfo['token']['limits'])) ? false : true;

			$returnto = ( $this->_isFirstLogin() ) ? 'Special:Preferences' : $_SESSION['returnto'];
			header('Location: ' . $wgScriptPath . '/index.php/' . $returnto );
		} else {
			/* Save HTTP status for error dialog on connnect page.*/
			header('Location: /wiki/?title=Special:AppNetLogin');
		}
	}

	private function _redirect(){
		global $wgRequest, $wgOut, $wgUser;

		// set callback url
		$app_redirectUri = $this->getTitle( 'callback' )->getFullURL();

		// Creating OAuth object
		$connection = new EZAppDotNet( $this->_consumerKey, $this->_consumerSecret );

		// set returnto url
		$_SESSION['returnto'] = ( $wgRequest->getText( 'returnto' ) ) ? $wgRequest->getText( 'returnto' ) : '';

		// tie to existing account
		/*
		if( $wgUser->isLoggedIn() ) {
			$_SESSION['wiki_username'] = $wgUser->getName();
			$_SESSION['wiki_token'] = $wgUser->getToken();
		}
		*/

		// not sure if this is the proper way to do it in mediawiki ?!

//		switch( $connection->http_code ){
//			case 200:
			$url = $connection->getAuthUrl( $app_redirectUri, array('basic') );
			header('Location: '. $url);
	/*		break;
			default:
			$wgOut->addWikiMsg( 'appnetlogin-couldnotconnect' );
			break;
		}*/
	}

	/**
	 * I'm not even sure it is possible to know this
	 **/
	private function _isFirstLogin() {
		return false;
	}

	/**
	 * First argument passed is a user object
	 * We return here after the callback has redirected us to $returnto with usually valid tokens in the session
	 */
	public function efAppNetAuth( $user ){
		if( session_id() == '' )
			session_start();

		// test if access token is set in our session
		if (empty($_SESSION['AppDotNetPHPAccessToken']) ) {
		        return false;
		}

		/* Unverified ADN credentials found, verify them */
		if (!isset($_SESSION['status']) || $_SESSION['status'] != 'verified') {

			// set callback url
			$app_redirectUri = $this->getTitle( 'callback' )->getFullURL();

			// Creating OAuth object
			$connection = new EZAppDotNet( $this->_consumerKey, $this->_consumerSecret );

			$connection->setAccessToken($_SESSION['AppDotNetPHPAccessToken']);

			// verify credentials and create a new user session from the adn username
			$v = $connection->getUser();
			$user = $this->_userExists( $v['name'], $v['username'], $v['id'] );
		} else {
			// ADN oauth status is verified
			$user = $this->_userExists( $_SESSION['adn_name'], $_SESSION['adn_username'], $_SESSION['adn_id'], $_SESSION['adn_paid'] );
		}
//		unset( $_SESSION['access_token'] );	// or we will not be able to log in as somebody else
		$user->setCookies();
		$user->saveSettings();
		return true;
	}

	private function _userExists( $name, $screen_name, $id, $adn_paid ) {
		$user = User::newFromName( $screen_name );

		/* let's see if this username already exists or whether it is tied to
		 * and already existing native account : case sensitive */
		if( $user->getId() == 0 )
			$this->_createUser( $user, $name, $screen_name, $id, $adn_paid );
		else $this->_isCreatedFromTwitter( $user );	// return false if not
		return $user;
	}

	/**
	 * Todo: if we are supposed to tie this account to an existing one, create it but don't use it
	 * --> cf _isCreatedFromTwitter --> relation
	 * Unfortunately there doesn't seem to be a way to disable or hide an account programmatically
	 */
	private function _createUser( $user, $name, $screen_name, $id, $adn_paid ){
		global $wgAuth;

		try {
			wfDebug( __METHOD__ . ':: created user ' . $screen_name . ' from AppNet' );
			$user->addToDatabase();
			$user->setRealName($name);

			if ( $wgAuth->allowPasswordChange() )
				$user->setPassword(User::randomPassword());

			if(defined('AppNetAccountFilter')) {
				if($adn_paid === true && AppNetAccountFilter == 'paid')
					$user->addGroup('adnpaid');
			} else {
				$user->addGroup('adnpaid');
			}

			$user->addGroup('adn');

//			$user->removeGroup('Users');

			//$user->confirmEmail();
			$user->setToken();

			// store adn details in our own table
			$this->_storeInTable( $user, $screen_name, $id );
			return true;

		} catch( Exception $e ) {
			print( $e->getTraceAsString() );
			return false;
		}
	}

	/* should we not use the external_user table since it has the exact same layout? */
	private function _storeInTable( $user, $screen_name, $id ){
		$dbw = wfGetDB(DB_MASTER);
		$dbw->insert( $this->_twUserTable,
			array('x_user_id' => $user->getId(), 'x_adn_id' => $id, 'x_adn_username' => $screen_name),
			__METHOD__,
			array()
		);
	}

	// user already exists... was it created from adn or did it alread exist before?
	private function _isCreatedFromTwitter( $user ){
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->select( $this->_twUserTable, 'x_adn_username',
			array( 'x_user_id' => $user->getId() ),
			__METHOD__
		);

		if ( $row = $dbr->fetchObject( $res ) ) {
			$dbr->freeResult( $res );
			$user->saveToCache();
		} else {
			$dbr->freeResult( $res );
			return false;
		}
	}

	public function efAppNetLogout(){
		if (session_id() == '') {
			session_start();
		}
		//setcookie(session_name(), session_id(), 1, '/');
		session_destroy();
		return true;
	}
}
