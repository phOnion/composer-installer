<?php
declare(strict_types=1);
/**
 * @author Dimitar Dimitrov <daghostman.dd@gmail.com>
 */
namespace Onion\Composer\Installers\Traits;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

trait TestRunnerTrait
{
    public function getPhpunitExecutablePath():string
    {
        if (0 !== strpos(PHP_OS, 'WIN')) {
            return glob('{*/bin,bin}phpunit', GLOB_BRACE)[0];
        }

        return glob('{*/bin,bin}/phpunit.bat', GLOB_BRACE)[0];
    }

    public function runPackageTests(string $installPath)
    {
        if (!is_dir($installPath . '/test') && !is_dir($installPath . '/tests')) {
            throw new \RuntimeException('No unit tests found for module');
        }

        $workdirBackup = getcwd();
        chdir($installPath);
        $coverageDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5($installPath);
        $command = sprintf(
            '%s --coverage-xml %s --strict-coverage --report-useless-tests ' .
            '--disallow-test-output --disallow-todo-tests' .
            '--no-globals-backup --process-isolation ' .
            '--stop-on-warning',
            $this->getPhpunitExecutablePath(),
            $coverageDir
        );
        exec($command, $output, $testsResult);

        if ($testsResult !== 0) {
            throw new \RuntimeException("Tests for %s failed with \n\n . " . $output);
        }

        $xml = new \SimpleXMLElement($coverageDir . '/index.xml', null, true);
        foreach ($xml->getDocNamespaces() as $prefix => $namespace) {
            if ($prefix === null) {
                $prefix = 'c';
            }

            $xml->registerXPathNamespace($prefix, $namespace);
        }

        $totals = $xml->xpath('//c:project/directory/totals');

        $sum = 0.00;
        foreach ($totals as $total) {
            $sum += (float) $total->attributes()['percent'];
        }

        if ($sum / count($totals) < 85) {
            throw new \RuntimeException(
                'Package, does not meet the necessary test coverage standard (85%). ' .
                'Improve code quality or contact maintainer'
            );
        }

        if ($command !== 0) {
            chdir($workdirBackup);
            return false;
        }

    }
}
