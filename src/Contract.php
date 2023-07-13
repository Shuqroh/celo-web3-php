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
     * @param Provider $provider
     */
    public function __construct(string $address, array $abi, Provider $provider)
    {
        if (!AddressValidator::validate($address)) {
            throw new \InvalidArgumentException('Invalid contract address!');
        }

        $this->address = $address;
        $this->provider = $provider;
        $this->contract = (new Web3Contract($this->provider->web3->provider, json_encode($abi)))->at($address);
    }

    /**
     * @param string $method
     * @param mixed ...$params
     * @return string|null
     * @throws \Exception
     */
    public function getEstimateGas(string $method, ...$params): ?string
    {
        $gas = null;
        $callback = function ($err, $res) use (&$gas) {
            if ($err) {
                throw new \Exception($err->getMessage(), $err->getCode());
            } else {
                $gas = $res;
            }
        };

        $this->contract->estimateGas($method, ...$params, $callback);

        if ($gas instanceof BigNumber) {
            return Utils::hex($gas->toString());
        }

        return Utils::hex($this->defaultGas);
    }

    /**
     * @param string $method
     * @param mixed ...$params
     * @return string|null
     * @throws \Exception
     */
    public function getData(string $method, ...$params): ?string
    {
        $data = $this->contract->getData($method, ...$params);
        return '0x' . $data;
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
     * @param mixed ...$params
     * @return mixed
     */
    public function __call(string $method, array $params)
    {
        $result = null;
        $callback = function ($err, $res) use (&$result) {
            if ($err) {
                throw new \Exception($err->getMessage(), $err->getCode());
            } else {
                $result = $res;
            }
        };

        $this->contract->call($method, ...$params, $callback);

        return $result;
    }
}
