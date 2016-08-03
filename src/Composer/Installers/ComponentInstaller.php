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

class ComponentInstaller extends LibraryInstaller implements InstallerInterface
{
    use CommonInstallerTrait;
    use TestRunnerTrait;

    public function supports($packageType):bool
    {
        return strtolower($packageType) === 'component';
    }

    public function getInstallPath(PackageInterface $package):string
    {
        if (!$this->validatePackageName($package)) {
            $prefix = $this->getPackagePrefix();

            throw new \InvalidArgumentException(sprintf(
                'Unable to install component, onion components must be ' .
                'prefixed with "%s". Valid naming convention is ' .
                '"*/%s*"',
                $prefix,
                $prefix
            ));
        }

        return 'components/' .
            $this->translatePackageNameToInstallPath(
                $package->getPrettyName()
            );
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        try {
            $this->runPackageTests($this->getInstallPath($package));
            $this->installConfigurationFiles($package);
        } catch (\RuntimeException $ex) {
            $this->io->writeError($ex->getMessage());
            $this->uninstall($repo, $package);
        }
    }

    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::uninstall($repo, $package);
        $this->uninstallConfigurationFiles($package);
    }

    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        $this->uninstallConfigurationFiles($initial);
        try {
            parent::update($repo, $initial, $target);
            $this->installConfigurationFiles($target);
        } catch (\RuntimeException $ex) {
            $this->io->writeError($ex->getMessage());
            $this->install($repo, $initial);
        }
    }
}
