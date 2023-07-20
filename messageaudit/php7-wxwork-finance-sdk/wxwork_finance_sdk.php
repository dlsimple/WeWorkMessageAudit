<?php


try {
$obj = new WxworkFinanceSdk("wwd94a26ac15fa69cf", "umKYgNGTV3ZFfurxFass0GpBrNRe5-mVu_R8wD6XCHM", [
    "timeout" => -2,
]);
// 私钥地址
$privateKey = file_get_contents('private.pem');

$chats = json_decode($obj->getChatData(0, 100), true);
foreach ($chats['chatdata'] as $val) {
    // var_dump($val);
    $decryptRandKey = null;
    openssl_private_decrypt(base64_decode($val['encrypt_random_key']), $decryptRandKey, $privateKey, OPENSSL_PKCS1_PADDING);
    var_dump($decryptRandKey);
    // $obj->downloadMedia($sdkFileId, "/tmp/download/文件新名称.后缀");
}


}catch(\WxworkFinanceSdkException $e) {
    var_dump($e->getMessage(), $e->getCode());
}
