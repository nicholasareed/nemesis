<?php  
class NickAuthComponent extends Object { 

	var $components = array('Session');


	var $controller;
	var $User;


	function initialize(&$controller){
		$this->controller =&$controller;

		$this->loadModel('User');
		
	}


	function startup(){

	}


	function beforeRender(){

	}


	function shutdown(){

	}
		

	function auth(){
		$user_id = $this->Session->read('DarkAuth.service_auth_user');
		if($user_id){
			// Get User
			$conditions = array('User.id' => $user_id,
								'User.live' => 1);
			$this->controller->{$this->user_model_name}->contain();
			$check = $this->controller->{$this->user_model_name}->find($conditions);
			if(empty($check)){
				return false;
			}

			// Might be getting logged out quickly, depends on if the Cookie is set??
			$this->current_user = $check;
			$this->li = true;
			$this->id = $check[$this->user_model_name]['id'];
			return true;
		}

		return false;
	}

	
} 
?> 