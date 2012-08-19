<?php

class UsersController extends AppController {

	public $name = 'Users';

	public $uses = array();

	var $components = array('Fb');

	var $_FbAccess = array();


	function index() {
		// Homepage

		// - just a link to the Profile, should just redirect there?

	}


	function profile($user_id = null){
		// Viewing an individual

		$user_id = $user_id === null ? $user_id = $this->Fb->id : intval($user_id);

		$this->loadModel('User');

		$conditions = array('User.id' => $user_id,
							'User.live' => 1);
		$this->User->contain();
		$user = $this->User->find('first',compact('conditions'));

		if(empty($user)){
			$this->redirect('/?unable to locate user');
		}

		// me?
		$me = false;
		if($user['User']['id'] == $this->Fb->id){
			$me = true;
		}

		pr('someone else');
		exit;

		// Get details about the person (profile, image, etc.)

		// Last few games

		// Nemesi (my Nemesises)

		$this->set(compact('user','me'));

	}

	
	
	//  Facebook authentication
	function fb_auth(){

		App::import('Vendor','facebook',array('file' => 'Facebook/facebook.php'));
		$this->facebook = new Facebook(array(
		  'appId'  => FB_APP_ID,
		  'secret' => FB_APP_SECRET,
		  'cookie' => true, // enable optional cookie support
		));
		
		//Facebook Authentication part
		$session = $this->facebook->getSession();
		$loginUrl = $this->facebook->getLoginUrl(
				array(
					//'canvas'    => 0,
					//'fbconnect' => 0,
					'req_perms' => FB_APP_PERMISSIONS
				)
		);
		
		$data = array('li' => 0,
						'uid' => 0,
						'friends' => array(),
						'fullfriends' => array(),
						'data' => array());

		if(!$session){
			echo "<script type='text/javascript'>top.location.href = '$loginUrl';</script>";
			exit;
		} else {
			
			// Save the Service
			// - Facebook is same as Twitter
			
			/*
			
			$this->Service =& ClassRegistry::init('Service');
					
			$fb_uid = $this->facebook->getUser();
			$token = $this->facebook->getAccessToken();
			$me = $this->Fb->api('/me');
			
			$conditions = array(//'Service.user_id' => $this->sAuth['id'],
													'Service.type' => 'facebook',
													'Service.service_id' => $fb_uid);
			
			$service = $this->Service->find('first',compact('conditions'));
			
			if(!empty($service)){
				// Modify the existing for this user
				
				$data = $service['Service'];
				$data['user_id'] = $this->sAuth['id'];
				$data['service_id'] = $fb_uid;
				$data['service_name'] = $me['name'];
				$data['oauth_token'] = $token;
				$data['data'] = serialize($me);
				
				$this->Service->create();
				if(!$this->Service->save($data)){
					$this->redirect('/?failed to save Facebook auth');
				}
				
			} else {
				
				$data = array('user_id' => $this->sAuth['id'],
											'service_id' => $fb_uid,
											'service_name' => $me['name'],
											'type' => 'facebook',
											'oauth_token' => $token,
											//'oauth_token_secret' => $token_credentials['oauth_token_secret'],
											'data' => serialize($me));
				
				$this->Service->create();
				if(!$this->Service->save($data)){
					$this->redirect('/?failed to save Facebook auth');
				}
			}
			*/
				
			$this->loadModel('Service');
					
			$fb_uid = $this->facebook->getUser();
			$token = $this->facebook->getAccessToken();
			$me = $this->Fb->api('/me');
						
			$conditions = array(//'Service.user_id' => $this->sAuth['id'],
													'Service.type' => 'facebook',
													'Service.service_id' => $fb_uid);
			
			$service = $this->Service->find('first',compact('conditions'));
			
			if(!empty($service)){
				// Service already exists (has been created for a user)
				
				// Modify the existing for this user
				
				// Are we logged in already?
				// - if yes, link with existing User
				// - if no, log in as the user who already had the $user_id set
				
				$data = $service['Service'];
				
				$user_id = 0;
				if($this->Fb->li){
					$user_id = $this->Fb->id;
					$data['user_id'] = $user_id;
				} else {
					$user_id = $data['user_id'];
				}
				
				$data['service_id'] = $fb_uid;
				$data['service_name'] = $me['name'];
				$data['oauth_token'] = $token;
				$data['oauth_token_secret'] = $session['secret'];
				$data['additional'] = $session['sig'];  // sig
				$data['additional2'] = $session['session_key']; // session_key
				$data['data'] = serialize($me);			// basic info
				
				$this->Service->create();
				if(!$this->Service->save($data)){
					$this->redirect('/?failed to save Facebook auth');
				}
				
			} else {
				// Service not used before
				
				// Logged in?
				// - if yes, update the existing user
				// - if no, then no user created for this service, so create a new user
				
				$data = array('service_id' => $fb_uid,
								'service_name' => $me['name'],
								'type' => 'facebook',
								'oauth_token' => $token,
								'oauth_token_secret' => $session['secret'],
								'additional' => $session['sig'],
								'additional2' => $session['session_key'],
								'data' => serialize($me));
											
				$user_id = 0;
				
				if($this->Fb->li){
					$user_id = $this->Fb->id;
					$data['user_id'] = $user_id;
				} else {
					// Create a new User
					$uData = array();
					$uData['username'] = $fb_uid;

					$this->loadModel('User');

					$this->User->create();
					if(!$this->User->save($uData)){
						$this->redirect('/?failed to save Facebook auth');
					}
					$user_id = $this->User->id;
					$data['user_id'] = $user_id;
				}
				
				$this->Service->create();
				if(!$this->Service->save($data)){
					$this->redirect('/?failed to save Facebook auth');
				}
			}
			
		
			// Save logged in Session User
			$this->Session->write('FbAuth.service_auth_user',$user_id);
			
			$this->redirect('/');
					
		}

	}


	function logout(){
		$this->Fb->destroyData();
		$this->redirect('/');
	}


}
