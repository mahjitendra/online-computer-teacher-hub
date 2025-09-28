<?php
class DefaultApiController extends ApiController {
    public function index(){
        $this->jsonResponse(['message' => 'Welcome to the Online Computer Teacher Hub API']);
    }
}
?>