<?php

class Posts extends MY_Controller {

    protected $data = [
        "title" => "Discussions",
        "sub_title" => "List of discussions",
        "active_tab" => "discussion",
    ];

    public function __construct() {
        //parent constructor
        parent::__construct();

        //protected the area from not logged visitors
        $this->protectedArea();

        //load the libraries
        $this->load->library('form_validation');

        //load the models
        $this->load->model('PostQuery');
        $this->load->model('TagQuery');
    }

    public function Index($page = 1) {
        $this->setData("active", 1);

        return $this->postsList($page, 'active');
    }

    protected function postsList($page, $status = 'active') {

        define('rpp', 10);

        //getting category and search
        $category = $this->input->get("category", true);
        $author = $this->input->get("author", true);
        $search = $this->input->get("search", true);
        $tags = $this->input->get("tags", true);
        $sort = $this->input->get("sort", true);
        $direction = strtolower($this->input->get("direction", true));
        $qs = $_SERVER['QUERY_STRING'];

        //condition
        $condition = "status = '$status' and file_id is null";
        if ($category && $category != "uncategorized") {
            $condition.=" and id in (select post_id from post_category where category_id = '$category')";
        }if ($category && $category == "uncategorized") {
            $condition.=" and id in (select post_id from post_category where category_id is null)";
        } else if (!$category) {
            //all categories
        }
        if ($author && $author != "") {
            $condition.=($condition != "" ? " and" : "") . " user_id = $author";
        }
        if ($search) {
            $condition.=($condition != "" ? " and" : "") . " (title like '%$search%' or content like '%$search%')";
        }
        if ($tags) {
            $ptags = "'" . str_replace(",", "','", $tags) . "'";
            $condition.=($condition != "" ? " and" : "") . " id in (select post_id from post_tag where tag_id in (select id from tag where name in ($ptags)))";
        }

        //sort
        if ($direction != "asc") {
            $direction = "desc";
        }
        switch (strtolower($sort)) {
            case 'title':
                $orderBy = "title $direction" . ", `last_reply_time` desc, `time` desc";
                break;
            case 'author':
                $orderBy = "user_id $direction" . ", `last_reply_time` desc, `time` desc";
                break;
            case 'post-date':
                $orderBy = "`time` $direction" . ", `last_reply_time` desc";
                break;
            case 'last-activity':
                $orderBy = "`last_reply_time` $direction" . ", `time` desc";
                break;
            case 'last-active':
                $orderBy = "`last_reply_user_id` $direction" . ", `last_reply_time` desc, `time` desc";
                break;
            case 'replies':
                $orderBy = "`total_replies` $direction" . ", `last_reply_time` desc, `time` desc";
                break;
            default:
                $orderBy = "last_reply_time desc, `time` desc";
                $sort = "last-activity";
        }

        //posts
        $posts = PostQuery::getInstance()->allRoots($condition)->order($orderBy);

        //categories
        $categories = CategoryQuery::getInstance()->all(null, CategoryQuery::RETURN_AS_PRESENTER);

        //users
        $users = UserQuery::getInstance()->all(null, UserQuery::RETURN_AS_PRESENTER);

        //pager
        $pager = Paginator::getInstance($posts, (int) $page, rpp);

        //data
        $this->data['posts'] = $pager;
        $this->data['categories'] = $categories;
        $this->data['users'] = $users;
        $this->data['selected_category'] = $category;
        $this->data['selected_author'] = $author;
        $this->data['search'] = $search;
        $this->data['search_tags'] = $tags;
        $this->data['sort_by_column'] = $sort;
        $this->data['sort_by_direction'] = $direction;
        $this->data['qs'] = $qs;

        $this->view_loader->load("internal/posts/list", $this->data, 'internal');
    }

    protected function post_form(PostModel $post) {

        //prepare the data
        $categories = CategoryStates::getTree();
        $categories = array_key_exists('children', $categories) ? $categories['children'] : [];

        //add to data
        $this->setData('sub_title', $post->id ? 'Edit Discussion' : 'New Discussion');
        $this->setData('categories', $categories);
        $this->setData('post', $post);

        //display
        $this->view_loader->load("internal/posts/post_form", $this->data, 'internal');
    }

    public function create() {
        $post = PostModel::createEmpty($this->currentUser()->model());

        return $this->post_form($post);
    }

    public function edit($id) {
        $post = PostQuery::getInstance()->findById($id);

        if (!$post || $post->isForFile()) {
            $this->redirectWithOperationMessage("posts", "The specified post does not exist!", 1);
        }

        return $this->post_form($post);
    }

