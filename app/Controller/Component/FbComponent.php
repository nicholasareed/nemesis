<?
class FbComponent extends Object {
	
	var $controller;
	
	var $li = false; // logged in or not (should always be 1, unless redirecting to Auth)
	var $id = 0; // fb_uid
	var $uid = 0; // fb_uid
	var $data = array(); // DB info
	
	var	$friends = array();
	var	$fullfriends = array();
	var $myVotes = array();
	var $trustedBy = array();

	var $current_user = array();
	var $deny = false;

	var $facebook;
	
	
	function initialize(&$controller){

		App::import('Vendor','facebook',array('file' => 'Facebook/facebook.php'));
		$this->facebook = new Facebook(array(
		  'appId'  => FB_APP_ID,
		  'secret' => FB_APP_SECRET,
		  'cookie' => true, // enable optional cookie support
		));

		$this->controller =&$controller;

		// Check Logged In
		// - sets some components vars
		$this->auth();

		//now see if the calling controller wants auth
		// - different than normal DarkAuth 1.3 usage
		// 	 - per-action control (I like this way better)
		$vars = get_class_vars($this->controller->name.'Controller');
					
		if(empty($vars['_FbAccess'])) {
			$vars = get_class_vars('AppController');
			$vars['_FbAccess'] = array();
		}
		
		$action = $this->controller->action;   
			
		if(isset($this->controller->params['prefix'])){
			if(isset($vars['_FbAccess'][$action])){
				$vars['_FbAccess'][$action][] = $this->controller->params['prefix'];
			} else {
				$vars['_FbAccess'][$action] = array($this->controller->params['prefix']);
			}
		}
		
		// Get actions that require a login
		$access = $vars['_FbAccess'];
		$deny = false; 
		if(in_array($action,$access)){
			// Requires auth
			if(!$this->li){
				$deny = true;
			}
		}

		$this->deny = $deny;

		// Not logged in and denied?
		if(!$this->li && $this->deny){ 
			// base64_encode the url we are trying to go to

			echo "Need to be logged in here";
			exit;

			/*
			$redirect_url = base64_encode($_SERVER['REQUEST_URI']);
			$this->Session->write('redirect_url',$redirect_url);
			$this->controller->redirect('/users/login?redirect='.$redirect_url);
			*/
		}

	}

	function startup(){

		//finally give the view access to the data 
		$this->FbAuth = array(
			'li' => $this->li,
			'id' => $this->id
		); 

		// Set User if it exists
		if($this->li){
			//$this->DA['User'] = $this->current_user[$this->user_model_name];
		}
		$this->controller->set('_FbAuth',$this->FbAuth); 


		// Denied access?
		// - display 'denied' view
		if($this->deny){
			//echo $this->controller->render('/users/deny');
			echo "Not logged in (must be)";
			exit;
		}

	}


	function beforeRender(){

	}


	function shutdown(){

	}


	function beforeRedirect(){

	}
		

	function auth(){

		$this->controller->loadModel('User');

		$user_id = $this->controller->Session->read('FbAuth.service_auth_user');
		if($user_id){
			// Get User
			$conditions = array('User.id' => $user_id,
								'User.live' => 1);
			$this->controller->User->contain();
			$check = $this->controller->User->find('first',compact('conditions'));
			if(empty($check)){
				return false;
			}

			// Might be getting logged out quickly, depends on if the Cookie is set??
			$this->current_user = $check;
			$this->li = true;
			$this->id = $check['User']['id'];
			return true;
		}

		return false;
	}


	function destroyData(){
		$this->controller->Session->delete('FbAuth.service_auth_user'); 
	}
	

	function api($request){
		
		try {
			$data = $this->facebook->api($request);
			return $data;
		} catch (FacebookApiException $e) {
			error_log($e);
			return false;
		}
			
	}
	
	
	function getFriends(){
		// Check Cache for friend ids, otherwise, get all friends from the API
		
		// MOVE THIS TO A MODEL (all Fb calls, api and stuff should be in there, easier to access from anywhere)
		
		if(!$this->li){
			return array();
		}
		
		//Cache::set(array('duration' => '1 hour'));
		//$results = Cache::read('fb_friends_'.GLOBAL_CACHE.FRIENDS_CACHE.'_'.$this->uid);
		$results = false;
		if($results === false || !is_array($results)){
			
				$data = $this->api('/me/friends');
				
				if($data !== false){
					$ids = Set::extract($data['data'],'{n}.id');
				
					//Cache::set(array('duration' => '1 hour'));
					//Cache::write('fb_friends_'.GLOBAL_CACHE.FRIENDS_CACHE.'_'.$this->uid, $ids);
				
					return $ids;
				}
			} else {
				return $results;
			}
			
	}
	
	
	function getOrderedFriends($friends = array()){
		
		// $friends contains array of fb_uid
		
		//Cache::set(array('duration' => '1 hour'));
		//$results = Cache::read('fb_ordered_friends_'.GLOBAL_CACHE.ORDEREDFRIENDS_CACHE.'_'.$this->uid);
		$results = false;
		if($results === false || !is_array($results)){
			
			array_push($friends,$this->uid);
			$a = implode(',',$friends);
			
			$u = urlencode("SELECT uid, first_name, last_name, pic_square, name FROM user WHERE uid IN (".$a.")");
			$f = $this->restApi('fql.query',"format=json&query=".$u);
			
			$f = preg_replace('/:(\d+)/', ':"${1}"', $f);
			
			$temp = json_decode($f);
			
			$friends = array();
			foreach($temp as $friend){
				$cached = getFbUser($friend->uid);
				array_push($friends,array('uid' => $friend->uid,
																	'first_name' => $friend->first_name,
																	'last_name' => $friend->last_name,
																	'name' => $friend->name,
																	'pic_square' => $friend->pic_square,
																	'nickname' => $cached['nickname'],
																	'ordered' => $friend->last_name.', '.$friend->first_name));
			}
			
			$results = Set::sort($friends,'{n}.ordered','asc');
			
			//Cache::set(array('duration' => '1 hour'));
			//Cache::write('fb_ordered_friends_'.GLOBAL_CACHE.ORDEREDFRIENDS_CACHE.'_'.$this->uid, $results);
			
		}
		
		return $results;
			
	}
	
	
	function restApi($method,$params = array()){
		// Old Rest API (useful for dashboard.setCount and other [semi]depreciated methods)
		
		// example method: 'dashboard.setCount'
		
		if(!isset($this->HttpSocket)){
				App::import('Core','HttpSocket');
				$this->HttpSocket = new HttpSocket();
			}
			
			$results = $this->HttpSocket->get('https://api.facebook.com/method/'.$method, $params);
		
		return $results;
		
	}
	
	
	function dashboardSetCount($fb_uid){
		
		// Works fine, just for looks right now though
		
		//return;
		
			App::import('Core','HttpSocket');
			$HttpSocket = new HttpSocket();
			
			$token = $this->facebook->getAccessToken();
			
			$params = array('uid' => $this->uid,
											'count' => 0,
											'access_token' => $token);
			
			$results = $HttpSocket->get('https://api.facebook.com/method/dashboard.setCount', $params);
			
	}

}
?>