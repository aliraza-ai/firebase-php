<?php
include('db.php');

$curl = curl_init('https://eb.pakgold.net/PGR/CurrencyRate');
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
]);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);

$currencyUpdate=
[
    'dollarBuy' =>$data['dollarBuy'],
    'dollarSell' => $data['dollarSell'],
    'gbpSell' => $data['gbpSell'],
    'gbpBuy' => $data['gbpBuy'],
    'euroBuy' => $data['euroBuy'],
    'euroSell' => $data['euroSell'],
    'malayBuy' => $data['malayBuy'],
    'malaySell' => $data['malaySell'],
    'riyalBuy' => $data['riyalBuy'],
    'riyalSell' => $data['riyalSell'],
    'dirhamBuy' => $data['dirhamBuy'],
    'dirhamSell' => $data['dirhamSell'],
    'dollarInterBankBuy' => $data['dollarInterBankBuy'],
    'dollarInterBankSell' => $data['dollarInterBankSell'],
    'dollarUpdateTime' => $data['dollarUpdateTime'],
];

$ref_goldTable='goldrate';
$ref_currency='currency';

$database->getReference($ref_currency)->update($currencyUpdate);
?>

<?php

// Fetch data from URLs and process results as before
$urls = 
[
    'https://blockchain.info/ticker',
    'https://eb.pakgold.net/PGR/CurrencyRate',
    'https://eb.pakgold.net/AndroidPGR/GetBoardRate',
    'https://eb.pakgold.net/AndroidPGR/GetMainPageRate'
];

function fetchData($url) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

$mh = curl_multi_init();
$handles = [];

foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
    ]);
    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
}

$running = null;
do {
    curl_multi_exec($mh, $running);
} while ($running > 0);

$results = [];

foreach ($handles as $ch) {
    $response = curl_multi_getcontent($ch);
    $results[] = json_decode($response, true);
    curl_multi_remove_handle($mh, $ch);
}

curl_multi_close($mh);


// Process the results as needed
foreach ($results as $result) {
    if (isset($result["USD"]["15m"])) {
        $btc = number_format((float)$result["USD"]["15m"], 2, '.', '');
    }

    if (isset($result["dollarBuy"]) && isset($result["dollarSell"])) {
        $buyDollar = $result["dollarBuy"];
        $sellDollar = $result["dollarSell"];
    }

    if (isset($result["_22ktBuy"]) && isset($result["_22ktSell"])) {
        $buy22kt = $result["_22ktBuy"];
        $sell22kt = $result["_22ktSell"];
    }

    if (isset($result["pcsBuy"]) && isset($result["pcsSell"])) {
        $buy24k = $result["pcsBuy"];
        $gramsGoldBuy = number_format((float)$result["pcsBuy"] / 11.7, 2, '.', '');
        $sell24k = $result["pcsSell"];
        $gramsGoldSell = number_format((float)$result["pcsSell"] / 11.7, 2, '.', '');
    }

    if (isset($result["silver"])) {
        $silverB = ($result["silver"] / 2.42) * $buyDollar;
        $silverS = ($result["silver"] / 2.42) * $sellDollar;
        $buySilver = number_format((float)$silverB, 2, '.', '');
        $sellSilver = number_format((float)$silverS, 2, '.', '');
    }

    if (isset($result["kse"])) {
        $index = $result["kse"];
    }
}


$GoldUpdate=
[
    'btc' =>$btc,
    'buy22kt' => $buy22kt,
    'sell22kt' => $sell22kt,
    'buy24k' => $buy24k,
    'sell24k' => $sell24k,
    'gramsGoldBuy' => $gramsGoldBuy,
    'gramsGoldSell' => $gramsGoldSell,
    'buySilver' => $buySilver,
    'index' => $index,
    'dateTime' => $data['dollarUpdateTime'],
];
$database->getReference($ref_goldTable)->update($GoldUpdate);


?>