<?php
// آدرس API کاوه نگار
$apiUrl = "http://api.kavenegar.com/v1/70386E577761486E416148485264525346363734456277357677625679446E45413651453967523037486B3D/verify/lookup.json";
$params = [
    'receptor' => '09301303005',
    'token'    => '1234',
    'template' => 'PanelOTP'
];

// ساخت URL با پارامترها
$queryString = http_build_query($params);
$fullUrl = $apiUrl . '?' . $queryString;

// شروع لاگ‌گیری
$logFile = __DIR__ . '/curl_test_log.txt';
$logData = "Starting CURL test at " . date('Y-m-d H:i:s') . "\n";

// تنظیمات CURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fullUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true); // دریافت هدرهای HTTP
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // تنظیم Timeout به 10 ثانیه
curl_setopt($ch, CURLOPT_VERBOSE, true); // فعال کردن لاگ داخلی
curl_setopt($ch, CURLOPT_STDERR, fopen($logFile, 'a')); // ذخیره لاگ داخلی در فایل
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

// اجرای درخواست
$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ذخیره نتیجه در لاگ
if ($error) {
    $logData .= "CURL Error: $error\n";
} else {
    $logData .= "HTTP Code: $httpCode\n";
    $logData .= "Response: $response\n";
}

// ذخیره لاگ در فایل
file_put_contents($logFile, $logData, FILE_APPEND);

// نمایش نتیجه در مرورگر
if ($error) {
    echo "Error occurred: $error. Check the log file for details.";
} else {
    echo "Request successful. Response logged.";
}
