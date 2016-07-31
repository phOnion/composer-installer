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

class TemplateInstaller extends LibraryInstaller implements InstallerInterface
{
    use CommonInstallerTrait;

    public function supports($packageType):bool
    {
        return strtolower($packageType) === 'template';
    }

    public function getInstallPath(PackageInterface $package):string
    {
        if (!$this->validatePackageName($package)) {
            $prefix = $this->getPackagePrefix();

            throw new \InvalidArgumentException(sprintf(
                'Unable to install template, onion template packages ' .
                'must be prefixed with "%s". Valid naming convention ' .
                'is "*/%s*"',
                $prefix,
                $prefix
            ));
        }

        return 'templates/' .
            $this->translatePackageNameToInstallPath(
                $package->getPrettyName()
            );
    }
}
