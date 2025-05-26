<?php

declare(strict_types=1);

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
class DrsoftFrProductWizard extends Module
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
        $this->tabs = [];
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
        $this->_clearCache('*');

        if (!parent::disable($force_all)) {
            return false;
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
        $this->_clearCache('*');

        if (!parent::enable($force_all)) {
            return false;
        }

        return true;
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
            $this->_errors[] = $this->trans(
                'There was an error during the installation.',
                [],
                'Modules.Drsoftfrproductwizard.Error'
            );

            return false;
        }

        try {
            $this->getCacheClearerChain()->clear();
        } catch (Throwable $t) {
            $this->_errors[] = $t->getMessage();
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
        if (!parent::uninstall()) {
            $this->_errors[] = $this->trans(
                'There was an error during the uninstallation.',
                [],
                'Modules.Drsoftfrproductwizard.Error'
            );

            return false;
        }

        try {
            $this->getCacheClearerChain()->clear();
        } catch (Throwable $t) {
            $this->_errors[] = $t->getMessage();
        }

        return parent::uninstall();
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
}
