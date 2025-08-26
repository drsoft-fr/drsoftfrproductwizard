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
                        'Your Selection' => $this->getContext()->getTranslator()->trans('Your Selection', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'No products selected yet.' => $this->getContext()->getTranslator()->trans('No products selected yet.', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'Select options from the configurator to build your product.' => $this->getContext()->getTranslator()->trans('Select options from the configurator to build your product.', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'Quantity' => $this->getContext()->getTranslator()->trans('Quantity', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'Total' => $this->getContext()->getTranslator()->trans('Total', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'Add to Cart' => $this->getContext()->getTranslator()->trans('Add to Cart', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'Please complete all steps before adding to cart.' => $this->getContext()->getTranslator()->trans('Please complete all steps before adding to cart.', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'Selected' => $this->getContext()->getTranslator()->trans('Selected', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'Select' => $this->getContext()->getTranslator()->trans('Select', [], 'Modules.Drsoftfrproductwizard.Global'),
                        'Quantity:' => $this->getContext()->getTranslator()->trans('Quantity:', [], 'Modules.Drsoftfrproductwizard.Global'),
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

            $values['routes']['addToCart'] = $this->getContext()
                ->link
                ->getModuleLink(
                    $this->module->name,
                    'ajax',
                    [
                        'action' => 'add-to-cart',
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
