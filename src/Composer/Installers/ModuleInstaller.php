<?php
declare(strict_types=1);
/**
 * @author Dimitar Dimitrov <daghostman.dd@gmail.com>
 */
namespace Onion\Composer\Installers;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Onion\Composer\Installers\Interfaces\InstallerInterface;
use Onion\Composer\Installers\Traits\CommonInstallerTrait;
use Onion\Composer\Installers\Traits\TestRunnerTrait;

class ModuleInstaller extends LibraryInstaller implements InstallerInterface
{
    use CommonInstallerTrait;
    use TestRunnerTrait;

    public function supports($packageType):bool
    {
        return strtolower($packageType) === 'module';
    }

    public function getInstallPath(PackageInterface $package):string
    {
        if (!$this->validatePackageName($package)) {
            $prefix = $this->getPackagePrefix();

            throw new \InvalidArgumentException(sprintf(
                'Unable to install module, onion modules must be ' .
                'prefixed with "%s". Valid naming convention is ' .
                '"*/%s*"',
                $prefix,
                $prefix
            ));
        }

        return 'modules/' .
        $this->translatePackageNameToInstallPath(
            $package->getPrettyName()
        );
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        try {
            $this->runPackageTests($this->getInstallPath($package));
        } catch (\RuntimeException $ex) {
            $this->uninstall($repo, $package);
            throw $ex;
        }
    }

    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        try {
            $this->runPackageTests($this->getInstallPath($target));
        } catch (\RuntimeException $ex) {
            $this->uninstall($repo, $target);
            $this->install($repo, $initial);
            throw $ex;
        }
    }
}
