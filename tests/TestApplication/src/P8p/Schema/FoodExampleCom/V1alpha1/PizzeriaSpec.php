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

#[K8sSchemaRef(name: 'com.example.food.v1alpha1.Pizzeria.spec')]
class PizzeriaSpec
{
    /**
     * @param string                  $location    city or area where the pizzeria is located
     * @param string                  $name        the name of the pizzeria
     * @param bool                    $open        whether the pizzeria is currently open
     * @param PizzeriaSpecChef|null   $chef        information about the head chef
     * @param int|null                $pizzaCount  number of pizzas sold per day on average
     * @param array<int, string>|null $specialties list of available pizza specialties
     */
    public function __construct(
        public string $location,
        public string $name,
        public bool $open,
        public ?PizzeriaSpecChef $chef = null,
        public ?PizzeriaSpecDelivery $delivery = null,
        public ?int $pizzaCount = null,
        public ?array $specialties = null,
    ) {
    }
}
