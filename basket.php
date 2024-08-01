<?php
session_start();
$connect = mysqli_connect("localhost", "root", "", "shopping_cart");

if (isset($_POST["add_to_cart"])) {
    if (isset($_SESSION["cart"])) {
        $session_array_id = array_column($_SESSION["cart"], "id");

        if (!in_array($_GET["id"], $session_array_id)) {
            $session_array = array(
                "id" => $_GET["id"],
                "name" => $_POST["name"],
                "price" => number_format($_POST["price"], 2, '.', ''),
                "quantity" => $_POST["quantity"]
            );

            $_SESSION["cart"][] = $session_array;
        } else {
            foreach ($_SESSION["cart"] as $key => $value) {
                if ($value["id"] == $_GET["id"]) {
                    $_SESSION["cart"][$key]["quantity"] += $_POST["quantity"];
                }
            }
        }
    } else {
        $session_array = array(
            "id" => $_GET["id"],
            "name" => $_POST["name"],
            "price" => number_format($_POST["price"], 2, '.', ''),
            "quantity" => $_POST["quantity"]
        );

        $_SESSION["cart"][] = $session_array;
    }
}

if (isset($_POST["update_cart"])) {
    foreach ($_POST["quantity"] as $id => $quantity) {
        foreach ($_SESSION["cart"] as $key => $value) {
            if ($value["id"] == $id) {
                $_SESSION["cart"][$key]["quantity"] = $quantity;

                if ($quantity <= 0) {
                    unset($_SESSION["cart"][$key]);
                }
            }
        }
    }
}

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'clearall') {
        unset($_SESSION['cart']);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Alışveriş Sepeti</h2>
                <div class="row g-4">
                    <?php
                    $query = "SELECT * FROM cart_list";
                    $result = mysqli_query($connect, $query);

                    while ($row = mysqli_fetch_array($result)) { ?>

                        <div class="col-md-6">
                            <div class="card">
                                <img src="img/<?= $row['img'] ?>" class="card-img-top" alt="<?= $row['name'] ?>"
                                    style='height: 200px; object-fit: cover;'>
                                <div class="card-body">
                                    <h5 class="card-title"><?= $row['name']; ?></h5>
                                    <p class="card-text"><?= number_format($row['price'], 2, '.', ''); ?>₺</p>
                                    <form method="post" action="basket.php?id=<?= $row['id'] ?>">
                                        <input type="hidden" name="name" value="<?= $row['name'] ?>">
                                        <input type="hidden" name="price" value="<?= number_format($row['price'], 2, '.', '') ?>">
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">Adet:</label>
                                            <input type="number" name="quantity" value="1" class="form-control" min="1">
                                        </div>
                                        <button type="submit" name="add_to_cart" class="btn btn-warning w-100">Sepete Ekle</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php } ?>
                </div>
            </div>

            <div class="col-md-6">
                <h2 class="text-center mb-4">Seçilen Ürünler</h2>
                <?php
                $total = 0;
                $output = "";

                if (!empty($_SESSION["cart"])) {
                    $output .= "
                    <form method='post' action='basket.php'>
                    <table class='table table-bordered'>
                        <thead class='table-dark'>
                            <tr>
                                <th>ID</th>
                                <th>Ürün Adı</th>
                                <th>Ürün Fiyatı</th>
                                <th>Adet</th>
                                <th>Toplam Fiyat</th>
                            </tr>
                        </thead>
                        <tbody>
                    ";

                    foreach ($_SESSION["cart"] as $key => $value) {
                        $output .= "
                        <tr>
                            <td>{$value['id']}</td>
                            <td>{$value['name']}</td>
                            <td>₺" . number_format($value['price'], 2, '.', '') . "</td>
                            <td>
                                <input type='number' name='quantity[{$value['id']}]' value='{$value['quantity']}' class='form-control' style='width: 70px;' min='0'>
                            </td>
                            <td>₺" . number_format($value['price'] * $value['quantity'], 2, '.', '') . "</td>
                        </tr>
                        ";

                        $total += $value["quantity"] * $value["price"];
                    }

                    $output .= "
                        <tr>
                            <td colspan='4' class='text-end'><b>Toplam Fiyat</b></td>
                            <td>₺" . number_format($total, 2, '.', '') . "</td>
                        </tr>
                        <tr>
                            <td colspan='5' class='text-end'>
                                <button type='submit' name='update_cart' class='btn btn-primary btn-sm'>Sepeti Güncelle</button>
                                <a href='basket.php?action=clearall' class='btn btn-warning btn-sm'>Sepeti Temizle</a>
                                <a href='odeme.php?payment_amount=" . number_format($total * 100, 0, '', '') . "' class='btn btn-success btn-sm'>Ödeme Yap</a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    </form>
                    ";
                }

                echo $output;
                ?>
            </div>
        </div>
    </div>
</body>
</html>
