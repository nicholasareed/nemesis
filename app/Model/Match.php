<?php

class Match extends AppModel {

	var $name = 'Match';

	var $belongsTo = array('Sport');

	var $hasAndBelongsToMany = array('User');


}
