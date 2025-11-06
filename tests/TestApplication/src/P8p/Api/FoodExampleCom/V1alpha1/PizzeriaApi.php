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

namespace P8p\Bundle\Tests\TestApplication\P8p\Api\FoodExampleCom\V1alpha1;

use P8p\Bundle\Tests\TestApplication\P8p\Schema\FoodExampleCom\V1alpha1\Pizzeria;
use P8p\Bundle\Tests\TestApplication\P8p\Schema\FoodExampleCom\V1alpha1\PizzeriaList;
use P8p\Client\Api\AbstractApi;
use P8p\Client\Response;
use P8p\Sdk\Schema\Core\V1\DeleteOptions;
use P8p\Sdk\Schema\Core\V1\Status;

class PizzeriaApi extends AbstractApi
{
    /**
     * List objects of kind Pizzeria.
     *
     * @param string                                                                                                                                                                                                                                                                                                           $namespace       object name and auth scope, such as for teams and projects
     * @param array{pretty?: string|null, allowWatchBookmarks?: bool|null, continue?: string|null, fieldSelector?: string|null, labelSelector?: string|null, limit?: int|null, resourceVersion?: string|null, resourceVersionMatch?: string|null, sendInitialEvents?: bool|null, timeoutSeconds?: int|null, watch?: bool|null} $queryParameters
     *
     * @return Response<PizzeriaList>
     */
    public function list(string $namespace, array $queryParameters = []): Response
    {
        return $this->client->makeRequest(
            verb: 'GET',
            path: '/apis/food.example.com/v1alpha1/namespaces/{namespace}/pizzerias',
            pathParameters: ['namespace' => $namespace],
            responseClass: PizzeriaList::class,
            queryParameters: $queryParameters,
        );
    }

    /**
     * Create a Pizzeria.
     *
     * @param string                                                                                                       $namespace       object name and auth scope, such as for teams and projects
     * @param array{pretty?: string|null, dryRun?: string|null, fieldManager?: string|null, fieldValidation?: string|null} $queryParameters
     *
     * @return Response<Pizzeria>
     */
    public function create(string $namespace, Pizzeria $body, array $queryParameters = []): Response
    {
        return $this->client->makeRequest(
            verb: 'POST',
            path: '/apis/food.example.com/v1alpha1/namespaces/{namespace}/pizzerias',
            pathParameters: ['namespace' => $namespace],
            responseClass: Pizzeria::class,
            body: $body,
            queryParameters: $queryParameters,
        );
    }

    /**
     * Delete collection of Pizzeria.
     *
     * @param string                                                                                                                                                                                                                                                                                                           $namespace       object name and auth scope, such as for teams and projects
     * @param array{pretty?: string|null, allowWatchBookmarks?: bool|null, continue?: string|null, fieldSelector?: string|null, labelSelector?: string|null, limit?: int|null, resourceVersion?: string|null, resourceVersionMatch?: string|null, sendInitialEvents?: bool|null, timeoutSeconds?: int|null, watch?: bool|null} $queryParameters
     *
     * @return Response<Status>
     */
    public function deleteCollection(string $namespace, array $queryParameters = []): Response
    {
        return $this->client->makeRequest(
            verb: 'DELETE',
            path: '/apis/food.example.com/v1alpha1/namespaces/{namespace}/pizzerias',
            pathParameters: ['namespace' => $namespace],
            responseClass: Status::class,
            queryParameters: $queryParameters,
        );
    }

    /**
     * Read the specified Pizzeria.
     *
     * @param string                                                     $name            name of the Pizzeria
     * @param string                                                     $namespace       object name and auth scope, such as for teams and projects
     * @param array{pretty?: string|null, resourceVersion?: string|null} $queryParameters
     *
     * @return Response<Pizzeria>
     */
    public function read(string $name, string $namespace, array $queryParameters = []): Response
    {
        return $this->client->makeRequest(
            verb: 'GET',
            path: '/apis/food.example.com/v1alpha1/namespaces/{namespace}/pizzerias/{name}',
            pathParameters: ['name' => $name, 'namespace' => $namespace],
            responseClass: Pizzeria::class,
            queryParameters: $queryParameters,
        );
    }

