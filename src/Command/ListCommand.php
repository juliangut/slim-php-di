<?php

/*
 * (c) 2015-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI\Command;

use DI\Container;
use DI\Definition\Exception\InvalidDefinition;
use DI\NotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListCommand extends Command
{
    public const NAME = 'slim:container:list';

    protected static $defaultName = self::NAME;

    public function __construct(
        private Container $container,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Container debug definitions')
            ->addOption('full', null, InputOption::VALUE_NONE, 'Show full definition')
            ->addArgument('search', InputArgument::OPTIONAL, 'Definition search pattern')
            ->setHelp(<<<'HELP'
            The <info>%command.name%</info> command lists container debug definitions.

            You can search for definitions by a pattern:

              <info>%command.full_name%</info> <comment>Logger</comment>

            Result definitions can be displayed fully:

              <info>%command.full_name%</info> <comment>--full</comment>

            HELP);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $ioStyle = new SymfonyStyle($input, $output);

        $entries = $this->getEntries($input);
        if (\count($entries) === 0) {
            $ioStyle->error('No container entries to show');

            return self::FAILURE;
        }

        $ioStyle->comment('List of container entries');

        (new Table($output))
            ->setStyle('symfony-style-guide')
            ->setHeaders(['Entry', 'Definition'])
            ->setRows($this->getTableRows($entries, $input->getOption('full') !== false))
            ->render();

        $ioStyle->newLine();

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function getEntries(InputInterface $input): array
    {
        $entries = $this->container->getKnownEntryNames();

        $searchPattern = $this->getSearchPattern($input);
        if ($searchPattern === null) {
            return array_values($entries);
        }

        return array_values(array_filter(
            $entries,
            static fn(string $entry): bool => preg_match($searchPattern, $entry) === 1,
        ));
    }

    private function getSearchPattern(InputInterface $input): ?string
    {
        /** @var string|null $searchPattern */
        $searchPattern = $input->getArgument('search');
        if ($searchPattern === null) {
            return null;
        }

        foreach (['~', '!', '\/', '#', '%', '\|'] as $delimiter) {
            $pattern = sprintf('/^%1$s.*%1$s[imsxeuADSUXJ]*$/', $delimiter);
            if (preg_match($pattern, $searchPattern) === 1) {
                return $searchPattern;
            }
        }

        return sprintf('/%s/i', preg_quote($searchPattern, '/'));
    }

    /**
     * Get entries formatted for table.
     *
     * @param list<string> $entries
     *
     * @return array<list<string>>
     */
    private function getTableRows(array $entries, bool $fullDefinition): array
    {
        return array_map(
            function (string $entryName) use ($fullDefinition): array {
                try {
                    $definition = $this->container->debugEntry($entryName);
                } catch (InvalidDefinition | NotFoundException) {
                    $definition = 'Invalid definition';
                }

                return [$entryName, $this->formatDefinition($definition, $fullDefinition)];
            },
            $entries,
        );
    }

    /**
     * Format definition output.
     */
    private function formatDefinition(string $definition, bool $fullDefinition): string
    {
        if ($definition === 'Factory') {
            return $definition;
        }

        if (preg_match('/^Object \(\n {4}class =/', $definition) === 1) {
            return $this->formatObjectDefinition($definition, $fullDefinition);
        }

        if (preg_match('/^Value \(/', $definition) === 1) {
            return $this->formatValueDefinition($definition, $fullDefinition);
        }

        if (preg_match('/^\[/', $definition) === 1) {
            return $this->formatArrayDefinition($definition, $fullDefinition);
        }

        if (preg_match('/^Environment variable/', $definition) === 1) {
            return $this->formatEnvironmentDefinition($definition, $fullDefinition);
        }

        if (preg_match('/^get\(/', $definition) === 1) {
            return $this->formatAliasDefinition($definition);
        }

        if (preg_match('/^Decorate\(/', $definition) === 1) {
            return $this->formatDecorateDefinition($definition);
        }

        return $definition;
    }

    private function formatObjectDefinition(string $definition, bool $fullDefinition): string
    {
        if ($fullDefinition) {
            return $definition;
        }

        preg_match('/^Object \(\n {4}class = (#(NOT INSTANTIABLE)# )?(.+)\n(.+)/', $definition, $matches);

        return sprintf(
            '%s%sObject (%s)',
            preg_match('/lazy = true/', $matches[4]) === 1 ? 'Lazy ' : '',
            \array_key_exists(2, $matches) ? 'Not Instantiable ' : '',
            $matches[3],
        );
    }

    private function formatValueDefinition(string $definition, bool $fullDefinition): string
    {
        return $fullDefinition ? $definition : 'Value';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function formatArrayDefinition(string $definition, bool $fullDefinition): string
    {
        return $fullDefinition ? 'Array (' . $definition . ')' : 'Array';
    }

    private function formatEnvironmentDefinition(string $definition, bool $fullDefinition): string
    {
        if ($fullDefinition) {
            return $definition;
        }

        /**
         * @see https://regex101.com/r/jDgOgO/1
         */
        return preg_replace(
            '/^Environment variable \(\n +variable = ([^\n]+)\n +optional = (?:yes|no).*\n\)$/s',
            'Environment variable ($1)',
            $definition,
        ) ?? '';
    }

    private function formatAliasDefinition(string $definition): string
    {
        return preg_replace('/^get\((.+)\)$/', 'Alias ($1)', $definition) ?? '';
    }

    private function formatDecorateDefinition(string $definition): string
    {
        return preg_replace('/^Decorate\((.+)\)$/', 'Decorate ($1)', $definition) ?? '';
    }
}
