<div style="width: 100%; margin: 0 auto; display: table;">
    <?php
    $merchant_id = '';
    $merchant_key = '';
    $merchant_salt = '';

    // Assuming $paymentInformation is passed to the view
    $email = 'uydevp@gmail.com';

    $payment_amount = $_GET['payment_amount'];

    $merchant_oid = "11";
    $user_name = "UĞURCAN YAŞ";
    $user_address = "Adana";
    $user_phone = "5555555555";

    $merchant_ok_url = "https://bygizembutik.com/odeme-sonrasi/ok/" . $merchant_oid;
    $merchant_fail_url = "https://bygizembutik.com/odeme-sonrasi/fail/" . $merchant_oid;

    $user_basket = base64_encode(json_encode([
        [
            "product_id" => 1,
            "product_name" => "Test Ürünü",
            "product_amount" => 1,
            "product_price" => 1.00
        ]
    ]));

    if (isset($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else {
        $ip = $_SERVER["REMOTE_ADDR"];
    }

    $user_ip = $ip;

    $timeout_limit = "1000";
    $debug_on = 1;
    $test_mode = 1;
    $no_installment = 0;
    $max_installment = 0;
    $currency = "TL";

    $hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $user_basket . $no_installment . $max_installment . $currency . $test_mode;
    $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));

    $post_vals = array(
        'merchant_id' => $merchant_id,
        'user_ip' => $user_ip,
        'merchant_oid' => $merchant_oid,
        'email' => $email,
        'payment_amount' => $payment_amount,
        'paytr_token' => $paytr_token,
        'user_basket' => $user_basket,
        'debug_on' => $debug_on,
        'no_installment' => $no_installment,
        'max_installment' => $max_installment,
        'user_name' => $user_name,
        'user_address' => $user_address,
        'user_phone' => $user_phone,
        'merchant_ok_url' => $merchant_ok_url,
        'merchant_fail_url' => $merchant_fail_url,
        'timeout_limit' => $timeout_limit,
        'currency' => $currency,
        'test_mode' => $test_mode
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $result = @curl_exec($ch);

    if (curl_errno($ch))
        die("PAYTR IFRAME connection error. err:" . curl_error($ch));

    curl_close($ch);

    $result = json_decode($result, 1);

    if ($result['status'] == 'success')
        $token = $result['token'];
    else
        die("PAYTR IFRAME failed. reason:" . $result['reason']);
    ?>

    <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
    <iframe src="https://www.paytr.com/odeme/guvenli/<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
    <script>
        iFrameResize({}, '#paytriframe');
    </script>

</div>
