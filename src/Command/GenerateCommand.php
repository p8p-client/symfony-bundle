<?php

/*
 * This file is part of the P8P project.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace P8p\Bundle\Command;

use Composer\Autoload\ClassLoader;
use P8p\CodeGenerator\Config\Api;
use P8p\CodeGenerator\Config\Config;
use P8p\CodeGenerator\Model\Model;
use P8p\CodeGenerator\Reader\OpenApiV3Reader;
use P8p\CodeGenerator\Writer\Cleaner;
use P8p\CodeGenerator\Writer\Writer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'p8p:generate',
    description: 'Generate Custom Resource Definition (CRD) classes',
)]
class GenerateCommand extends Command
{
    /**
     * @param array<string, mixed> $generatorConfig
     */
    public function __construct(
        private readonly array $generatorConfig,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force generation without confirmation (will delete existing output directory)',
            )
            ->addOption(
                'base-url',
                'b',
                InputOption::VALUE_REQUIRED,
                'Kubernetes API base URL',
                'http://127.0.0.1:8001/',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Check if CodeGenerator is installed
        if (!class_exists(Config::class)) {
            $io->error([
                'The p8p/code-generator package is required to use this command.',
                'Please install it by running:',
                '',
                '  composer require p8p/generator --dev',
            ]);

            return Command::FAILURE;
        }

        // Validate configuration
        if (empty($this->generatorConfig)) {
            $io->error('No generator configuration found. Please configure the "p8p.generator" section in your config/packages/p8p.yaml file.');

            return Command::FAILURE;
        }

        $baseUrl = rtrim((string) $input->getOption('base-url'), '/'); /* @phpstan-ignore cast.string */
        $force = (bool) $input->getOption('force');
        $outputPath = (string) $this->generatorConfig['path']; /* @phpstan-ignore cast.string */

        // Check if output directory exists and ask for confirmation
        if (is_dir($outputPath) && !$force) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                sprintf(
                    '<question>The output directory "%s" will be deleted and regenerated. Continue? [y/N]</question> ',
                    $outputPath,
                ),
                false,
            );

            if (!$helper->ask($input, $output, $question)) {
                $io->info('Operation cancelled.');

                return Command::SUCCESS;
            }
        }

        // Build Config object from bundle configuration
        $config = $this->buildConfig();

        $io->title('Generating CRD classes');
        $io->section('Configuration');
        $io->table(
            ['Setting', 'Value'],
            [
                ['Base URL', $baseUrl],
                ['Namespace', $config->baseNamespace],
                ['Output Path', $config->basePath],
                ['APIs', count($config->apis)],
            ],
        );

        try {
            // Read OpenAPI specifications
            $io->section('Reading OpenAPI specifications');
            $model = new Model();
            $openApiReader = new OpenApiV3Reader($baseUrl, $config);
            $openApiReader->read($model);
            $io->success('OpenAPI specifications loaded successfully');

            // Clean output directory
            $io->section('Cleaning output directory');
            $cleaner = new Cleaner($config);
            $cleaner->clean();
            $io->success('Output directory cleaned');

            // Write PHP files
            $io->section('Generating PHP classes');
            $writer = new Writer();
            $writer->write($model);
            $io->success('PHP classes generated successfully');

            $io->success('CRD generation completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error([
                'An error occurred during generation:',
                $e->getMessage(),
            ]);

            return Command::FAILURE;
        }
    }

    private function buildConfig(): Config
    {
        $apis = [];
        foreach ($this->generatorConfig['apis'] as $apiConfig) { /* @phpstan-ignore foreach.nonIterable */
            $apis[] = new Api(
                group: (string) $apiConfig['group'], /* @phpstan-ignore offsetAccess.nonOffsetAccessible, cast.string */
                version: (string) $apiConfig['version'], /* @phpstan-ignore offsetAccess.nonOffsetAccessible, cast.string */
            );
        }

        $vendorPath = \dirname((string) new \ReflectionClass(ClassLoader::class)->getFileName(), 2);

        return new Config(
            baseNamespace: (string) $this->generatorConfig['namespace'], /* @phpstan-ignore cast.string */
            basePath: (string) $this->generatorConfig['path'], /* @phpstan-ignore cast.string */
            apis: $apis,
            schemasOverride: [],
            externalSdkPath: $vendorPath.'/p8p/sdk/src',
        );
    }
}
