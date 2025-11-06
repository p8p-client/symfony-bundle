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

namespace P8p\Bundle\Tests\TestApplication\P8p\Schema\FoodExampleCom\V1beta1;

use P8p\Client\Attribute\K8sSchemaRef;

#[K8sSchemaRef(name: 'com.example.food.v1beta1.Pizzeria.status')]
class PizzeriaStatus
{
    /**
     * @param array<int, PizzeriaStatusConditions>|null $conditions
     * @param int|null                                  $pizzasServed total pizzas served since opening
     */
    public function __construct(
        public ?array $conditions = null,
        public ?int $pizzasServed = null,
        public ?float $rating = null,
    ) {
    }
}
