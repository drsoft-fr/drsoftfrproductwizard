<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Controller\Hook;

use DrSoftFr\Module\ProductWizard\Config;
use DrSoftFr\PrestaShopModuleHelper\Controller\Hook\AbstractHookController;
use DrSoftFr\PrestaShopModuleHelper\Controller\Hook\HookControllerInterface;
use Exception;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use Throwable;

final class ActionFrontControllerSetVariablesController extends AbstractHookController implements HookControllerInterface
{
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

    public function run(): array
    {
        try {
            $imageRetriever = new ImageRetriever($this->getContext()->link);
            $values = [
                'routes' => [],
                'messages' => [
                    'Modules.Drsoftfrproductwizard.Global' => [
                        'Loading...' => $this->getContext()->getTranslator()->trans('Loading...', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'Loading configurator options...' => $this->getContext()->getTranslator()->trans('Loading configurator options...', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'No configuration options available.' => $this->getContext()->getTranslator()->trans('No configuration options available.', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'Skip this step without selecting any products.' => $this->getContext()->getTranslator()->trans('Skip this step without selecting any products.', [], 'Modules.Drsoftfrproductwizard.Global'),
                    ],
                    'Modules.Drsoftfrproductwizard.Error' => [
                        'Failed to load configurator' => $this->getContext()->getTranslator()->trans('Failed to load configurator', [], 'Modules.Drsoftfrproductwizard.Error'),
                        'Error fetching configurator: %error%' => $this->getContext()->getTranslator()->trans('Error fetching configurator: %error%', [], 'Modules.Drsoftfrproductwizard.Error'),
                        'An error occurred while loading the configurator' => $this->getContext()->getTranslator()->trans('An error occurred while loading the configurator', [], 'Modules.Drsoftfrproductwizard.Error'),
                    ],
                ],
                'noPictureImage' => $imageRetriever->getNoPictureImage($this->getContext()->language),
                'error' => false,
            ];

            $values['routes']['getConfigurator'] = $this->getContext()
                ->link
                ->getModuleLink(
                    $this->module->name,
                    'ajax',
                    [
                        'action' => 'get-configurator',
                        'ajax' => true,
                    ]
                );
        } catch (Throwable $t) {
            $this->handleException($t);
        } finally {
            return $values ?: [];
        }
    }
}
