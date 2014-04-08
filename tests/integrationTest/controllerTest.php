<?php

class controllerTest {
    
    public function case1() {
        assert( '1 < 2');
    }
    
    public function case2() {
        assert('1!=1');
    }

    public function case3() {
        return false;
    }
}