    /**
     * Replace the specified Pizzeria.
     *
     * @param string                                                                                                       $name            name of the Pizzeria
     * @param string                                                                                                       $namespace       object name and auth scope, such as for teams and projects
     * @param array{pretty?: string|null, dryRun?: string|null, fieldManager?: string|null, fieldValidation?: string|null} $queryParameters
     *
     * @return Response<Pizzeria>
     */
    public function replace(string $name, string $namespace, Pizzeria $body, array $queryParameters = []): Response
    {
        return $this->client->makeRequest(
            verb: 'PUT',
            path: '/apis/food.example.com/v1alpha1/namespaces/{namespace}/pizzerias/{name}',
            pathParameters: ['name' => $name, 'namespace' => $namespace],
            responseClass: Pizzeria::class,
            body: $body,
            queryParameters: $queryParameters,
        );
    }

    /**
     * Delete a Pizzeria.
     *
     * @param string                                                                                                                                                                                                        $name            name of the Pizzeria
     * @param string                                                                                                                                                                                                        $namespace       object name and auth scope, such as for teams and projects
     * @param array{pretty?: string|null, dryRun?: string|null, gracePeriodSeconds?: int|null, ignoreStoreReadErrorWithClusterBreakingPotential?: bool|null, orphanDependents?: bool|null, propagationPolicy?: string|null} $queryParameters
     *
     * @return Response<Status>
     */
    public function delete(
        string $name,
        string $namespace,
        DeleteOptions $body,
        array $queryParameters = [],
    ): Response {
        return $this->client->makeRequest(
            verb: 'DELETE',
            path: '/apis/food.example.com/v1alpha1/namespaces/{namespace}/pizzerias/{name}',
            pathParameters: ['name' => $name, 'namespace' => $namespace],
            responseClass: Status::class,
            body: $body,
            queryParameters: $queryParameters,
        );
    }

    /**
     * Partially update the specified Pizzeria.
     *
     * @param string                                                                                                                          $name            name of the Pizzeria
     * @param string                                                                                                                          $namespace       object name and auth scope, such as for teams and projects
     * @param array<mixed>                                                                                                                    $body
     * @param array{pretty?: string|null, dryRun?: string|null, fieldManager?: string|null, fieldValidation?: string|null, force?: bool|null} $queryParameters
     *
     * @return Response<Pizzeria>
     */
    public function patch(string $name, string $namespace, array $body, array $queryParameters = []): Response
    {
        return $this->client->makeRequest(
            verb: 'PATCH',
            path: '/apis/food.example.com/v1alpha1/namespaces/{namespace}/pizzerias/{name}',
            pathParameters: ['name' => $name, 'namespace' => $namespace],
            responseClass: Pizzeria::class,
            body: $body,
            queryParameters: $queryParameters,
        );
    }

    /**
     * List objects of kind Pizzeria.
     *
     * @param array{allowWatchBookmarks?: bool|null, continue?: string|null, fieldSelector?: string|null, labelSelector?: string|null, limit?: int|null, pretty?: string|null, resourceVersion?: string|null, resourceVersionMatch?: string|null, sendInitialEvents?: bool|null, timeoutSeconds?: int|null, watch?: bool|null} $queryParameters
     *
     * @return Response<PizzeriaList>
     */
    public function listForAllNamespaces(array $queryParameters = []): Response
    {
        return $this->client->makeRequest(
            verb: 'GET',
            path: '/apis/food.example.com/v1alpha1/pizzerias',
            responseClass: PizzeriaList::class,
            queryParameters: $queryParameters,
        );
    }
}