    public function submit() {
        try {

            //
            $url = $this->input->post("poster_url");

            $this->form_validation->set_rules('post_id', 'Post ID', 'xss_clean|is_natural');
            $this->form_validation->set_rules('title', 'post title', 'required|xss_clean');
            $this->form_validation->set_rules('content', 'post content', 'required');
            $this->form_validation->set_rules('category', 'post content', 'is_natural_no_zero|xss_clean');
            $this->form_validation->set_rules('tag', 'post content', 'xss_clean');

            $errors = "";
            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                return $this->post_submit_error($errors);
            }

            $title = $this->input->post('title');
            $content = $this->input->post('content');
            $postId = $this->input->post('post_id');
            $categoryId = $this->input->post('category');
            $tags = $this->input->post('tag');

            if ($categoryId) {
                $category = CategoryQuery::getInstance()->findById($categoryId);
            } else {
                $category = null;
            }

            $action = $this->input->post('action');
            if ($action !== PostModel::STATUS_ACTIVE) {
                $action = PostModel::STATUS_DRAFT;
            }

            if (!$postId) {
                $post = PostQuery::getInstance()->InsertNew($title, $content, $this->currentUser()->model());
                $post->category($category, true);
                $post->setTags($tags);
                $postId = $post->col('id');
            } else {
                $post = PostQuery::getInstance()->findById($postId);
                if (!$post || $post->isForFile()) {
                    throw new Exception("Not a post", 1);
                }
                $post->update($title, $content, $category, $tags, $this->currentUser()->model());
            }


            if ($action != $post->col('status')) {
                $post->setStatus($action);
            }

            $target = $post->isDraft() ? "drafts" : "post/{$post->getRootPost()->id}";

            return $this->redirectWithOperationMessage("posts/$target", "The post has been successfully published!");
        } catch (Exception $e) {
            return $this->redirectWithOperationMessage($url, $e->getMessage(), $e->getCode());
        }
    }

    public function post($id) {

        $post = PostQuery::getInstance()->findById($id);
        if (!$post || $post->isForFile() || $post->isDraft()) {
            $this->redirectWithOperationMessage("posts", "The specified post does not exist!", 1);
        }
        $replies = $post->getReplies("status='active'")->order("time asc");

        $this->setData("post", $post);
        $this->setData("sub_title", $post->title);
        $this->setData("replies", $replies);

        return $this->view_loader->load("internal/posts/view", $this->data, 'internal');
    }

    protected function post_submit_error($errors) {
        var_dump($errors);
    }

    public function reply() {

        try {

            //
            $url = $this->input->post("poster_url");
            if (!$url) {
                $url = base_url();
            }

            $this->form_validation->set_rules('post_id', 'Post ID', 'required|xss_clean|is_natural');
            $this->form_validation->set_rules('content', 'Reply content', 'required');
            $this->form_validation->set_rules('title', 'Reply title', 'required|xss_clean');

            $errors = "";
            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                return $this->post_submit_error($errors);
            }

            $postId = $this->input->post('post_id');
            $content = $this->input->post('content');
            $title = $this->input->post('title');

            $post = PostQuery::getInstance()->findById($postId);
            if (!$post) {
                throw new Exception("The provided post is invalied!", 1);
            }

            $replyPost = $post->addReply($content, $title, $this->currentUser()->model());

            return $this->redirectWithOperationMessage("posts/post/{$post->getRootPost()->id}", "The reply has been successfully sent!");
        } catch (Exception $e) {
            return $this->redirectWithOperationMessage($url, $e->getMessage(), $e->getCode());
        }
    }

    public function drafts($page = 1) {
        $this->setData("sub_title", "Your Drafts");
        $this->setData("active", 0);

        return $this->postsList($page, 'draft');
    }

    public function delete() {

        $this->protectedArea();

        $id = $this->input->post("id", true);
        $post = PostQuery::getInstance()->findById($id);
        if (!$post) {
            return $this->redirectWithOperationMessage("posts", "Not a discussion", 1);
        }

        if (!$this->currentUser()->canEditPost($post)) {
            return $this->redirectWithOperationMessage("posts/view/$id", "Insufficient privileges to delete this discussion.", 1);
        }

        $post->delete();

        if ($post->isRootPost()) {
            return $this->redirectWithOperationMessage("posts", "The post has been deleted successfully!");
        } else {
            return $this->redirectWithOperationMessage("posts/post/{$post->getRootPost()->id}", "The reply has been deleted successfully!");
        }
    }

    public function close() {

        $id = $this->input->post("id", true);
        $post = PostQuery::getInstance()->findById($id);
        if (!$post) {
            return $this->redirectWithOperationMessage("posts", "Not a discussion", 1);
        }

        if (!$this->currentUser()->canClosePost($post)) {
            return $this->redirectWithOperationMessage("posts/view/$id", "Insufficient privileges to close this discussion.", 1);
        }

        $post->close();

        return $this->redirectWithOperationMessage("posts/post/{$post->getRootPost()->id}", "The discussion has been closed successfully!");
    }

    public function closed($page = 1) {

        $this->setData("sub_title", "Closed Discussions");
        $this->setData("active", 0);

        return $this->postsList($page, 'closed');
    }

}
