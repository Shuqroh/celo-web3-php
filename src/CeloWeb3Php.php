<?php

namespace Shuqroh\CeloWeb3Php;


$celo_mainnet = [
    "id" => 42220,
    "hexId" => "0xa4ec",
    "name" => "Celo Mainnet",
    "rpcUrl" => "https://forno.celo.org",
    "explorerUrl" => "	https://celoscan.com",
    "nativeCurrency" => [
        "symbol" => "CELO",
        "decimals" => 18
    ]
];


$celo_testnet = [
    "id" => 44787,
    "hexId" => "0xaef3",
    "name" => "Celo Testnet Alfajores",
    "rpcUrl" => "https://alfajores-forno.celo-testnet.org",
    "explorerUrl" => "https://alfajores-blockscout.celo-testnet.org/",
    "nativeCurrency" => [
        "symbol" => "CELO",
        "decimals" => 18
    ]
];

$cusd_token_address = [
    "mainnet" => "0x765DE816845861e75A25fCA122bb6898B8B1282a",
    "testnet" => "0x874069Fa1Eb16D44d622F2e0Ca25eeA172369bC1"
];


/**
 * Create web3 provider instance
 * @var Provider
 */

$provider =  new Provider($celo_testnet, true);


/**
 * get provder network
 * @var Object
 */

print_r($provider->getNetwork());



/**
 * transfer celo
 * @var string
 */


$from = "0x8d8e9c5b1e162a7d0eef414a8e6e0f1a4d9d4d9b";
$to = "0x8d8e9c5b1e162a7d0eef414a8e6e0f1a4d9d4d9b";
$amount = 1;

print_r($provider->transfer($from, $to, $amount));


/**
 * transfer cusd
 * @var string
 */


$from = "0x8d8e9c5b1e162a7d0eef414a8e6e0f1a4d9d4d9b";
$to = "0x8d8e9c5b1e162a7d0eef414a8e6e0f1a4d9d4d9b";
$amount = 1;
$token_address = "0x";

print_r($provider->transfer($from, $to, $amount, $cusd_token_address['testnet']));



/**
 * connect to smart contract
 * @var string
 * 
 */

$contract_address = "0x8d8e9c5b1e162a7d0eef414a8e6e0f1a4d9d4d9b";
$contract_abi = [];

$contract = new Contract($contract_address, $contract_abi, $provider);


/**
 * call smart contract function
 * @var string
 * 
 */

print_r($contract->call("function_name", ["param1", "param2"]));
