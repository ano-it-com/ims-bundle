<?php

namespace ANOITCOM\IMSBundle\Command\Install;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class InstallCommand extends Command
{

    protected static $defaultName = 'ims:install';

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var KernelInterface
     */
    private $kernel;


    public function __construct(
        KernelInterface $kernel,
        Filesystem $fs
    ) {
        parent::__construct(self::$defaultName);
        $this->kernel = $kernel;
        $this->fs     = $fs;
    }


    public function run(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('Installing IMS Bundle');

        $io->writeln('Installing migrations...');
        $migrationsPath = $this->installMigrations();

        $io->success('Migrations installed to ' . $migrationsPath);

        return 0;

    }


    private function installMigrations(): string
    {

        [ $migrationContent, $className ] = $this->compileMigration();

        $path = $this->kernel->getProjectDir() . '/src/Migrations/' . $className . '.php';

        $this->fs->dumpFile($path, $migrationContent);

        return $path;
    }


    private function compileMigration(): array
    {
        $templatePath = __DIR__ . '/Migrations/Migration.tpl.php';
        $className    = 'Version' . (new \DateTime('now', new \DateTimeZone('UTC')))->format('YmdHis');

        ob_start();

        include $templatePath;

        $content = ob_get_clean();

        return [ $content, $className ];

    }
}