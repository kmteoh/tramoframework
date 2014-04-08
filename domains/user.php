<?php

class user extends domain {
    public $id;
    public $firstName;
    public $lastName;
    public $status;

    public $dateCreated;
    public $dateUpdated;
    
    public $belongsTo = array('city');
    public $hasMany = array(array('books'=>'book'));
}