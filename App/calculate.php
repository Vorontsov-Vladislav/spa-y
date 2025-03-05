<?php
namespace App;
require_once 'Infrastructure/sdbh.php'; use sdbh\sdbh;

class Calculate
{
    public function calculate1()
    {
        $dbh = new sdbh();
        $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
        $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

        if ($start_date && $end_date) {
            $start = new \DateTime($start_date);
            $end = new \DateTime($end_date);
            $interval = $start->diff($end);

            $rental_days = max($interval->days, 1);
        }

        $product_id = isset($_POST['product']) ? $_POST['product'] : 0;
        $selected_services = isset($_POST['services']) ? $_POST['services'] : [];
        $product = $dbh->make_query("SELECT * FROM a25_products WHERE ID = $product_id");
        if ($product) {
            $product = $product[0];
            $price = $product['PRICE'];
            $tarif = $product['TARIFF'];
        } else {
            echo "Ошибка, товар не найден!";
            return;
        }

        $tarifs = unserialize($tarif);
        if (is_array($tarifs)) {
            $product_price = $price;
            foreach ($tarifs as $day_count => $tarif_price) {
                if ($rental_days >= $day_count) {
                    $product_price = $tarif_price;
                }
            }
            $total_price = $product_price * $rental_days;
        }else{
            $total_price = $price * $rental_days;
        }

        $services_price = 0;
        foreach ($selected_services as $service) {
            $services_price += (float)$service * $rental_days;
        }

        $total_price += $services_price;

        echo $total_price;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instance = new Calculate();
    $instance->calculate1();
}