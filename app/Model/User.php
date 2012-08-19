<?php

class User extends AppModel {

	var $name = 'User';


	var $hasAndBelongsToMany = array('Match');

}
