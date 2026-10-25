<?php

namespace XF\Purchasable;

use XF\Entity\PaymentProfile;
use XF\Entity\User;

// As XenForo documents it: purchasableId a string, which core's own purchasable assigns an entity id to.
/**
 * @property string $title
 * @property string $description
 * @property float $cost
 * @property string $currency
 * @property bool $recurring
 * @property int $lengthAmount
 * @property string $lengthUnit
 * @property User $purchaser
 * @property PaymentProfile $paymentProfile
 * @property string $purchasableTypeId
 * @property string $purchasableId
 * @property string $purchasableTitle
 * @property array $extraData
 * @property string $cancelUrl
 * @property string $returnUrl
 * @property string $updateUrl
 * @property string $requestKey
 */
class Purchase
{
    public function __get($key)
    {
        return null;
    }

    public function __set($key, $value)
    {
    }
}
