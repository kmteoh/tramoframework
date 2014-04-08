<?php

class htmlTaglib {
    public $namespace = 'html';

    public function menuItem($attrs=array(),$body=null) {
        return '<li'.($attrs['active']=='true'?' class="active"':'').'><a href="'.$attrs['href'].'">'.$attrs['label'].'</a></li>';
    }

}

