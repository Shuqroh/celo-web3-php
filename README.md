# Utilization of the WEB3.php Framework using Celo Blockchain for seamless communication:

## Table of Contents:

- [Utilization of the WEB3.php Framework using Celo Blockchain for seamless communication](#utilization-of-the-web3.php-framework-using-celo-blockchain-for-seamless-communication)
- [Introduction](#introduction)
- [Pre-requisites](#pre-requisites)
- [Getting Started](#getting-started)
  - [How it works?](#how-it-works-?)
  - [Configure composer.json](#configure-composer.json)
  - [Code Implementation](#code-implementation)
- [Conclusion](conclusion)
- [About the Author](#about-the-author)
- [References](#references)
  
## Introduction:

Through this tutorial, we will explore the utilization of the web3.php framework for seamless communication with the Celo blockchain. By the tutorial's end, you will possess the capability to connect to the Celo network, access blockchain data and effortlessly send transactions using web3.php.

## Pre-requisites:

* Basic knowledge of [PHP](https://www.w3schools.com/php/) programming.
* Familiarity with [Blockchain](https://aws.amazon.com/what-is/blockchain/?aws-products-all.sort-by=item.additionalFields.productNameLowercase&aws-products-all.sort-order=asc) concepts.

## Getting Started:

Assuming familiarity with **Composer**, I will omit the setup instructions for installing and configuring **Composer** on your development computer. Additionally, I assume you have already set up [VS Code](https://code.visualstudio.com/download) on your PC. For macOS users, PHP and Composer can be easily installed via [Homebrew](https://brew.sh/).

If you are new to PHP development and have never used **Composer** before, you can learn more about it at [getcomposer.org](https://getcomposer.org/).

To begin, you can clone the [celo-web3-php](https://github.com/Shuqroh/celo-web3-php) repository.

**Composer** is a powerful tool for managing dependencies in PHP projects. It enables you to specify the libraries your project relies on and automatically handles their installation and updates for you.

### How it works?

Web3-php is a PHP interface for interacting with the Ethereum blockchain and ecosystem. Native ABI parsing and smart contract interactions.

### Configure composer.json:

Create a new folder, named "celo-web3-php," to manage the project. Initiate Composer within the project folder to set up Composer and the necessary files.

```bash
composer init
```

After initializing Composer, your composer.json file should resemble the following:

```json
{
    "name": "shuqroh/celo-web3-php",
    "autoload": {
        "psr-4": {
            "Shuqroh\\CeloWeb3Php\\": "src/"
        }
    },
    "authors": [
        {
            "name": "Shuqroh",
            "email": "shukurahganiyuy@yahoo.com"
        }
    ],
    "require": {}
}
```

Update the composer file by changing/add the minimum stability to dev:

```bash
"minimum-stability": "dev"
```

Install web3-php through the terminal:

```bash
composer require web3p/web3.php dev-master
```

or add it manually by adding ```"web3p/web3.php": "dev-master"``` to require in composer.json:

```json
{
    "name": "shuqroh/celo-web3-php",
    "autoload": {
        "psr-4": {
            "Shuqroh\\CeloWeb3Php\\": "src/"
        }
    },
    "minimum-stability": "dev",
    "authors": [
        {
            "name": "Shuqroh",
            "email": "shukurahganiyuy@yahoo.com"
        }
    ],
    "require": {
        "web3p/web3.php": "dev-master"
    }
}
```
Then run ```composer install``` in your terminal, to confirm if the installation is complete open the vendor folder and look for ```web3p/web3.php```If found then the installation is complete.

### Code implementation:

Open ```src``` folder and create file named ```CeloWeb3Php.php```. Import web3 with the keyword ```use Web3\Web3;```

In order to manage resources like smart contract abi JSON, create a folder at the root of our project named ```resources``` and you can create as many JSON files for contract ABI.

Also let's add package like ```web3p/ethereum-tx multiplechain/utils multiplechain/evm-based-chains```, copy the command below and excute it in your terminal

```bash
composer require web3p/ethereum-tx  multiplechain/utils multiplechain/evm-based-chains
```

Let's make use of abstraction and the likes to manage each of our Dapp functionality using OOP.

Create a file named ```Transaction.php```, this file will contain everything we need to transfer tokens from one address to another.

```php
<?php

namespace Shuqroh\CeloWeb3Php;

use Exception;
use Web3\Validators\AddressValidator;
use Web3\Validators\BlockHashValidator;
use Web3\Validators\TransactionValidator;

final class Transaction
{
    /**
     * Provider
     * @var Provider
     */
    private $provider;

    /**
     * Transaction hash
     * @var string
     */
    private $hash;

    /**
     * Transaction data
     * @var object
     */
    private $data;

    /**
     * @param string $txHash
     * @param Provider|null $provider
     * @throws Exception
     */
    public function __construct(string $txHash, Provider $provider)
    {
        if (BlockHashValidator::validate($txHash) === false) {
            throw new \Exception('Invalid transaction id!', 24000);
        }

        $this->hash = $txHash;
        $this->provider = $provider;

        try {
            $this->getData();
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return object|null
     */
    public function getData(): ?object
    {
        $this->provider->methods->getTransactionByHash($this->hash, function ($err, $tx) {
            if ($err) {
                throw new \Exception($err->getMessage(), $err->getCode());
            } else {
                if (TransactionValidator::validate((array)$tx) === false) {
                    throw new \Exception('Invalid transaction data!', 25000);
                } else {
                    $this->data = $tx;
                }
            }
        });

        $this->provider->methods->getTransactionReceipt($this->hash, function ($err, $tx) {
            if ($err) {
                throw new \Exception($err->getMessage(), $err->getCode());
            } else {
                $this->data->status = isset($tx->status) ? $tx->status : null;
                $this->data->gasUsed = isset($tx->gasUsed) ? $tx->gasUsed : null;
            }
        });

        return $this->data;
    }

    /**
     * @return object|null
     */
    public function decodeInput(): ?object
    {
        $input = $this->data->input;
        $pattern = '/.+?(?=000000000000000000000000)/';
        preg_match($pattern, $input, $matches, PREG_OFFSET_CAPTURE, 0);
        $method = $matches[0][0];

        if ($input != '0x') {
            $input = str_replace($method, '', $input);
            $receiver = '0x' . substr(substr($input, 0, 64), 24);
            $amount = '0x' . ltrim(substr($input, 64), 0);
            return (object) compact('receiver', 'amount');
        } else {
            return null;
        }
    }

    /** 
     * @return int
     */
    public function getConfirmations(): int
    {
        try {
            $currentBlock = $this->provider->getBlockNumber();
            if ($this->data->blockNumber === null) return 0;

            if (is_string($this->data->blockNumber)) {
                $this->data->blockNumber = Utils::toDec($this->data->blockNumber, 0);
            }

            $confirmations = $currentBlock - $this->data->blockNumber;
            return $confirmations < 0 ? 0 : $confirmations;
        } catch (Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return ?bool
     */
    public function getStatus(): ?bool
    {
        $result = null;

        if ($this->data == null) {
            $result = false;
        } else {
            if ($this->data->blockNumber !== null) {
                if ($this->data->status == '0x0') {
                    $result = false;
                } else {
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $result =  $this->getStatus();

        if (is_bool($result)) {
            return $result;
        } else {
            return $this->validate();
        }
    }

    /**
     * @param string address 
     * @return bool
     */
    public function verifyTokenTransfer(string $address): bool
    {
        if (AddressValidator::validate($address = strtolower($address)) === false) {
            throw new Exception('Invalid token address!');
        }

        if ($this->validate()) {
            if ($this->data->input == '0x') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function verifyCoinTransfer(): bool
    {
        if ($this->validate()) {
            if ($this->data->value == '0x0') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * @param string receiver 
     * @param int amount 
     * @param string address 
     * @return bool
     */
    public function verifyTokenTransferWithData(string $receiver, float $amount, string $address): bool
    {
        if (AddressValidator::validate($receiver = strtolower($receiver)) === false) {
            throw new Exception('Invalid receiver address!');
        }

        if ($this->verifyTokenTransfer($address)) {
            $decodedInput = $this->decodeInput();
            $token = new Token($address, [], $this->provider);

            $data = (object) [
                "receiver" => strtolower($decodedInput->receiver),
                "amount" => Utils::toDec($decodedInput->amount, ($token->getDecimals()))
            ];

            if ($data->receiver == $receiver && $data->amount == $amount) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param string receiver 
     * @param int amount 
     * @return bool
     */
    public function verifyCoinTransferWithData(string $receiver, float $amount): bool
    {
        if (AddressValidator::validate($receiver = strtolower($receiver)) === false) {
            throw new Exception('Invalid receiver address!');
        }

        if ($this->verifyCoinTransfer()) {

            $coin = new Coin($this->provider);

            $data = (object) [
                "receiver" => strtolower($this->data->to),
                "amount" => Utils::toDec($this->data->value, ($coin->getDecimals()))
            ];

            if ($data->receiver == $receiver && $data->amount == $amount) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param string|null tokenAddress
     * @return bool
     */
    public function verifyTransfer(?string $tokenAddress = null): bool
    {
        if (!$tokenAddress) {
            return $this->verifyCoinTransfer();
        } else {
            return $this->verifyTokenTransfer($tokenAddress);
        }
    }

    /**
     * @param object $config
     * @return bool
     */
    public function verifyTransferWithData(object $config): bool
    {
        if (isset($config->tokenAddress) && !is_null($config->tokenAddress)) {
            return $this->verifyTokenTransferWithData($config->receiver, $config->amount, $config->tokenAddress);
        } else {
            return $this->verifyCoinTransferWithData($config->receiver, $config->amount);
        }
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return rtrim($this->provider->getNetwork()->explorerUrl, '/') . '/tx/' . $this->hash;
    }
}
```

* Manage utils ```Utils.php```

```php
<?php

namespace Shuqroh\CeloWeb3Php;

use Web3\Validators\AddressValidator;
use MultipleChain\Utils as AbstractUtils;

final class Utils extends AbstractUtils
{
    /**
     * Validate parameters
     *
     * @param string $from
     * @param string $to
     * @param float $amount
     * @param string|null $tokenAddress
     * @return void
     * @throws Exception
     */
    public static function validate(string $from, string $to, float $amount, ?string $tokenAddress = null): void
    {
        if ($amount <= 0) {
            throw new \Exception("The amount cannot be zero or less than zero!", 20000);
        }

        if (AddressValidator::validate($from) === false) {
            throw new \Exception('Invalid sender address!', 21000);
        }

        if (AddressValidator::validate($to) === false) {
            throw new \Exception('Invalid receiver address!', 22000);
        }

        if (!is_null($tokenAddress) && AddressValidator::validate($tokenAddress) === false) {
            throw new \Exception('Invalid token address!', 23000);
        }
    }
}
```
**Note**: The code  provided above is a part of the `Utils.php` file in the `Shuqroh\CeloWeb3Php` namespace. It defines a class called `Utils` , which extends another class called `AbstractUtils` from the `MultipleChain` namespace. Let's break down the code and understand its functionality:

* The `namespace` statement declares the namespace in which the `Utils` class resides, `Shuqroh\CeloWeb3Php`. Namespaces are used to organize code and prevent naming conflicts.
* The `use` statements import the necessary classes for use within the `Utils` class. In this case, it imports the `AddressValidator` class from the `Web3\Validators` namespace and the `AbstractUtils` class from the `MultipleChain` namespace.
* The `final` keyword before the class declaration means that the class cannot be subclassed or extended further.
* The `Utils` class extends the `AbstractUtils` class. By extending `AbstractUtils`, the `Utils` class inherits all the properties and methods defined in `AbstractUtils`.
* The `validate` method is a static method defined in the `Utils` class. It takes four parameters: `$from`, `$to`, `$amount`, and `$tokenAddress`. The types of these parameters are specified in the method signature.
* The method is responsible for validating the parameters passed to it. If any of the validations fail, an exception is thrown.
* The first validation checks if the `$amount` is greater than zero. If it is zero or less, an `\Exception` is thrown with a custom message and error code.
* The next two validations use the `AddressValidator::validate()` method from the `Web3\Validators` namespace to check the validity of the `$from` and `$to` addresses. If any of these addresses are invalid, an `\Exception` is thrown with specific error codes.
* The final validation checks if the `$tokenAddress` is not `null` and if it is provided, it also validates the address. If the address is invalid, an `\Exception` is thrown with a specific error code.

That's the explanation of the code provided. The `Utils` class extends `AbstractUtils` and provides a static `validate` method for validating various parameters.

* Manage Token ```Token.php```

```php
<?php

namespace Shuqroh\CeloWeb3Php;

use Web3p\EthereumTx\Transaction;
use Web3\Validators\AddressValidator;
use phpseclib\Math\BigInteger as BigNumber;

final class Token
{
    /**
     * Provider
     * @var Provider
     */
    private $provider;

    /**
     * Current token contract address
     * @var string
     */
    private $address;

    /**
     * Current token contract
     * @var Web3Contract
     */
    public $contract;

    /**
     * @param string $contractAddress
     * @param array $abi
     * @param Provider|null $provider
     */
    public function __construct(string $contractAddress, array $abi = [], Provider $provider)
    {
        if (AddressValidator::validate($contractAddress) === false) {
            throw new \Exception('Invalid token address!', 23000);
        }

        $this->provider = $provider;
        $this->address = $contractAddress;
        $abi = empty($abi) ? file_get_contents(dirname(__DIR__) . '/resources/erc20.json') : $abi;
        $this->contract = (new Contract($contractAddress, json_decode($abi), $provider));
    }

    /**
     * Generates a token transfer data
     *
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return array
     * @throws Exception
     */
    public function transferData(string $from, string $to, float $amount): array
    {
        if ($this->getBalance($from) < $amount) {
            throw new \Exception('Insufficient balance!', 10000);
        }

        $hexAmount = Utils::toHex($amount, $this->getDecimals());

        $data = $this->contract->getData('transfer', $to, $hexAmount);
        $gas = $this->contract->getEstimateGas('transfer', $to, $hexAmount, ['from' => $from]);

        return [
            'from' => $from,
            'value' => '0x0',
            'to' => $this->address,
            'chainId' => $this->provider->getChainId(),
            'nonce' => $this->provider->getNonce($from),
            'gasPrice' => $this->provider->getGasPrice(),
            'gas' => $gas,
            'data' => $data,
        ];
    }

    /**
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return Transaction
     */
    public function transfer(string $from, string $to, float $amount): Transaction
    {
        Utils::validate($from, $to, $amount, $this->address);

        return new Transaction($this->transferData($from, $to, $amount));
    }

    /**
     * Returns the token's decimals
     * @return int
     * @throws Exception
     */
    public function getDecimals(): int
    {
        $result = $this->contract->decimals();

        if (is_array($result) && $result[0] instanceof BigNumber) {
            return intval($result[0]->toString());
        } else {
            throw new \Exception("There was a problem retrieving the decimals value!", 12000);
        }
    }

    /**
     * Returns the balance of the current token in the address given wallet address
     *
     * @param string $address
     * @return float
     * @throws Exception
     */
    public function getBalance(string $address): float
    {
        $result = $this->contract->balanceOf($address);

        if (is_array($result) && $result['balance'] instanceof BigNumber) {
            return Utils::toDec($result['balance']->toString(), $this->getDecimals());
        } else {
            throw new \Exception("There was a problem retrieving the balance!", 11000);
        }
    }

    /**
     * get token name
     *
     * @return string|null
     * @throws Exception
     */
    public function getName(): ?string
    {
        return isset($this->contract->name()[0]) ? $this->contract->name()[0] : null;
    }

    /**
     * get token symbol
     *
     * @return string|null
     * @throws Exception
     */
    public function getSymbol(): ?string
    {
        return isset($this->contract->symbol()[0]) ? $this->contract->symbol()[0] : null;
    }

    /**
     * get token total supply
     *
     * @return string
     * @throws Exception
     */
    public function getTotalSupply(): string
    {
        $totalSupply = $this->contract->totalSupply();

        if (is_array($totalSupply) && end($totalSupply) instanceof BigNumber) {
            $totalSupply = Utils::toDec(
                end($totalSupply)->toString(),
                $this->getDecimals()
            );
            return rtrim(number_format($totalSupply, $this->getDecimals(), ',', '.'), 0);
        } else {
            throw new \Exception("There was a problem retrieving the total suppy!", 14001);
        }
    }

    /**
     * Returns the current token contract address
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return $this->contract->$name(...$args);
    }
}
```
**Note**: The code provided above is for the `Token` class in the `Shuqroh\CeloWeb3Php` namespace. Let's break down the code and understand its functionality:

* The code declares a class called `Token` within the `Shuqroh\CeloWeb3Php` namespace.
* The class is marked as `final`, which means it cannot be subclassed or extended further.
* The class has several properties and methods that we'll examine in detail.

Let's take a look at the individual methods and their functionality:

1. `__construct(string $contractAddress, array $abi = [], Provider $provider)`

* This is the constructor method of the `Token` class.
* It takes three parameters: `$contractAddress`, `$abi`, and `$provider`.
* It checks the validity of the `$contractAddress` using the `AddressValidator::validate()` method. If the address is invalid, it throws an exception with an error code (`23000`).
* It assigns the `$provider` parameter to the `private $provider` property.
* It assigns the `$contractAddress` parameter to the `private $address` property.
* If the `$abi` parameter is empty, it reads the contents of the `erc20.json` file and assigns it to the `$abi` variable.
* It creates a new `Contract` instance by passing the `$contractAddress`, decoded `$abi`, and `$provider` to the constructor.
* The created `Contract` instance is assigned to the `public $contract` property of the `Token` class.

2. `transferData(string $from, string $to, float $amount): array`

* This method generates token transfer data.
* It takes three parameters: `$from`, `$to`, and `$amount`.
* It checks if the balance of the `$from` address is sufficient for the transfer by calling the `getBalance($from)` method. If the balance is insufficient, it throws an exception with an error code (`10000`).
* It converts the `$amount` to its hexadecimal representation using the `Utils::toHex()` method, based on the token's decimals.
* It uses the `getData()` method of the `Contract` instance to generate the transfer data, passing `$to` and `$hexAmount` as arguments.
* It estimates the gas required for the transfer using the `getEstimateGas()` method of the `Contract` instance.
* It returns an array with the transfer details, including the `from` address, `value`, `to` address (token contract address), `chainId`, `nonce`, `gasPrice`, `gas`, and `data`.

3. `transfer(string $from, string $to, float $amount): Transaction`

* This method initiates a token transfer.
* It takes three parameters: `$from`, `$to`, and `$amount`.
* It calls the `Utils::validate()` method to validate the `$from`, `$to`, and `$amount` parameters, as well as the token contract address (`$this->address`).
* It calls the `transferData()` method to get the transfer data.
* It creates and returns a new `Transaction` instance using the transfer data.

4. `getDecimals(): int`

* This method returns the decimals value of the token.
* It calls the `decimals()` method of the `Contract` instance to retrieve the decimals value.
* If the result is an array and the first element is an instance of `BigNumber`, it converts and returns the decimals value as an integer.
* If there is a problem retrieving the decimals value, it throws an exception with an error code (`12000`).

5. `getBalance(string $address): float`

* This method returns the balance of the token for a given wallet address.
* It takes one parameter: `$address`.
* It calls the `balanceOf($address)` method of the `Contract` instance to retrieve the balance of the given address.
* If the result is an array and the `balance` key holds an instance of `BigNumber`, it converts and returns the balance value as a float, using the `Utils::toDec()` method.
* If there is a problem retrieving the balance, it throws an exception with an error code (`11000`).

6. `getName(): ?string`

* This method returns the name of the token.
* It calls the `name()` method of the `Contract` instance.
* If the first element of the returned array is set, it returns the name as a string.
* Otherwise, it returns `null`.

7. `getSymbol(): ?string`

* This method returns the symbol of the token.
* It calls the `symbol()` method of the `Contract` instance.
* If the first element of the returned array is set, it returns the symbol as a string.
* Otherwise, it returns `null`.

8. `getTotalSupply(): string`

* This method returns the total supply of the token.
* It calls the `totalSupply()` method of the `Contract` instance.
* If the result is an array and the last element is an instance of `BigNumber`, it converts and returns the total supply value as a formatted string.
* If there is a problem retrieving the total supply, it throws an exception with an error code (`14001`).

9. `getAddress(): string`

* This method returns the current token contract address.
* It simply returns the value of the `private $address` property.

10. `__call(string $name, array $args)`

* This is a magic method that allows invoking contract methods dynamically.
* It passes the `$name` and `$args` parameters to the corresponding method of the `Contract` instance and returns the result.

That's the breakdown of the `Token` class. It provides various methods to interact with a token contract, such as transferring tokens, retrieving balances, getting token information, and more.

* Manage provider ```Provider.php```

```php
<?php

namespace Shuqroh\CeloWeb3Php;

use Web3\Web3;
use Web3\Eth;
use Exception;
use Web3\Providers\HttpProvider;
use MultipleChain\EvmBasedChains;
use Web3\RequestManagers\HttpRequestManager;
use phpseclib\Math\BigInteger as BigNumber;
use Web3p\EthereumTx\Transaction as PendingTransaction;

final class Provider
{

    /**
     * Current time
     * @var int
     */
    private $time;

    /**
     * The connected blockchain network
     * @var object
     */
    private $network;

    /**
     * Current blockchain gas price
     * @var int
     */
    private $defaultGasPrice = 10000000000;

    /**
     * Current blockchain transfer nonce
     * @var int
     */
    private $defaultNonce = 1;

    /**
     * Web3 instance
     * @var Web3
     */
    public $web3;

    /**
     * Eth instance / RPC Api methods
     * @var Eth
     */
    public $methods;

    /**
     * @var PendingTransaction|null
     */
    private $pendingTransaction = null;

    /**
     * Exception codes
     * @var array
     */
    public static $codes = [
        10000 => 'Insufficient balance!',
        11000 => 'There was a problem retrieving the balance',
        12000 => 'There was a problem retrieving the decimals value',
        13000 => 'There was a problem retrieving the transaction id',
        14000 => 'There was a problem retrieving the chain id',
        14001 => 'There was a problem retrieving the total supply',
        15000 => 'Before you can use the signing process, you must create a pending transaction.',
        16000 => 'Transaction time out!',
        18000 => 'It should contain native currency, symbol and decimals values',
        20000 => 'The amount cannot be zero or less than zero!',
        21000 => 'Invalid sender address!',
        22000 => 'Invalid receiver address!',
        23000 => 'Invalid token address!',
        24000 => 'Invalid transaction id!',
        25000 => 'Invalid transaction data!',
        26000 => 'Transaction failed!'
    ];

    /**
     * @param string|array $network
     * @param boolean|null $testnet
     * @param integer $timeOut
     */
    public function __construct($network, bool $testnet = null, int $timeOut = 5)
    {
        $networks = $testnet ? EvmBasedChains::$testnets : EvmBasedChains::$mainnets;

        if (is_object($network)) {
            $this->network = $network;
        } elseif (is_array($network)) {
            $this->network = (object) $network;
        } else if (isset($networks[$network])) {
            $this->network = (object) $networks[$network];
        } else {
            throw new Exception('Network not found!');
        }

        if (!is_object($this->network->nativeCurrency)) {
            $this->network->nativeCurrency = (object) $this->network->nativeCurrency;
        }

        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager($this->network->rpcUrl, $timeOut)));
        $this->methods = $this->web3->eth;

        $this->time = time();
    }

    /**
     * @return object
     */
    public function getNetwork(): object
    {
        return $this->network;
    }

    /**
     * Start transfer process
     * @param string $from
     * @param string $to
     * @param float $amount
     * @param string|null $tokenAddress
     * @return Provider
     * @throws Exception
     */
    public function transfer(string $from, string $to, float $amount, ?string $tokenAddress = null): Provider
    {
        if (is_null($tokenAddress)) {
            return $this->coinTransfer($from, $to, $amount);
        } else {
            return $this->tokenTransfer($from, $to, $amount, $tokenAddress);
        }
    }

    /**
     * Start token transfer process
     * @param string $from
     * @param string $to
     * @param float $amount
     * @param string $tokenAddress
     * @return Provider
     * @throws Exception
     */
    public function tokenTransfer(string $from, string $to, float $amount, string $tokenAddress): Provider
    {
        $this->validate($from, $to, $amount, $tokenAddress);

        $this->pendingTransaction = (new Token($tokenAddress, [], $this))->transfer($from, $to, $amount);

        return $this;
    }

    /**
     * Start coin transfer process
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return Provider
     * @throws Exception
     */
    public function coinTransfer(string $from, string $to, float $amount): Provider
    {
        $this->validate($from, $to, $amount);

        $this->pendingTransaction = (new Coin($this))->transfer($from, $to, $amount);

        return $this;
    }

    /**
     * @param string $privateKey
     * @param PendingTransaction|null $pendingTransaction
     * @return string
     * @throws Exception
     */
    public function sign(string $privateKey, ?PendingTransaction $pendingTransaction = null): string
    {
        if ($pendingTransaction instanceof PendingTransaction) {
            $this->pendingTransaction = $pendingTransaction;
        }

        if ($this->pendingTransaction instanceof PendingTransaction) {
            return $this->pendingTransaction->sign($privateKey);
        } else {
            throw new \Exception("Before you can use the signing process, you must create a pending transaction.", 15000);
        }
    }

    /**
     * @param string $privateKey
     * @param PendingTransaction|null $pendingTransaction
     * @return Transaction
     * @throws Exception
     */
    public function signAndSend(string $privateKey, ?PendingTransaction $pendingTransaction = null): Transaction
    {
        return $this->sendSignedTransaction($this->sign($privateKey, $pendingTransaction));
    }

    /**
     * Runs the signed transaction
     * @param string $signedTransaction
     * @return Transaction
     * @throws Exception
     */
    public function sendSignedTransaction(string $signedTransaction): Transaction
    {
        try {
            $transactionId = null;
            $this->methods->sendRawTransaction('0x' . $signedTransaction, function ($err, $tx) use (&$transactionId) {
                if ($err) {
                    throw new \Exception($err->getMessage(), $err->getCode());
                } else {
                    $transactionId = $tx;
                }
            });
        } catch (\Exception $e) {
            if ((time() - $this->time) >= 15) {
                throw new \Exception("Transaction time out!", 16000);
            } else {
                if ($e->getCode() == -32000 && $e->getMessage() != 'invalid sender') {
                    return $this->sendSignedTransaction($signedTransaction);
                }
            }
        }

        if (is_string($transactionId)) {
            return $this->createTransactionInstance($transactionId);
        } else {
            throw new \Exception("There was a problem retrieving the transaction id!", 13000);
        }
    }


    /**
     * @param string $transactionId
     * @return Transaction
     * @throws Exception
     */
    public function createTransactionInstance(string $transactionId): Transaction
    {
        try {
            return new Transaction($transactionId, $this);
        } catch (\Exception $e) {
            if ((time() - $this->time) >= 15) {
                throw new \Exception("Transaction failed.", 26000);
            } else {
                if ($e->getCode() == 0 || $e->getCode() == 25000) {
                    return $this->createTransactionInstance($transactionId);
                }
            }
        }
    }

    /**
     * Gets the chain id of the blockchain network given the RPC url address
     * @return int
     * @throws Exception
     */
    public function getChainId(): int
    {
        $chainId = null;
        $this->web3->net->version(function ($err, $res) use (&$chainId) {
            if ($err) {
                throw new \Exception($err->getMessage(), $err->getCode());
            } else {
                $chainId = $res;
            }
        });


        if (is_string($chainId)) {
            return intval($chainId);
        } else {
            throw new \Exception("There was a problem retrieving the chain id!", 14000);
        }
    }

    /**
     * get block number
     * @return int
     * @throws Exception
     */
    public function getBlockNumber(): int
    {
        $number = null;
        $this->methods->blockNumber(function ($err, $res) use (&$number) {
            if ($err) {
                throw new \Exception($err->getMessage(), $err->getCode());
            } else {
                $number = $res;
            }
        });

        if (is_object($number) && $number instanceof BigNumber) {
            return intval($number->toString());
        } else {
            throw new \Exception("There was a problem retrieving the chain id!", 14000);
        }
    }

    /**
     * It receives the gas fee required for the transactions
     * @return string
     * @throws Exception
     */
    public function getGasPrice(): string
    {
        $result = null;
        $this->methods->gasPrice(function ($err, $res) use (&$result) {
            if ($err) {
                throw new \Exception($err->getMessage(), $err->getCode());
            } else {
                $result = $res;
            }
        });

        if ($result instanceof BigNumber) {
            return Utils::hex($result->toString());
        } else {
            return Utils::hex($this->defaultGasPrice);
        }
    }

    /**
     * Get transfer nonce
     * @param string $from
     * @return string
     * @throws Exception
     */
    public function getNonce(string $from): string
    {
        $result = null;
        $this->methods->getTransactionCount($from, 'pending', function ($err, $res) use (&$result) {
            if ($err) {
                throw new \Exception($err->getMessage(), $err->getCode());
            } else {
                $result = $res;
            }
        });

        if ($result instanceof BigNumber) {
            return Utils::hex($result->toString());
        } else {
            return Utils::hex($this->defaultNonce);
        }
    }

    /**
     * @return object
     */
    public function getCurrency(): object
    {
        return $this->network->nativeCurrency;
    }

    /**
     * @param string $hash
     * @return Coin
     */
    public function Coin(): Coin
    {
        return new Coin($this);
    }

    /**
     * @param string $address
     * @return Token
     */
    public function Token(string $address, array $abi = []): Token
    {
        return new Token($address, $abi, $this);
    }

    /**
     * @param string $hash
     * @return Transaction
     */
    public function Transaction(string $hash): Transaction
    {
        return new Transaction($hash, $this);
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return $this->methods->$name(...$args);
    }
}
```
**Note**:  The code provided above is a PHP class named `Provider` within the namespace `Shuqroh\CeloWeb3Php`. Here's a breakdown of the class and its methods:

1. The class has several properties such as `$time`, `$network`, `$defaultGasPrice`, `$defaultNonce`, `$web3`, `$methods`, and `$pendingTransaction`. These properties are used to store various data and instances.

2. The constructor method `__construct()` is used to initialize the class. It accepts parameters `$network`, `$testnet`, and `$timeOut` to configure the blockchain network. It sets up the Web3 instance using the provided RPC URL.

3. The class has getter methods `getNetwork()`, `getCurrency()`, and `getBlockNumber()` that return information about the connected blockchain network, native currency, and the current block number, respectively.

4. The `transfer()` method is used to initiate a transfer process. It accepts parameters such as `$from`, `$to`, `$amount`, and an optional `$tokenAddress` to specify whether it's a coin transfer or a token transfer.

5. The methods `tokenTransfer()` and `coinTransfer()` handle token transfers and coin transfers, respectively. They validate the input parameters and create a pending transaction object using the `Token` or `Coin` class.

6. The `sign()` method is used to sign a transaction using a private key. It accepts the private key as a parameter and returns the signed transaction as a string.

7. The `signAndSend()` method signs a transaction and sends it to the network. It accepts a private key and an optional pending transaction object. It internally calls the `sign()` method and then invokes `sendSignedTransaction()`.

8. The `sendSignedTransaction()` method sends a signed transaction to the network. It takes the signed transaction as a parameter and returns a `Transaction` instance.

9. The `createTransactionInstance()` method creates a `Transaction` instance given a transaction ID. It handles exceptions and retries the operation if necessary.

10. The `getChainId()` method retrieves the chain ID of the connected blockchain network.

11. The methods `getGasPrice()` and `getNonce()` retrieve the gas price and transfer nonce, respectively, from the network. They handle exceptions and provide default values if necessary.

12. The methods `Coin()`, `Token()`, and `Transaction()` create instances of the `Coin`, `Token`, and `Transaction` classes, respectively.

13. The magic method `__call()` allows invoking Ethereum RPC API methods using dynamic method calls on the `Provider` instance.

Additionally, the class has a static property `$codes` that defines exception codes and their corresponding error messages.

This class is part of a larger package/library for interacting with the Celo blockchain using PHP.

* Manage contract ```Contract.php```

```php
<?php

namespace Shuqroh\CeloWeb3Php;

use Web3\Contract as Web3Contract;
use Web3\Validators\AddressValidator;
use phpseclib\Math\BigInteger as BigNumber;

final class Contract
{
    /**
     * Provider
     * @var Provider
     */
    private $provider;

    /**
     * Current token contract address
     * @var string
     */
    private $address;

    /**
     * web3 contract
     * @var Web3Contract
     */
    public $contract;

    /**
     * Default gas
     * @var int
     */
    private $defaultGas = 50000;

    /**
     * @param string $address
     * @param array $abi
     * @param Provider|null $provider
     */
    public function __construct(string $address, array $abi, Provider $provider)
    {
        if (AddressValidator::validate($address) === false) {
            throw new \Exception('Invalid contract address!', 23000);
        }

        $this->address = $address;
        $this->provider = $provider;
        $this->contract = (new Web3Contract($this->provider->web3->provider, json_encode($abi)))->at($address);
    }

    /**
     * @param string $method
     * @param array $params
     * @return string|null
     * @throws Exception
     */
    public function getEstimateGas(string $method, ...$params): ?string
    {
        $gas = null;
        call_user_func_array([$this->contract, 'estimateGas'], [$method, ...$params, function ($err, $res) use (&$gas) {
            if ($err) {
                throw new \Exception($err->getMessage(), $err->getCode());
            } else {
                $gas = $res;
            }
        }]);

        if ($gas instanceof BigNumber) {
            return Utils::hex($gas->toString());
        } else {
            return Utils::hex($this->defaultGas);
        }

        return $gas;
    }

    /**
     * @param string $method
     * @param array $params
     * @return string|null
     * @throws Exception
     */
    public function getData(string $method, ...$params): ?string
    {
        return '0x' . $this->contract->getData($method, ...$params);
    }

    /**
     * Returns the current token contract address
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call(string $method, array $params)
    {
        $result = null;
        call_user_func_array([$this->contract, 'call'], [$method, ...$params, function ($err, $res) use (&$result) {
            if ($err) {
                throw new \Exception($err->getMessage(), $err->getCode());
            } else {
                $result = $res;
            }
        }]);

        return $result;
    }
}
```
**Note**: The code provided above is a PHP class named `Contract` within the namespace `Shuqroh\CeloWeb3Php`. Let's go through the code and explain each part:

```php
<?php

namespace Shuqroh\CeloWeb3Php;

use Web3\Contract as Web3Contract;
use Web3\Validators\AddressValidator;
use phpseclib\Math\BigInteger as BigNumber;

final class Contract
{
    // ...
```

The code begins with the PHP opening tag `<?php` followed by the `namespace` declaration. It imports some required classes using the `use` statement.

The `Contract` class is defined as `final`, which means it cannot be extended by other classes. This class represents a contract on the Celo blockchain and provides methods to interact with the contract.

```php
    /**
     * Provider
     * @var Provider
     */
    private $provider;
```

This private property holds an instance of the `Provider` class. It is used to connect to the Celo network and access the web3 provider.

```php
    /**
     * Current token contract address
     * @var string
     */
    private $address;
```

This private property stores the address of the current token contract.

```php
    /**
     * web3 contract
     * @var Web3Contract
     */
    public $contract;
```

This public property holds an instance of the `Web3Contract` class. It represents the contract on the Celo network and provides methods to interact with it.

```php
    /**
     * Default gas
     * @var int
     */
    private $defaultGas = 50000;
```

This private property holds the default gas value used in contract interactions if the gas value is not explicitly specified.

```php
    /**
     * @param string $address
     * @param array $abi
     * @param Provider|null $provider
     */
    public function __construct(string $address, array $abi, Provider $provider)
    {
        // ...
    }
```

This is the constructor method of the `Contract` class. It is called when creating a new instance of the class. It takes the contract address, ABI (Application Binary Interface), and a `Provider` object as parameters. The constructor validates the contract address using the `AddressValidator` class and throws an exception if the address is invalid. It initializes the `address`, `provider`, and `contract` properties accordingly.

```php
    /**
     * @param string $method
     * @param array $params
     * @return string|null
     * @throws Exception
     */
    public function getEstimateGas(string $method, ...$params): ?string
    {
        // ...
    }
```

This method, `getEstimateGas()`, calculates the estimated gas required to execute a contract method. It takes the method name as a string and variable-length parameter list for the method arguments. It uses the `estimateGas()` method of the contract object to get the gas estimation. The gas estimation is retrieved asynchronously using a callback function. If an error occurs during estimation, an exception is thrown. The method then converts the gas value to a hexadecimal string using the `Utils::hex()` method and returns it. If the gas value is not returned as a `BigNumber`, it uses the default gas value defined in the class.

```php
    /**
     * @param string $method
     * @param array $params
     * @return string|null
     * @throws Exception
     */
    public function getData(string $method, ...$params): ?string
    {
        // ...
    }
```

This method

, `getData()`, generates the encoded data for a contract method call. It takes the method name as a string and variable-length parameter list for the method arguments. It uses the `getData()` method of the contract object to generate the data. The generated data is prefixed with '0x' and returned as a string.

```php
    /**
     * Returns the current token contract address
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }
```

This method, `getAddress()`, simply returns the current contract address.

```php
    /**
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call(string $method, array $params)
    {
        // ...
    }
```

This magic method, `__call()`, allows dynamic invocation of contract methods. It is called when a method is called on the `Contract` object that is not explicitly defined in the class. It uses the `call()` method of the contract object to invoke the requested method asynchronously with the provided parameters. The result is retrieved asynchronously using a callback function. If an error occurs during the method invocation, an exception is thrown. The result is then returned.

That's an overview of the provided code. It represents a PHP class that wraps a Celo contract and provides methods for estimating gas, generating data, retrieving the contract address, and dynamically invoking contract methods.

* Manage coin ```Coin.php```

```php
<?php

namespace Shuqroh\CeloWeb3Php;

use Web3p\EthereumTx\Transaction;
use phpseclib\Math\BigInteger as BigNumber;

final class Coin
{
    /**
     * Provider
     * @var Provider
     */
    private $provider;

    /**
     * @param Provider|null $provider
     */
    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Generates a coin transfer data
     *
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return array
     * @throws Exception
     */
    public function transferData(string $from, string $to, float $amount): array
    {
        if ($this->getBalance($from) < $amount) {
            throw new \Exception('Insufficient balance!', 10000);
        }

        return [
            'to' => $to,
            'data' => '',
            'from' => $from,
            'gas' => Utils::hex(21000),
            'chainId' => $this->provider->getChainId(),
            'nonce' => $this->provider->getNonce($from),
            'gasPrice' => $this->provider->getGasPrice(),
            'value' => Utils::toHex($amount, $this->getDecimals()),
        ];
    }

    /**
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return Transaction
     */
    public function transfer(string $from, string $to, float $amount): Transaction
    {
        Utils::validate($from, $to, $amount);

        return new Transaction($this->transferData($from, $to, $amount));
    }

    /**
     * Returns the coin decimals
     * @return int
     */
    public function getDecimals(): int
    {
        return $this->provider->getCurrency()->decimals;
    }

    /**
     * Returns the coin symbol
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->provider->getCurrency()->symbol;
    }

    /**
     * Returns the balance of the current token in the address given wallet address
     * @param string $address
     * @return float
     * @throws Exception
     */
    public function getBalance(string $address): float
    {
        $result = null;
        $this->provider->methods->getBalance($address, function ($err, $res) use (&$result) {
            if ($err) {
                throw new \Exception($err->getMessage(), $err->getCode());
            } else {
                $result = $res;
            }
        });

        if ($result instanceof BigNumber) {
            return Utils::toDec($result->toString(), $this->getDecimals());
        } else {
            throw new \Exception("There was a problem retrieving the balance!", 11000);
        }
    }
}
```
**Note**: The code provided above is a PHP class named `Coin` within the namespace `Shuqroh\CeloWeb3Php`. Let's go through the code and explain each part:

```php
<?php

namespace Shuqroh\CeloWeb3Php;

use Web3p\EthereumTx\Transaction;
use phpseclib\Math\BigInteger as BigNumber;

final class Coin
{
    // ...
```

The code begins with the PHP opening tag `<?php` followed by the `namespace` declaration. It imports some required classes using the `use` statement.

The `Coin` class is defined as `final`, which means it cannot be extended by other classes. This class represents a coin (token) on the Celo blockchain and provides methods to interact with the coin, such as transferring coins and retrieving balance.

```php
    /**
     * Provider
     * @var Provider
     */
    private $provider;
```

This private property holds an instance of the `Provider` class. It is used to connect to the Celo network and access the web3 provider.

```php
    /**
     * @param Provider|null $provider
     */
    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }
```

This is the constructor method of the `Coin` class. It takes a `Provider` object as a parameter and assigns it to the `provider` property.

```php
    /**
     * Generates a coin transfer data
     *
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return array
     * @throws Exception
     */
    public function transferData(string $from, string $to, float $amount): array
    {
        // ...
    }
```

This method, `transferData()`, generates the data required for a coin transfer. It takes the sender's address (`$from`), recipient's address (`$to`), and the amount of coins to transfer (`$amount`) as parameters. It first checks if the sender has sufficient balance using the `getBalance()` method. If the balance is insufficient, it throws an exception. It then returns an array containing transfer-related data, such as the recipient's address, data (empty for a simple coin transfer), sender's address, gas limit, chain ID, nonce, gas price, and the value of the coins to transfer.

```php
    /**
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return Transaction
     */
    public function transfer(string $from, string $to, float $amount): Transaction
    {
        // ...
    }
```

This method, `transfer()`, initiates a coin transfer. It takes the sender's address (`$from`), recipient's address (`$to`), and the amount of coins to transfer (`$amount`) as parameters. It first validates the addresses and amount using the `validate()` method from the `Utils` class. It then calls the `transferData()` method to generate the transfer data and passes it to the `Transaction` constructor to create a new `Transaction` object representing the coin transfer. The created `Transaction` object is returned.

```php
    /**
     * Returns the coin decimals
     * @return int
     */
    public function getDecimals(): int
    {
        return $this->provider->getCurrency()->decimals;
    }
```

This method, `getDecimals()`, returns the number of decimal places for the coin.

```php
    /**
     * Returns the coin symbol
     * @return string


     */
    public function getSymbol(): string
    {
        return $this->provider->getCurrency()->symbol;
    }
```

This method, `getSymbol()`, returns the symbol of the coin.

```php
    /**
     * Returns the balance of the current token in the address given wallet address
     * @param string $address
     * @return float
     * @throws Exception
     */
    public function getBalance(string $address): float
    {
        // ...
    }
```

This method, `getBalance()`, retrieves the balance of the current coin for a given wallet address. It takes the address as a parameter. It calls the `getBalance()` method of the provider's `methods` object to get the balance asynchronously. The balance result is retrieved using a callback function. If an error occurs during the balance retrieval, an exception is thrown. The method then converts the balance value to the appropriate decimal value using the `toDec()` method from the `Utils` class and returns it.

That's an overview of the provided code. It represents a PHP class that interacts with a coin (token) on the Celo blockchain. It provides methods to generate transfer data, initiate coin transfers, retrieve balance, and retrieve coin-related information such as decimals and symbol.

We may now attempt to communicate with Celo Blockchain after classifying all fundamental functions into distinct categories.

Let's update ```CeloWeb3Php.php```

```php
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
```

The code above explains how you can create a web3 provider instance from the OOP classes we created before and then use it to transfer tokens, connect to smart contracts etc.

## Conclusion:

Therefore, the utilization of the WEB3.php framework in conjunction with the Celo blockchain provides a powerful solution for seamless communication. WEB3.php is a popular framework that allows developers to interact with blockchain networks, and by integrating it with Celo, a blockchain platform focused on creating financial inclusion, communication can be enhanced in various ways.

By leveraging WEB3.php, developers can easily build applications that interact with the Celo blockchain, enabling seamless communication through smart contracts, decentralized applications (dApps), and other blockchain functionalities. This framework provides a wide range of features and tools, such as sending and receiving transactions, querying blockchain data and interacting with smart contracts, all of which contribute to a robust communication infrastructure.

## About the Author:

Shukurah Ganiyu is a UI/UX designer and a content writer passionate about blockchain, DeFi, NFTs and cryptocurrencies for the emerging Web3 sector. 

- [Github](https://github.com/Shuqroh)

## References:

[Source code](https://github.com/Shuqroh/celo-web3-php)

[Multichain EVM](https://github.com/MultipleChain/evm-chains-php)
