<?php
require_once 'Application/AdminService.php'; use App\Application\AdminService;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $adminService = new AdminService();

    if ($adminService->isProductNameExists($name)->num_rows > 0) {
        echo "exists";
    } else {
        echo "available";
    }
}