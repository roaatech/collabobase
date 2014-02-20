<?php

class PermaLinkHelper {
    
}

class FilePermaLink extends PermaLinkHelper {

    public static function view($id) {
        return base_url("permalink/file_view/{$id}");
    }

    public static function download($id, $versionId = null) {
        return base_url("permalink/file_download/{$id}" . ($versionId ? "/{$versionId}" : ""));
    }

    public static function newVersion($id) {
        return base_url("files/new_version/{$id}");
    }

    public static function editVersion($id) {
        return base_url("files/edit_version/{$id}");
    }

    public static function removeVersion($id) {
        return base_url("files/remove_version/{$id}");
    }

    public static function allVersions($id) {
        return base_url("files/all_versions/{$id}");
    }

    public static function editFile($id) {
        return base_url("files/edit/{$id}");
    }

    public static function removeFile($id) {
        return base_url("files/remove_file/{$id}");
    }

    public static function addComment($id) {
        return base_url("files/add_comment/$id");
    }

}

class PostPermalink extends PermaLinkHelper {

    public static function view($id) {
        return base_url("posts/post/$id");
    }

}
