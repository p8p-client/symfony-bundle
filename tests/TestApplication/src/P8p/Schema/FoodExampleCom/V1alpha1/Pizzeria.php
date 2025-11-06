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

use P8p\Client\Attribute\K8sSchema;
use P8p\Client\Attribute\K8sSchemaRef;
use P8p\Sdk\Schema\Core\V1\ObjectMeta;

#[K8sSchemaRef(name: 'com.example.food.v1alpha1.Pizzeria')]
#[K8sSchema(kind: 'Pizzeria', group: 'food.example.com', version: 'v1alpha1')]
class Pizzeria
{
    /**
     * @param ObjectMeta|null $metadata Standard object's metadata. More info: https://git.k8s.io/community/contributors/devel/sig-architecture/api-conventions.md#metadata
     */
    public function __construct(
        public ?ObjectMeta $metadata = null,
        public ?PizzeriaSpec $spec = null,
        public ?PizzeriaStatus $status = null,
    ) {
    }
}
