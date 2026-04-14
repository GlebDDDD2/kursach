<?php
session_start();

require_once __DIR__ . '/../site-core/src/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\ProfileController;

Autoloader::register(__DIR__ . '/../site-core/src');
require_once __DIR__ . '/../site-core/config/Database.php';

$page = $_GET['page'] ?? 'home';

$home = new HomeController();
$auth = new AuthController();
$profile = new ProfileController();
$admin = new AdminController();

switch ($page) {
    case 'home':
        $home->index();
        break;
    case 'catalog':
        $home->catalog();
        break;
    case 'property':
        $home->property();
        break;
    case 'login':
        $auth->login();
        break;
    case 'register':
        $auth->register();
        break;
    case 'logout':
        $auth->logout();
        break;
    case 'profile':
        $profile->index();
        break;
    case 'request_details':
        $profile->requestDetails();
        break;
    case 'change_password':
        $profile->changePassword();
        break;
    case 'request_viewing':
        $profile->requestViewing();
        break;
    case 'admin':
        $admin->dashboard();
        break;
    case 'admin_property_add':
        $admin->propertyForm();
        break;
    case 'admin_property_edit':
        $admin->propertyForm(max(0, (int) ($_GET['id'] ?? 0)));
        break;
    case 'admin_property_store':
        $admin->storeProperty();
        break;
    case 'admin_property_update':
        $admin->updateProperty();
        break;
    case 'admin_property_delete':
        $admin->deleteProperty();
        break;
    case 'admin_toggle_visibility':
        $admin->togglePropertyVisibility();
        break;
    case 'admin_requests':
        $admin->requests();
        break;
    case 'admin_request_status':
        $admin->updateRequestStatus();
        break;
    case 'admin_seeder':
        $admin->seeder();
        break;
    default:
        http_response_code(404);
        echo 'Страница не найдена';
}
