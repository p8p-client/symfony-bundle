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

#[K8sSchemaRef(name: 'com.example.food.v1alpha1.Pizzeria.spec.chef')]
class PizzeriaSpecChef
{
    public function __construct(
        public ?int $experienceYears = null,
        public ?string $favoritePizza = null,
        public ?string $name = null,
    ) {
    }
}
