<?php

/*
 * This file is part of the P8P project.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace P8p\Bundle\Tests\Command;

use P8p\Bundle\Command\GenerateCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateCommandTest extends TestCase
{
    public function testCommandWithoutConfiguration(): void
    {
        $command = new GenerateCommand([]);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('No generator configuration found', $tester->getDisplay());
    }

    public function testCommandName(): void
    {
        $command = new GenerateCommand([]);

        $this->assertSame('p8p:generate', $command->getName());
    }

    public function testCommandDescription(): void
    {
        $command = new GenerateCommand([]);

        $this->assertSame('Generate Custom Resource Definition (CRD) classes', $command->getDescription());
    }

    public function testCommandHasForceOption(): void
    {
        $command = new GenerateCommand([]);

        $this->assertTrue($command->getDefinition()->hasOption('force'));
        $this->assertTrue($command->getDefinition()->hasShortcut('f'));
    }

    public function testCommandHasBaseUrlOption(): void
    {
        $command = new GenerateCommand([]);

        $this->assertTrue($command->getDefinition()->hasOption('base-url'));
        $this->assertTrue($command->getDefinition()->hasShortcut('b'));

        $option = $command->getDefinition()->getOption('base-url');
        $this->assertSame('http://127.0.0.1:8001/', $option->getDefault());
    }
}
