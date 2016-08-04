<?php
declare(strict_types=1);
/**
 * @author Dimitar Dimitrov <daghostman.dd@gmail.com>
 */
namespace Onion\Composer\Installers\Traits;

use Composer\Package\PackageInterface;

trait CommonInstallerTrait
{
    public function getPackagePrefix():string
    {
        return 'onion-';
    }

    public function validatePackageName(PackageInterface $packageName):bool
    {
        list($vendor, $project)=
            explode('/', $packageName->getPrettyName(), 2);

        return 0 === strpos($project, $this->getPackagePrefix());
    }

    public function translatePackageNameToInstallPath($packageName):string
    {
        list($vendor, $project)=explode('/', $packageName, 2);

        return sprintf(
            '%s/%s',
            $vendor,
            substr($project, strlen($this->getPackagePrefix()))
        );
    }

    protected function installConfigurationFiles(PackageInterface $package)
    {
        $moduleConfigurationPath = realpath($this->getInstallPath($package) . '/config');

        $configDirectory = 'config/' .
            $this->translatePackageNameToInstallPath(
                $package->getPrettyName()
            );

        if (!@mkdir($configDirectory, 0755, true) && !is_dir($configDirectory)) {
            throw new \RuntimeException('Unable to create directory %s, check permissions');
        }

        foreach (glob($moduleConfigurationPath . '/*/*.global.php') as $item) {
            if (is_dir($item)) {
                continue;
            }

            if (is_link($item)) {
                $item = readlink($item);
            }

            symlink(
                $item,
                realpath($configDirectory) .
                DIRECTORY_SEPARATOR .
                pathinfo($item, PATHINFO_BASENAME)
            );
        }
    }

    protected function uninstallConfigurationFiles(PackageInterface $package)
    {

        $packageConfigDir = 'config/' . $this->translatePackageNameToInstallPath($package->getPrettyName());

        if (!is_dir($packageConfigDir)) {
            return;
        }

        $wiped = true;
        foreach (glob($packageConfigDir . '/*.php') as $item) {
            unlink($item);
            if ($wiped === true) {
                $wiped = file_exists($item);
            }
        }

        if (!$wiped) {
            throw new \RuntimeException('Directory %s is not empty, please remove it manually.');
        }

        rmdir($packageConfigDir);
        rmdir(pathinfo($packageConfigDir, PATHINFO_DIRNAME));
    }

}
