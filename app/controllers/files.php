<?php

class Files extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->protectedArea();

        $this->load->model("FileQuery");
        $this->load->model("TagQuery");

        $this->setData("title", "Files");
        $this->setData("sub_title", "Files List");
        $this->setData("active_tab", "files");
    }

    public function index($page = 1) {

        //getting category and search
        $category = $this->input->get("category", true);
        $author = $this->input->get("author", true);
        $search = $this->input->get("search", true);
        $sort = $this->input->get("sort", true);
        $direction = strtolower($this->input->get("direction", true));
        $qs = $_SERVER['QUERY_STRING'];

        //condition
        $condition = "";
        if ($category && $category != "uncategorized") {
            $condition.="id in (select file_id from file_category where category_id = '$category')";
        }if ($category && $category == "uncategorized") {
            $condition.="id in (select file_id from file_category where category_id is null)";
        } else if (!$category) {
            //all categories
        }
        if ($author && $author != "") {
            $condition.=($condition != "" ? " and" : "") . " user_id = $author";
        }
        if ($search) {
            $condition.=($condition != "" ? " and" : "") . " (title like '%$search%')";
        }

        //sort
        if ($direction != "asc") {
            $direction = "desc";
        }
        switch (strtolower($sort)) {
            case 'title':
                $orderBy = "title $direction" . ", `update_time` desc, `time` desc";
                break;
            case 'author':
                $orderBy = "user_id $direction" . ", `update_time` desc, `time` desc";
                break;
            case 'time':
                $orderBy = "`time` $direction" . ", `update_time` desc";
                break;
            case 'updated':
                $orderBy = "`update_time` $direction" . ", `time` desc";
                break;
//            case 'last-active':
//                $orderBy = "`last_reply_user_id` $direction" . ", `last_reply_time` desc, `time` desc";
//                break;
            case 'version':
                $orderBy = "`version` $direction" . ", `update_time` desc, `time` desc";
                break;
            default:
                $orderBy = "update_time desc, `time` desc";
                $sort = "updated";
        }

        //prepare the files
        $files = FileQuery::getInstance()->allActive($condition, FileQuery::RETURN_AS_PRESENTER);
        $paginator = ResultSetPaginator::getInstance($files, $page);
        $files->order($orderBy);

        //category
        $categories = CategoryQuery::getInstance()->all(null, CategoryQuery::RETURN_AS_MODEL);

        //users
        $users = UserQuery::getInstance()->all(null, UserQuery::RETURN_AS_PRESENTER);

        //add the data
        $this->setData("files", $paginator);
        $this->data['categories'] = $categories->presenterSet();
        $this->data['users'] = $users;
        $this->data['selected_category'] = $category;
        $this->data['selected_author'] = $author;
        $this->data['search'] = $search;
        $this->data['sort_by_column'] = $sort;
        $this->data['sort_by_direction'] = $direction;
        $this->data['qs'] = $qs;

        //view the view
        return $this->view_loader->load("internal/files/list", $this->data, 'internal');
    }

    public function newFile() {

        //setting the data
        $this->setData("sub_title", "Upload New File");
        $this->setData("action", "new_file");
        $this->setData("target_action", base_url("files/uploadNewFile"));

        $this->setData("return_path", base_url("files"));

        return $this->displayFileForm();
    }

    public function edit($id) {
        $file = FileQuery::getInstance()->findById($id);
        if (!$file) {
            return $this->redirectWithOperationMessage("files", "Not a file", 1);
        }

        $this->setData("sub_title", "Edit file");
        $this->setData("action", "edit_file");
        $this->setData("target_action", base_url("files/updateFile"));
        $this->setData("file", $file);
        $this->setData("return_path", base_url("files/view/$id"));


        return $this->displayFileForm();
    }

    protected function displayFileForm() {

        //prepare the data
        $categories = CategoryQuery::getInstance()->all(null, CategoryQuery::RETURN_AS_PRESENTER);

        //setting the data
        $this->setData('categories', $categories);

        //view the page
        return $this->view_loader->load("internal/files/form", $this->data, 'internal');
    }

    public function uploadNewFile() {
//        $this->output->enable_profiler(TRUE);

        try {

            $this->load->library('form_validation');

            $this->form_validation->set_rules("title", "File title", "required|xss_clean");
            $this->form_validation->set_rules("category", "File category", "xss_clean|is_natural_no_zero");
            $this->form_validation->set_rules("tags", "File tags", "xss_clean");
            $this->form_validation->set_rules("version", "File version", "xss_clean|numeric");

            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                throw new Exception($errors, 1);
            }

            $title = $this->input->post("title");
            $description = $this->input->post("description");
            $category = $this->input->post("category");
            $tags = $this->input->post("tags");
            $version = $this->input->post("version");

            //preparing category
            if ($category) {
                $categoryModel = CategoryQuery::getInstance()->findById($category);
            } else {
                $categoryModel = null;
            }


            //configuring the upload
            $config['upload_path'] = __DIR__ . DIRECTORY_SEPARATOR . '../../assets/uploads/';
            $config['allowed_types'] = 'gif|jpg|png|pdf|doc|docx|txt|tif|tiff';
            $config['max_size'] = '2048';
            $config['max_width'] = '0';
            $config['max_height'] = '0';
            $config['encrypt_name'] = true;

            //Saving the uploaded file
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('file')) {
                $errors = $this->upload->display_errors('', '');
                throw new Exception($errors, 1);
            }

            //getting saving uploaded file result
            $uploadData = $this->upload->data();

            //creating the file
            $fileModel = FileQuery::getInstance()->insertNew($title, $description, $this->currentUser()->model());

            //adding the tags and the category
            $fileModel->post()->category($categoryModel, true);
            $fileModel->post()->addTags($tags);

            //adding file version
            $fileVersionModel = $fileModel->insertVersion($uploadData['file_name'], $uploadData['orig_name'], $uploadData['file_type'], $this->currentUser()->model(), $version, $uploadData['file_size']);

            $this->redirectWithOperationMessage("files/view/{$fileModel->id}", "The file has been successfully uploaded!");
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    public function view($id) {

        $fileModel = FileQuery::getInstance()->findById($id);
        if (!$fileModel) {
            return $this->redirect("files");
        }

        if ($fileModel->isRemoved()) {
            return $this->redirectWithOperationMessage("files", "This file has been deleted!", 1);
        }

        $category = $fileModel->post()->category();
        $tags = $fileModel->post()->presenter()->tagsCsv();

        $this->setData("file", $fileModel->presenter());
        $this->setData("sub_title", __("Files List"));
        $this->setData("category", $category ? $category->presenter() : __("Uncategorized"));
        $this->setData("tags", $tags? : __("No tags"));
        $this->setData("versions", $fileModel->versions("status='active'")->presenterSet());

        return $this->view_loader->load("internal/files/view", $this->data, 'internal');
    }

    public function download($id, $versionId = null) {

        //prepare fileModel
        $fileModel = FileQuery::getInstance()->findById($id);
        if (!$fileModel) {
            return $this->redirect("files");
        }



        //getting Last version
        if ($versionId === null) {
            $version = $fileModel->getLastVersion();
        } else {
            $version = $fileModel->getVersion($versionId);
        }
        if (!$version) {
            return $this->redirect("files");
        }

        //preparing data
        $fileName = $version->getDownloadName();
        $filePath = $this->getFileFullPath($version->file_name);

        $this->output->set_header("Content-type: {$version->file_type}; name={$fileName}");
        header("Content-Disposition: attachment; filename=$fileName");
        header("Content-Length: " . filesize($filePath));

        readfile($filePath);
    }

    public function newVersion($id) {

        //preparing the file
        $fileModel = FileQuery::getInstance()->findById($id);
        if (!$fileModel) {
            return $this->redirectWithOperationMessage("files", "Not a file!", 1);
        }

        //setting the data
        $this->setData("sub_title", "Upload New Version");
        $this->setData("file", $fileModel);
        $this->setData("action", "new_version");
        $this->setData("target_action", base_url("files/uploadNewVersion"));
        $this->setData("return_path", base_url("files/view/$id"));

        return $this->displayFileForm();
    }

    public function uploadNewVersion() {
//        $this->output->enable_profiler(TRUE);

        try {

            $this->load->library('form_validation');

            $this->form_validation->set_rules("id", "File id", "required|numeric|xss_clean");
            $this->form_validation->set_rules("version", "File version", "xss_clean|numeric");

            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                throw new Exception($errors, 1);
            }

            $id = $this->input->post("id");
            $title = "New version";
            $description = $this->input->post("description");
            $version = $this->input->post("version");

            //creating the file
            $fileModel = FileQuery::getInstance()->findById($id);
            if (!$fileModel) {
                return $this->redirectWithOperationMessage("files", "Not a file", 1);
            }

            if ($fileModel->version >= $version) {
                throw new Exception("Version should be larger than the current version {$fileModel->version}", 1);
            }

            //configuring the upload
            $config['upload_path'] = __DIR__ . DIRECTORY_SEPARATOR . '../../assets/uploads/';
            $config['allowed_types'] = 'gif|jpg|png|pdf|doc|docx|txt|tif|tiff';
            $config['max_size'] = '2048';
            $config['max_width'] = '0';
            $config['max_height'] = '0';
            $config['encrypt_name'] = true;

            //Saving the uploaded file
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('file')) {
                $errors = $this->upload->display_errors('', '');
                throw new Exception($errors, 1);
            }

            //getting saving uploaded file result
            $uploadData = $this->upload->data();

            //adding file version
            $fileVersionModel = $fileModel->insertVersion($uploadData['file_name'], $uploadData['orig_name'], $uploadData['file_type'], $this->currentUser()->model(), $version, $uploadData['file_size'], $title, $description);
            if (!$fileVersionModel || is_string($fileVersionModel)) {
                throw new Exception("Error in adding version. $fileVersionModel", 1);
            }

            return $this->redirectWithOperationMessage("files/view/{$fileModel->id}", "The version has been successfully uploaded!");

