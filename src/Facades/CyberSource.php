<?php

namespace Asciisd\CyberSource\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Asciisd\CyberSource\Models\Transaction authorize(array $options)
 * @method static \Asciisd\CyberSource\Models\Transaction charge(array $options)
 * @method static \Asciisd\CyberSource\Models\Transaction capture(string $transactionId, float $amount = null, array $options = [])
 * @method static \Asciisd\CyberSource\Models\Transaction void(string $transactionId, array $options = [])
 * @method static \Asciisd\CyberSource\Models\Transaction refund(string $transactionId, float $amount = null, array $options = [])
 * @method static \Asciisd\CyberSource\Models\Transaction retrieveTransaction(string $transactionId)
 *
 * @see \Asciisd\CyberSource\CyberSource
 */
class CyberSource extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cybersource';
    }
}
