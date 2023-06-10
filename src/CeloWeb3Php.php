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
