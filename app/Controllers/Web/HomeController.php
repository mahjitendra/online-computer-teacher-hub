<?php
class HomeController extends Controller {
    public function __construct(){
        // Future model loading can be done here
    }

    public function index(){
        $data = [
            'title' => 'Welcome to the Online Computer Teacher Hub',
            'description' => 'Your one-stop platform for computer science education.'
        ];

        $this->view('pages/frontend/home', $data);
    }
}
?>