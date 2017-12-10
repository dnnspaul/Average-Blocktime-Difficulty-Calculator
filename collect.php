<?php
require_once("ethereum.php"); // https://github.com/btelle/ethereum-php

function decode_hex($input) { // Function stolen from ethereum.php
	if(substr($input, 0, 2) == '0x')
		$input = substr($input, 2);

	if(preg_match('/[a-f0-9]+/', $input))
		return hexdec($input);

	return $input;
}

function encode_hex($input) {
	return "0x" . dechex($input);
}

$ethereum = new Ethereum('127.0.0.1', 8545); // Connect to your ethereum-jsonrpc
$requestLastXBlocks = 100; // Request last X blocks from our ethereum-client to calculate difficulty and blockTime

$current_blockinfo = $ethereum->eth_getBlockByNumber("latest", FALSE);
$data = array("difficulty" => decode_hex($current_blockinfo->difficulty), "blocktime" => array(decode_hex($current_blockinfo->timestamp)));

for($i = 1; $i < $requestLastXBlocks; $i++) { // skip ($i = 0) because we already have the current block
    $past_blockinfo = $ethereum->eth_getBlockByNumber(encode_hex(decode_hex($current_blockinfo->number) - $i), FALSE);

    $data["difficulty"] += decode_hex($past_blockinfo->difficulty);
    $data["blocktime"][] = decode_hex($past_blockinfo->timestamp);
}

$data["difficulty"] = floatval($data["difficulty"])/$requestLastXBlocks;

$blocktime_range = 0;
for($i = 1; $i < $requestLastXBlocks; $i++) {
    $blocktime_range += $data["blocktime"][$i - 1] - $data["blocktime"][$i];
}

$data["blocktime"] = $blocktime_range/$requestLastXBlocks;

echo json_encode($data);
?>
