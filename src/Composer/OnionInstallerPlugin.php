<?php
declare(strict_types=1);
/**
 * @author Dimitar Dimitrov <daghostman.dd@gmail.com>
 */
namespace Onion\Composer;

use Composer\Composer;

use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Onion\Composer\Installers\ComponentInstaller;
use Onion\Composer\Installers\ModuleInstaller;
use Onion\Composer\Installers\TemplateInstaller;

class OnionInstallerPlugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        if (!defined('ROOT_DIR')) {
            define('ROOT_DIR', getcwd());
        }

        $installationManager = $composer->getInstallationManager();
        $installationManager->addInstaller(
            new ModuleInstaller(
                $io,
                $composer,
                'module'
            )
        );
        $installationManager->addInstaller(
            new ComponentInstaller(
                $io,
                $composer,
                'component'
            )
        );
        $installationManager->addInstaller(
            new TemplateInstaller(
                $io,
                $composer,
                'template'
            )
        );
    }
}
