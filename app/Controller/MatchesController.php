<?php


App::uses('AppController', 'Controller');


class MatchesController extends AppController {

	public $name = 'Matches';

	public $uses = array();

	var $components = array('Wizard');

	var $_FbAccess = array('index','create');


	function beforeFilter(){
		if($this->action == 'create'){
			$this->Wizard->wizardAction = 'create';
			$this->Wizard->nestedViews = true;
			$this->Wizard->steps = array('against','sport','result');
		}
	}


	function test(){


		$this->loadModel('Match');

		$match_id = 1;
		$result_status = 'win';
		$this->Match->habtmNick('User',$match_id,$this->Fb->id,array('status' => $result_status, 'agreed' => 1));

	}


	function index() {
		// Homepage

		

	}


	function view($match_id = null){

		$match_id = intval($match_id);

		$this->loadModel('Match');

		$conditions = array('Match.id' => $match_id);
		$this->Match->contain(array('User','Sport'));

		$match = $this->Match->find('first',compact('conditions'));

		if(empty($match)){
			$this->redirect('/?unable-to-find-match'); 
		}

		//pr($match);exit;
		$this->set(compact('match'));

	}


	function history($p1 = null, $p2 = null, $sport = null){
		// Get all the matches between two players
		// - not breaking down by Sport yet

		$p1 = intval($p1);
		$p2 = intval($p2);

		$this->loadModel('User');
		$this->loadModel('Match');

		// Get users
		$conditions = array('User.id' => $p1);
		$this->User->contain('Match');
		$u1 = $this->User->find('first',compact('conditions'));
		$conditions = array('User.id' => $p2);
		$this->User->contain('Match');
		$u2 = $this->User->find('first',compact('conditions'));

		if(empty($u1) || empty($u2)){
			$this->redirect('/?could not find user');
		}

		// Get all p1 matches
		$u1_matches = Set::extract($u1['Match'],'{n}.id');

		// Get all p2 matches
		$u2_matches = Set::extract($u2['Match'],'{n}.id');

		// Compare the two
		$matches = array_intersect($u1_matches, $u2_matches);

		// Get those Matches
		$conditions = array('Match.id' => $matches);
		$this->Match->contain(array('User','Sport'));
		$matches = $this->Match->find('all',compact('conditions'));

		pr($matches);
		exit;

		$this->set(compact('matches'));

	}


	function user($user_id = null){
		// Show all matches for a person

		$user_id = $user_id === null ? $user_id = $this->Fb->id : intval($user_id);

		$this->loadModel('User');

		$conditions = array('User.id' => $user_id);
		$this->User->contain(array('Match' => array('Sport')));
		$user = $this->User->find('first',compact('conditions'));

		if(empty($user)){
			$this->redirect('/?cannot find user'); 
		}

		// pr($user);
		// exit;

		// Match history (w-l-t)
		$record = array('win' => 0,
						'lose' => 0,
						'tie' => 0
						);

		foreach($user['Match'] as $match){
			if(!array_key_exists($match['MatchesUser']['status'],$record)){
				continue;
			}

			$record[$match['MatchesUser']['status']] += 1;
		}

		$this->set(compact('user','record'));
		
	}


	function create($step = null){
		// Create a new match
		// - just finished playing a game, recording it here

		$this->Wizard->process($step);


	}


	function _prepareAgainst(){
		// List all my friends

		$friends = $this->Fb->getFriends();
		$this->set(compact('friends'));

	}


	function _processAgainst(){

		return true;
	}


	function _prepareSport(){

		$this->loadModel('Sport');
		$sports = $this->Sport->find('list');
		$this->set(compact('sports'));

	}


	function _processSport(){

		return true;
	}


	function _prepareResult(){

	}


	function _processResult(){

		return true;
	}


	function _afterComplete() {
		// Done, process the wizard data

		// Get data
		$w = $this->Wizard->read();

		// Does that User exist already?
		// - should we be doing this in the processAgainst portion?
		$this->loadModel('User');

		$friend_uid = $w['against']['Match']['friend'];

		$conditions = array('User.username' => $friend_uid);
		$this->User->contain();
		$friend = $this->User->find('first',compact('conditions'));

		if(empty($friend)){
			// Need to create them
			$uData = array();
			$uData['username'] = $friend_uid;

			$this->User->create();
			if(!$this->User->save($uData)){
				// Fuck
				echo "Failed saving new Friend";
				exit;
			}

			$id = $this->User->id;

			// get the friend again
			$this->User->contain();
			$conditions = array('User.id' => $id);
			$friend = $this->User->find('first',compact('conditions'));
		}

		// Got the Friend!
		if(empty($friend)){
			// FUCCCCCCKCKKKKK
			echo "Failed again getting new friend. ultimate fail";
			exit;
		}

		// Get the sport
		
		$sport_id = $w['sport']['Match']['sport'];
		
		// Get the result
		$result_status = $w['result']['Match']['result'];

		// Save the fucker!
		$mData = array();
		$mData['sport_id'] = $sport_id;
		$mData['status'] = $result_status == 'tie' ? 'tie' : 'won';

		// Save match
		$this->loadModel('Match');
		$this->Match->create();
		if(!$this->Match->save($mData)){
			echo "Failed saving match";
			exit;
		}

		$match_id = $this->Match->id;

		// Save habtm data

		// Me
		$this->Match->habtmNick('User',$match_id,$this->Fb->id,array('status' => $result_status, 'agreed' => 1));

		// Friend
		$friend_status = 'tie';
		if($result_status == 'win'){
			$friend_status = 'lose';
		} elseif ($result_status == 'lose'){
			$friend_status = 'win';
		}
		$this->Match->habtmNick('User',$match_id,$friend['User']['id'],array('status' => $friend_status, 'agreed' => 0));

		// Done, redirect
		$this->Wizard->reset();
		$this->redirect('/matches/view/'.$match_id);

	}


}
