<?php

class category extends domain {

    public $id;
    public $name;

    public $belongsTo = array(array('parent'=>'category'));
    public $hasMany = array(array('categories'=>'category'));
}