<?php
use Lijyue925\CKIP\CKIPClient;
require_once('src/CKIPClient.php');

// change to yours
define("CKIP_SERVER", "000.000.000.000");
define("CKIP_PORT", 0000);
define("CKIP_USERNAME", "xxxxxx");
define("CKIP_PASSWORD", "xxxxxxxxx");

$CKIP = new CKIPClient(
    CKIP_SERVER,
    CKIP_PORT,
    CKIP_USERNAME,
    CKIP_PASSWORD
);

$raw_text = "站在巨人的肩膀上，一步一步充實自己。";

print_r($CKIP->getSentence($raw_text));

print_r($CKIP->getTerm($raw_text));
