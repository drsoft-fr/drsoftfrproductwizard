<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Controller\Hook;

use DrSoftFr\Module\ProductWizard\Config;
use DrSoftFr\PrestaShopModuleHelper\Controller\Hook\AbstractHookController;
use DrSoftFr\PrestaShopModuleHelper\Controller\Hook\HookControllerInterface;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\Package;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Exception;
use Module;
use Throwable;
use Tools;

final class DisplayBeforeBodyClosingTagController extends AbstractHookController implements HookControllerInterface
{
    /**
     * @var Package
     */
    private $manifest;

    /**
     * @param Module $module
     * @param string $file
     * @param string $path
     * @param array $props
     */
    public function __construct(
        Module $module,
        string $file,
        string $path,
        array  $props
    )
    {
        parent::__construct(
            $module,
            $file,
            $path,
            $props
        );

        $this->manifest = new Package(
            new JsonManifestVersionStrategy(
                _PS_MODULE_DIR_ . '/' . $this->module->name . '/views/.vite/manifest.json'
            )
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

        $this->logger->error($errorMessage, [
            'error_code' => $t->getCode(),
            'object_type' => null,
            'object_id' => null,
            'allow_duplicate' => false,
        ]);
    }

    public function run(): string
    {
        try {
            $path = 'modules/' . $this->module->name . '/views/' . $this->manifest->getUrl('src/js/front/configurator/main.js')['file'];
            $domain = Tools::getShopDomainSsl(true, true);
            $url = $domain . '/' . $path;

            $this->getContext()->smarty->assign([
                'url' => $url,
            ]);

            return $this->module->display($this->file, '/views/templates/hook/display_before_body_closing_tag.tpl');
        } catch (Throwable $t) {
            $this->handleException($t);

            return '';
        }
    }
}
