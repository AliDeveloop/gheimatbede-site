<?php
// تنظیم هدر برای اطمینان از اینکه خروجی به صورت UTF-8 باشد
header('Content-Type: text/html; charset=utf-8');

// تابع برای ارسال درخواست HTTP
function fetchData($url, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// تابع برای ذخیره داده‌ها در فایل JSON
function saveToJson($data, $filename) {
    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// 1. دریافت داده‌ها از Binance
$binanceUrl = "https://api.binance.com/api/v3/ticker/24hr";
$binanceData = fetchData($binanceUrl);
$binanceResult = [];
foreach ($binanceData as $item) {
    $binanceResult[$item['symbol']] = [
        'price' => $item['lastPrice'],
        'change_24h' => $item['priceChangePercent']
    ];
}
saveToJson($binanceResult, 'binance.json');

// 2. دریافت داده‌ها از OKX
$okxUrl = "https://www.okx.com/api/v5/market/tickers?instType=SPOT";
$okxData = fetchData($okxUrl);
$okxResult = [];
if (isset($okxData['data'])) {
    foreach ($okxData['data'] as $item) {
        $okxResult[$item['instId']] = [
            'price' => $item['last'],
            'change_24h' => $item['idxPxChg24h'] ?? null // ممکن است این فیلد متفاوت باشد
        ];
    }
}
saveToJson($okxResult, 'okx.json');

// 3. دریافت داده‌ها از Bybit
$bybitUrl = "https://api.bybit.com/v5/market/tickers?category=spot";
$bybitData = fetchData($bybitUrl);
$bybitResult = [];
if (isset($bybitData['result']['list'])) {
    foreach ($bybitData['result']['list'] as $item) {
        $bybitResult[$item['symbol']] = [
            'price' => $item['lastPrice'],
            'change_24h' => $item['price24hPcnt']
        ];
    }
}
saveToJson($bybitResult, 'bybit.json');

// 4. دریافت داده‌ها از CoinEx
$coinexUrl = "https://api.coinex.com/v1/market/ticker/all";
$coinexData = fetchData($coinexUrl);
$coinexResult = [];
if (isset($coinexData['data']['ticker'])) {
    foreach ($coinexData['data']['ticker'] as $symbol => $item) {
        $coinexResult[$symbol] = [
            'price' => $item['last'],
            'change_24h' => $item['vol'] ? (($item['last'] - $item['open']) / $item['open'] * 100) : null
        ];
    }
}
saveToJson($coinexResult, 'coinex.json');

// پیام موفقیت
echo "داده‌ها با موفقیت از صرافی‌ها دریافت و در فایل‌های JSON ذخیره شدند.";
?>