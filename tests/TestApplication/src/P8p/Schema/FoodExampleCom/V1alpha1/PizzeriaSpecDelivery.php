<?php

/*
 * This file is part of the P8P project.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace P8p\Bundle\Tests\TestApplication\P8p\Schema\FoodExampleCom\V1alpha1;

use P8p\Client\Attribute\K8sSchemaRef;

#[K8sSchemaRef(name: 'com.example.food.v1alpha1.Pizzeria.spec.delivery')]
class PizzeriaSpecDelivery
{
    /**
     * @param array<int, string>|null $partners Names of delivery partners (e.g. “UberEats”, “Deliveroo”).
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?array $partners = null,
    ) {
    }
}
