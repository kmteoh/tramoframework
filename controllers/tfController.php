<?php

/**
 * @layout main
 * @template default
 */
class tfController extends controller {

    public $restService;
    public $tfService;

    public function __construct() {
        parent::__construct();
    }

    /**
     * @layout main
     * @template default
     */
    public function index() {
        $book = $city = $user = null;
        
        $data = $this->tfService->theFx();
        $ip = $this->restService->get('http://www.telize.com/ip');
        $user = user::get(1);
//        $user->books;
//        $user->save();
        $book = book::findByUserId($user->id);
//        $book->save();
        $city = city::findByPostcodeOrNameLike(3168,'Clayton');
//        $city->save();

        $category = category::get(1);

//        logger::error('This is debug message');

        return array(
            'category' => $category,
            'book' => $book,
            'user' => $user,
            'city' => $city,
            'data' => $data,
            'ip' => $ip,
            'customTemplate' => 'default'
        );
    }

    public function customException() {
        throw new CustomException("Calling this exception on purpose");
    }

    public function layoutException() {
        throw new LayoutException("Calling this exception which uses specific template defined in urlMappings.json, on purpose");
    }
}
