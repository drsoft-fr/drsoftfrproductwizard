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

final class ActionFrontControllerSetMediaController extends AbstractHookController implements HookControllerInterface
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

    /**
     * Runs the application.
     *
     * This function checks if the module is active and if the reCAPTCHA is enabled.
     * If both conditions are met, it registers the necessary JavaScript for the Google reCAPTCHA API.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $path = 'modules/' . $this->module->name . '/views/' . $this->manifest->getUrl('src/js/front/configurator/main.js')['css'][0];

            $this->getContext()->controller->registerStylesheet(
                'modules-' . $this->module->name . '-main',
                $path,
                ['media' => 'all', 'priority' => 1000]
            );
        } catch (Throwable $t) {
            $this->handleException($t);
        }
    }
}
