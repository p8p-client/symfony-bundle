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

namespace P8p\Bundle\Tests\TestApplication\Controller;

use P8p\Bundle\Factory\ClientRegistry;
use P8p\Client\Client;
use P8p\Sdk\Api\Core\V1\PodApi;
use P8p\Sdk\Schema\Core\V1\Container;
use P8p\Sdk\Schema\Core\V1\ObjectMeta;
use P8p\Sdk\Schema\Core\V1\Pod;
use P8p\Sdk\Schema\Core\V1\PodSpec;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/', name: 'default_client')]
    public function index(Client $client): Response
    {
        return $this->render('index.html.twig', [
            'pods' => $client->getApi(PodApi::class)->listForAllNamespaces()->getContent(),
        ]);
    }

    #[Route('/registry', name: 'registry_client')]
    public function registry(ClientRegistry $clientRegistry): Response
    {
        $client = $clientRegistry->get('other');

        return $this->render('index.html.twig', [
            'pods' => $client->getApi(PodApi::class)->listForAllNamespaces()->getContent(),
        ]);
    }

    #[Route('/registry-default', name: 'registry_default_client')]
    public function registryDefault(ClientRegistry $clientRegistry): Response
    {
        $client = $clientRegistry->getDefault();

        return $this->render('index.html.twig', [
            'pods' => $client->getApi(PodApi::class)->listForAllNamespaces()->getContent(),
        ]);
    }

    #[Route('/injectOther', name: 'inject_other_client')]
    public function injectOther(Client $otherClient): Response
    {
        return $this->render('index.html.twig', [
            'pods' => $otherClient->getApi(PodApi::class)->listForAllNamespaces()->getContent(),
        ]);
    }

    #[Route('/injectDefault', name: 'inject_default_client')]
    public function injectDefault(Client $defaultClient): Response
    {
        return $this->render('index.html.twig', [
            'pods' => $defaultClient->getApi(PodApi::class)->listForAllNamespaces()->getContent(),
        ]);
    }

    #[Route('/add-pod', name: 'add_pod')]
    public function addPod(Client $defaultClient): Response
    {
        $client = $defaultClient->getApi(PodApi::class)->create('default', new Pod(
            metadata: new ObjectMeta(
                name: 'test-pod-from-controller',
            ),
            spec: new PodSpec(
                containers: [
                    new Container(
                        name: 'nginx',
                        image: 'nginx',
                    ),
                ]
            )
        ))->getContent();

        return $this->render('index.html.twig', [
            'pods' => $defaultClient->getApi(PodApi::class)->listForAllNamespaces()->getContent(),
        ]);
    }
}
