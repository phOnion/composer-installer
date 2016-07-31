<?php
declare(strict_types=1);
/**
 * @author Dimitar Dimitrov <daghostman.dd@gmail.com>
 */

namespace Onion\Composer\Installers\Interfaces;


use Composer\Package\PackageInterface;

interface InstallerInterface
{
    public function validatePackageName(PackageInterface $packageName):bool;
    public function getPackagePrefix():string;
    public function translatePackageNameToInstallPath($packageName):string;
}
