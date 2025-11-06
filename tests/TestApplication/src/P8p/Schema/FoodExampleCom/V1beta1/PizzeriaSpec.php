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

#[K8sSchemaRef(name: 'com.example.food.v1beta1.Pizzeria.spec')]
class PizzeriaSpec
{
    /**
     * @param float|null              $ratingGoal  target rating the pizzeria aims to achieve
     * @param array<int, string>|null $specialties
     */
    public function __construct(
        public string $location,
        public string $name,
        public ?PizzeriaSpecChef $chef = null,
        public ?PizzeriaSpecDelivery $delivery = null,
        public ?bool $open = null,
        public ?float $ratingGoal = null,
        public ?array $specialties = null,
    ) {
    }
}
