<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
  | -------------------------------------------------------------------------
  | URI ROUTING
  | -------------------------------------------------------------------------
  | This file lets you re-map URI requests to specific controller functions.
  |
  | Typically there is a one-to-one relationship between a URL string
  | and its corresponding controller class/method. The segments in a
  | URL normally follow this pattern:
  |
  |	example.com/class/method/id/
  |
  | In some instances, however, you may want to remap this relationship
  | so that a different class/function is called than the one
  | corresponding to the URL.
  |
  | Please see the user guide for complete details:
  |
  |	http://codeigniter.com/user_guide/general/routing.html
  |
  | -------------------------------------------------------------------------
  | RESERVED ROUTES
  | -------------------------------------------------------------------------
  |
  | There area two reserved routes:
  |
  |	$route['default_controller'] = 'welcome';
  |
  | This route indicates which controller class should be loaded if the
  | URI contains no data. In the above example, the "welcome" class
  | would be loaded.
  |
  |	$route['404_override'] = 'errors/page_missing';
  |
  | This route will tell the Router what URI segments to use if those provided
  | in the URL cannot be matched to a valid route.
  |
 */

$route['default_controller'] = 'pages/home';
$route['account/?'] = 'account/index';
$route['account/(:any)'] = 'account/$1';
$route['landing'] = 'landing/Index';
$route['landing/(:any)'] = 'landing/$1';
$route['pages/(:any)'] = 'pages/$1';
$route['users'] = 'users/Index';
$route['users/(:num)'] = 'users/Index/$1';
$route['users/(:any)'] = 'users/$1';
$route['profile'] = 'profile/Index';
$route['profile/(:any)'] = 'profile/$1';
$route['categories/?'] = 'categories/Index';
$route['cateogires/(:any)'] = 'categories/$1';
$route['posts/?'] = 'posts/Index';
$route['posts/(:num)'] = 'posts/Index/$1';
$route['posts/(:any)\?(:any)'] = 'posts/$1?$2';
$route['tags/(:any)'] = 'tags/$1';
$route['tags/(:any)/(:any)'] = 'tags/$1/$2';
$route['messages/?'] = 'messages/index';
$route['messages/(:num)'] = 'messages/index/$1';
$route['messages/chats_list(/(:num))?'] = 'messages/getChatsList/$2';
$route['messages/new_chats_count'] = 'messages/getNewChatsCount';
$route['messages/edit_chat'] = 'messages/editChat';
$route['messages/leave_chat'] = 'messages/leaveChat';
$route['messages/(:any)'] = 'messages/$1';
$route['files/?'] = 'files/index';
$route['files/(:num)'] = 'files/index/$1';
$route['files/new'] = 'files/newFile';
$route['files/new_version/(:any)'] = 'files/newVersion/$1';
$route['files/upload_new'] = 'files/uploadNewFile';
$route['files/upload_version'] = 'files/uploadNewVersion';
$route['files/add_comment/(:any)'] = 'files/addComment/$1';
$route['files/remove_file/(:any)'] = 'files/removeFile/$1';
$route['files/remove_version/(:any)'] = 'files/removeVersion/$1';
$route['permalink/(:any)/(:any)'] = 'permalink/$1/$2';
$route['permalink(:any)'] = 'landing';
$route['(:any)/(:any)'] = '$1/$2';
$route['(:any)'] = 'pages/$1';
$route['404_override'] = '';


/* End of file routes.php */
/* Location: ./application/config/routes.php */