<?php
namespace App\Application;
require_once 'Domain/Users/UserEntity.php'; use App\Domain\Users\UserEntity;
require_once 'Infrastructure/sdbh.php'; use sdbh\sdbh;

class AdminService {

    /** @var UserEntity */
    public $user;

    /** @var sdbh */
    private $sdbh;

    public function __construct()
    {
        $this->user = new UserEntity();
        $this->sdbh = new sdbh();
    }

    public function addNewProduct($name, $price, $tariff)
    {
        if (!$this->user->isAdmin) return;

        $params = [
            'name' => $name,
            'price' => $price,
            'tariff' => $tariff
        ];

        return $this->sdbh->insert_row('a25_products', $params);
    }

    public function isProductNameExists($name) {
        $query = "SELECT * FROM a25_products WHERE NAME = '$name'";
        return $this->sdbh->query_exc($query);
    }
}