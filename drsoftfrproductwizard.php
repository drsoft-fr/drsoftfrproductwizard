<?php

declare(strict_types=1);

use DrSoftFr\Module\ProductWizard\Config;
use DrSoftFr\Module\ProductWizard\Controller\Admin\ConfiguratorController;
use DrSoftFr\Module\ProductWizard\Controller\Hook\ActionFrontControllerSetMediaController;
use DrSoftFr\Module\ProductWizard\Controller\Hook\ActionFrontControllerSetVariablesController;
use DrSoftFr\Module\ProductWizard\Controller\Hook\ActionOutputHTMLBeforeController;
use DrSoftFr\Module\ProductWizard\Controller\Hook\DisplayBeforeBodyClosingTagController;
use DrSoftFr\Module\ProductWizard\Install\Factory\InstallerFactory;
use DrSoftFr\Module\ProductWizard\Install\Installer;
use PrestaShop\PrestaShop\Core\Cache\Clearer\CacheClearerChain;

if (!defined('_PS_VERSION_') || !defined('_CAN_LOAD_FILES_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

/**
 * Class drsoftfrproductwizard
 */
class drsoftfrproductwizard extends Module
{
    /**
     * @var string $authorEmail Author email
     */
    public $authorEmail;

    /**
     * @var string $moduleGithubRepositoryUrl Module GitHub repository URL
     */
    public $moduleGithubRepositoryUrl;

    /**
     * @var string $moduleGithubIssuesUrl Module GitHub issues URL
     */
    public $moduleGithubIssuesUrl;

    public function __construct()
    {
        $this->author = 'drSoft.fr';
        $this->bootstrap = true;
        $this->dependencies = [];
        $this->name = 'drsoftfrproductwizard';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_
        ];
        $this->tab = 'content_management';
        $this->tabs = [
            [
                'class_name' => ConfiguratorController::TAB_CLASS_NAME,
                'name' => 'Product Wizard',
                'parent_class_name' => 'AdminCatalog',
                'route_name' => 'admin_drsoft_fr_product_wizard_configurator_index',
                'visible' => true,
            ],
        ];
        $this->version = '1.0.0';
        $this->authorEmail = 'contact@drsoft.fr';
        $this->moduleGithubRepositoryUrl = 'https://github.com/drsoft-fr/drsoftfrproductwizard';
        $this->moduleGithubIssuesUrl = 'https://github.com/drsoft-fr/drsoftfrproductwizard/issues';

        parent::__construct();

        $this->displayName = $this->trans('drSoft.fr Product Wizard, Assistant de configuration produit', [], 'Modules.Drsoftfrproductwizard.Admin');
        $this->description = $this->trans('Créez des parcours guidés de sélection de produits pour vos clients.', [], 'Modules.Drsoftfrproductwizard.Admin');

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', [], 'Modules.Drsoftfrproductwizard.Admin');
    }

    /**
     * Disables the module.
     *
     * @param bool $force_all Whether to disable all instances of the module, even if they are currently enabled.
     *
     * @return bool Whether the module was disabled successfully.
     */
    public function disable($force_all = false)
    {
        if (!parent::disable($force_all)) {
            $this->handleException(
                new Exception(
                    $this->trans(
                        'An error has occurred when deactivating the module.',
                        [],
                        'Modules.Drsoftfrproductwizard.Error'
                    )
                )
            );

            return false;
        }

        try {
            $this->getCacheClearerChain()->clear();
        } catch (Throwable $t) {
            $this->handleException($t);
        }

        return true;
    }

    /**
     * Enables the module by clearing the cache and calling the parent's enable method.
     *
     * @param bool $force_all Whether to force the enabling of all modules.
     *
     * @return bool True on successful enabling, false otherwise.
     */
    public function enable($force_all = false)
    {
        if (!parent::enable($force_all)) {
            $this->handleException(
                new Exception(
                    $this->trans(
                        'An error has occurred when activating the module.',
                        [],
                        'Modules.Drsoftfrproductwizard.Error'
                    )
                )
            );

            return false;
        }

        try {
            $this->getCacheClearerChain()->clear();
        } catch (Throwable $t) {
            $this->handleException($t);
        }

        return true;
    }

    /**
     * Get the CacheClearerChain.
     *
     * @return CacheClearerChain
     *
     * @throws Exception
     */
    private function getCacheClearerChain(): CacheClearerChain
    {
        $cacheClearerChain = $this->get('prestashop.core.cache.clearer.cache_clearer_chain');

        if (!($cacheClearerChain instanceof CacheClearerChain)) {
            throw new Exception('The cacheClearerChain object must implement CacheClearerChain.');
        }

        return $cacheClearerChain;
    }

    /**
     * Redirects the admin user to the ValidateCustomerPro controller in the admin panel.
     *
     * @return void
     */
    public function getContent(): void
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink(ConfiguratorController::TAB_CLASS_NAME)
        );
    }

    /**
     * Handles an exception by logging an error message.
     *
     * @param Throwable $t The exception to handle.
     *
     * @return void
     */
    private function handleException(Throwable $t): void
    {
        $errorMessage = Config::createErrorMessage(__METHOD__, __LINE__, $t);

        PrestaShopLogger::addLog($errorMessage, 3);

        $this->_errors[] = $errorMessage;
    }

    /**
     * @param array $p
     *
     * @return void
     */
    public function hookActionFrontControllerSetMedia(array $p = []): void
    {
        $file = _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php';
        $controller = new ActionFrontControllerSetMediaController($this, $file, $this->_path, $p);

        $controller->run();
    }

    /**
     * @param array $p
     *
     * @return array
     */
    public function hookActionFrontControllerSetVariables(array $p = []): array
    {
        $file = _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php';
        $controller = new ActionFrontControllerSetVariablesController($this, $file, $this->_path, $p);

        return $controller->run();
    }

    /**
     * @param array $p
     *
     * @return string
     */
    public function hookActionOutputHTMLBefore(array $p = []): string
    {
        $file = _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php';
        $controller = new ActionOutputHTMLBeforeController($this, $file, $this->_path, $p);

        return $controller->run();
    }

    /**
     * @param array $p
     *
     * @return string
     */
    public function hookDisplayBeforeBodyClosingTag(array $p = []): string
    {
        $file = _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php';
        $controller = new DisplayBeforeBodyClosingTagController($this, $file, $this->_path, $p);

        return $controller->run();
    }

    /**
     * Installs the module
     *
     * @return bool Returns true if the installation is successful, false otherwise.
     *
     * @throws PrestaShopException
     */
    public function install(): bool
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install()) {
            $this->handleException(
                new Exception(
                    $this->trans(
                        'There was an error during the installation.',
                        [],
                        'Modules.Drsoftfrproductwizard.Error'
                    )
                )
            );

            return false;
        }

        try {
            $installer = InstallerFactory::create();

            $installer->install($this);
        } catch (Throwable $t) {
            $this->handleException($t);

            return false;
        }

        try {
            $this->getCacheClearerChain()->clear();
        } catch (Throwable $t) {
            $this->handleException($t);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /**
     * Uninstalls the module
     *
     * @return bool Returns true if uninstallation was successful, false otherwise
     */
    public function uninstall(): bool
    {
        try {
            /** @var Installer $installer */
            $installer = $this->get(Config::INSTALLER_SERVICE);

            $installer->uninstall($this);
        } catch (Throwable $t) {
            $this->handleException($t);

            return false;
        }

        if (!parent::uninstall()) {
            $this->handleException(
                new Exception(
                    $this->trans(
                        'There was an error during the uninstallation.',
                        [],
                        'Modules.Drsoftfrproductwizard.Error'
                    )
                )
            );

            return false;
        }

        try {
            $this->getCacheClearerChain()->clear();
        } catch (Throwable $t) {
            $this->handleException($t);
        }

        return true;
    }
}