//            array (size=14)
//            'file_name' => string 'f636d23fe412acb1db3d7c1567b3d1d8.pdf' (length = 36)
//            'file_type' => string 'application/pdf' (length = 15)
//            'file_path' => string 'D:/www/t4f/assets/uploads/' (length = 26)
//            'full_path' => string 'D:/www/t4f/assets/uploads/f636d23fe412acb1db3d7c1567b3d1d8.pdf' (length = 62)
//            'raw_name' => string 'f636d23fe412acb1db3d7c1567b3d1d8' (length = 32)
//            'orig_name' => string '_.pdf' (length = 5)
//            'client_name' => string '?????? ????.pdf' (length = 15)
//            'file_ext' => string '.pdf' (length = 4)
//            'file_size' => float 124
//            'is_image' => boolean false
//            'image_width' => string '' (length = 0)
//            'image_height' => string '' (length = 0)
//            'image_type' => string '' (length = 0)
//            'image_size_str' => string '' (length = 0)
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    public function removeFile($id) {
        $file = FileQuery::getInstance()->findById($id);
        if (!$file) {
            return $this->redirectWithOperationMessage("files", "Not a file", 1);
        }
        $file->remove();
        return $this->redirectWithOperationMessage("files", "The file has been deleted!", 0);
    }

    public function removeVersion($id) {
//        $this->output->enable_profiler(TRUE);

        try {
            $file = FileQuery::getInstance()->findById($id);
            if (!$file) {
                throw new Exception("Not a file", 1);
            }


            $this->load->library('form_validation');

            $this->form_validation->set_rules("versionId", "File version id", "required|is_natural_no_zero|xss_clean");

            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                throw new Exception($errors, 1);
            }

            $versionId = $this->input->post('versionId', true);
            $result = $file->removeVersion($versionId);

            if ($file->status == "deleted") {
                return $this->redirectWithOperationMessage("files", "The version and the file have been deleted successfully!", 0);
            } else {
                return $this->redirectWithOperationMessage("files/view/$id", "The version has been deleted successfully!", 0);
            }
        } catch (Exception $e) {
            return $this->redirectWithOperationMessage("files", $e->getMessage(), $e->getCode());
        }
    }

    public function addComment($id) {
//        $this->output->enable_profiler(TRUE);

        try {

            $fileModel = FileQuery::getInstance()->findById($id);
            if (!$fileModel) {
                throw new Exception("Not a file!", 1);
            }

            $this->load->library('form_validation');

            $this->form_validation->set_rules("title", "Comment title", "required|xss_clean");
            $this->form_validation->set_rules("content", "Comment content", "xss_clean|required");

            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                throw new Exception($errors, 1);
            }

            $title = $this->input->post('title', true);
            $content = $this->input->post('content', true);

            $comment = $fileModel->addComment($title, $content, $this->currentUser()->model());
            if (!$comment) {
                throw new Exception("Error in adding the comment", 1);
            }

            $this->redirectWithOperationMessage("files/view/{$fileModel->id}", "The comment has been added!");
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    public function updateFile() {
//        $this->output->enable_profiler(TRUE);

        try {

            $this->load->library('form_validation');

            $this->form_validation->set_rules("id", "File id", "required|is_natural_no_zero|xss_clean");
            $this->form_validation->set_rules("title", "File title", "required|xss_clean");
            $this->form_validation->set_rules("category", "File category", "xss_clean|is_natural_no_zero");
            $this->form_validation->set_rules("tags", "File tags", "xss_clean");

            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                throw new Exception($errors, 1);
            }

            $id = $this->input->post('id', true);
            $fileModel = FileQuery::getInstance()->findById($id);
            if (!$fileModel) {
                throw new Exception("Not a file!", 1);
            }

            $title = $this->input->post('title', true);
            $content = $this->input->post('description', true);
            $category = $this->input->post('category', true);
            $tags = $this->input->post('tags', true);

            //preparing category
            if ($category) {
                $categoryModel = CategoryQuery::getInstance()->findById($category);
            } else {
                $categoryModel = null;
            }

            $error = $fileModel->update($title, $content, $categoryModel, $tags);
            if ($error) {
                throw new Exception("Error in adding the comment", 1);
            }

            $this->redirectWithOperationMessage("files/view/{$id}", "The file has been updated!");
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    protected function getFileFullPath($name) {
        return __DIR__ . DIRECTORY_SEPARATOR . self::LEVEL_UP . DIRECTORY_SEPARATOR . self::LEVEL_UP . DIRECTORY_SEPARATOR . "/assets/uploads/" . $name;
    }

}
