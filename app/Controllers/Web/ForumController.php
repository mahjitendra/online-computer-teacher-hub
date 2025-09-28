<?php
class ForumController extends Controller {
    public function __construct(){
        $this->forumModel = $this->model('Forum');
        $this->postModel = $this->model('ForumPost');
    }

    public function index(){
        $forums = $this->forumModel->getAllForums();

        $data = [
            'forums' => $forums
        ];

        $this->view('pages/frontend/forum', $data);
    }

    public function show($id){
        $forum = $this->forumModel->getForumById($id);
        $posts = $this->postModel->getPostsByForum($id);

        $data = [
            'forum' => $forum,
            'posts' => $posts
        ];

        $this->view('pages/frontend/forum-posts', $data);
    }

    public function createPost($forum_id){
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            return;
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'forum_id' => $forum_id,
                'user_id' => $_SESSION['user_id'],
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content'])
            ];

            if($this->postModel->createPost($data)){
                header('location: ' . URLROOT . '/forum/show/' . $forum_id);
            } else {
                die('Something went wrong');
            }
        } else {
            $forum = $this->forumModel->getForumById($forum_id);
            
            $data = [
                'forum' => $forum
            ];

            $this->view('pages/frontend/create-post', $data);
        }
    }

    public function post($id){
        $post = $this->postModel->getPostById($id);
        $replies = $this->postModel->getRepliesByPost($id);

        $data = [
            'post' => $post,
            'replies' => $replies
        ];

        $this->view('pages/frontend/forum-post-detail', $data);
    }

    public function reply($post_id){
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            return;
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'post_id' => $post_id,
                'user_id' => $_SESSION['user_id'],
                'content' => trim($_POST['content'])
            ];

            if($this->postModel->createReply($data)){
                header('location: ' . URLROOT . '/forum/post/' . $post_id);
            } else {
                die('Something went wrong');
            }
        }
    }
}
?>