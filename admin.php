<?php
require_once 'App/Domain/Users/UserEntity.php'; use App\Domain\Users\UserEntity;

$user = new UserEntity();
if (!$user->isAdmin) die('Доступ закрыт');
?>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          crossorigin="anonymous">
    <link href="assets/css/style.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h1 class="w-100 d-flex justify-content-center">Админка</h1>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            Добавление нового товара
        </button>

        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title justify-content-center" id="addProductModalLabel">Добавление нового товара</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <form action="App/add_product.php" method="POST" id="productForm">
                            <label class="form-label" for="name">Название:</label>
                            <input type="text" class="form-control" id="name" name="name" autocomplete="off" required onkeypress="return allowNameInput(event)" onpaste="handleNamePaste(event)">
                            <div class="invalid-feedback" id="nameFeedback"></div>

                            <label class="form-label" for="price">Цена:</label>
                            <input type="number" class="form-control" id="price" name="price" min=0 step="0.1" autocomplete="off" required onkeypress="return filterFloatInput(event)" onpaste="filterFloatPaste(this)">

                            <div class="tariff mt-3">
                                <h3>Тарифы</h3>
                                <table class="table table-bordered border-dark" id="tariffTable">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 40%">Количество</th>
                                            <th scope="col" style="width: 40%">Цена</th>
                                            <th scope="col" style="width: 20%">Действие</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <button type="button" class="btn btn-primary mt-2" id="addRow">Добавить тариф</button>
                            </div>
                            <input type="hidden" id="tariffData" name="tariff">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                <button type="submit" class="btn btn-success" id="saveProduct">Сохранить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="result"></div>
    </div>

<script>
    $(document).ready(function() {
        $("#name").on("change", function() {
            let name = $(this).val().trim();

            if (name.length > 0) {
                $.ajax({
                    url: "App/check_product_name.php",
                    type: "POST",
                    data: { name: name },
                    success: function(response) {
                        if (response === "exists") {
                            $("#name").addClass("is-invalid");
                            $("#nameFeedback").text("Такой товар уже существует!");
                            $("#saveProduct").prop("disabled", true);
                        } else {
                            $("#name").removeClass("is-invalid");
                            $("#nameFeedback").text("");
                            $("#saveProduct").prop("disabled", false);
                        }
                    }
                });
            } else {
                $("#name").removeClass("is-invalid");
                $("#nameFeedback").text("");
                $("#saveProduct").prop("disabled", false);
            }
        });

        $("#addRow").click(function() {
            $("#tariffTable tbody").append(`
                <tr>
                    <td><input class="form-control tariff-key" type="number" min="0" max="999999" step="1" autocomplete="off" required onkeypress="filterIntInput(this)"onpaste="filterIntPaste(this)"></td>
                    <td><input class="form-control tariff-value" type="number" min="0" step="0.1" autocomplete="off" required onkeypress="return filterFloatInput(event)" onpaste="filterFloatPaste(this)"></td>
                    <td><button type="button" class="btn btn-danger removeRow">Удалить</button></td>
                </tr>
            `);
        });

        $(document).on("click", ".removeRow", function() {
            $(this).closest("tr").remove();
        });

        $("#productForm").submit(function(event) {
            event.preventDefault();
            prepareTariffData();

            let formData = {
                name: $("#name").val(),
                price: $("#price").val(),
                tariff: $("#tariffData").val()
            };

            debugger
            $("#tariffModal").hide();

            $.ajax({
                url: "App/add_product.php",
                type: "POST",
                data: formData,
                success: function(response) {
                    $("#result").html(response);
                },
                error: function(response) {
                    $("#result").html("Ошибка при добавлении продукта.");
                }
            });
        });
    });

    function prepareTariffData() {
        let tariffs = {};
        $("#tariffTable tbody tr").each(function() {
            let key = $(this).find(".tariff-key").val();
            let value = $(this).find(".tariff-value").val();
            // let value = $(this).find(".tariff-value").val().replace(".", ",");
            if (key && value) {
                tariffs[key] = value;
            }
        });

        $("#tariffData").val(JSON.stringify(tariffs));
        $("#tariffModal").hide();
    }

    function filterIntInput(element) {
        let char = String.fromCharCode(event.which);
        if (!/^[0-9]$/.test(char)) {
            event.preventDefault();
        }
    }

    function filterFloatInput(element) {
        let char = event.key;

        if (!/^[0-9,]$/.test(char)) {
            return false;
        }

        let input = event.target;
        let value = input.value;

        if (char === ',' && value.includes(',')) {
            return false;
        }

        if (char === ',' && value.length === 0) {
            return false;
        }

        return true;
    }

    function filterIntPaste(element) {
        setTimeout(() => {
            element.value = element.value.replace(/[^0-9]/g, '');
        }, 0);
    }

    function filterFloatPaste(element) {
        event.preventDefault();

        let pasteData = (event.clipboardData || window.clipboardData).getData('text');
        pasteData = pasteData.replace(/[^0-9,]/g, '');
        let input = event.target;

        if (pasteData.includes(',') && input.value.includes(',')) {
            pasteData = pasteData.replace(/,/g, '');
        }

        document.execCommand('insertText', false, pasteData);
    }

    function allowNameInput(event) {
        let char = event.key;

        if (!/^[a-zA-Zа-яА-ЯёЁ0-9\s]$/.test(char)) {
            return false;
        }

        return true;
    }

    function handleNamePaste(event) {
        event.preventDefault();

        let pasteData = (event.clipboardData || window.clipboardData).getData('text');
        pasteData = pasteData.replace(/[^a-zA-Zа-яА-ЯёЁ0-9\s]/g, '');

        document.execCommand('insertText', false, pasteData);
    }
    </script>
</body>
</html>