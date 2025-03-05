<?php
require_once 'Application/AdminService.php'; use App\Application\AdminService;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $tariffData = $_POST['tariff'] ?? '{}';

    if (!empty($name) && !empty($price) && !empty($tariffData)) {
        $adminService = new AdminService();

        $tariffsArray = json_decode($tariffData, true);
        $serializedTariffs = serialize($tariffsArray);

        $params = [
            'name' => $name,
            'price' => (float)$price,
            'tariff' => $serializedTariffs
        ];

        $result = $adminService->addNewProduct($name, $price, $serializedTariffs);

        echo $result ? "Продукт успешно добавлен!" : "Ошибка при добавлении продукта.";
    } else {
        echo "Все поля обязательны.";
    }
}