<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Controller\Hook;

use DrSoftFr\Module\ProductWizard\Config;
use DrSoftFr\PrestaShopModuleHelper\Controller\Hook\AbstractHookController;
use DrSoftFr\PrestaShopModuleHelper\Controller\Hook\HookControllerInterface;
use Exception;
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
            $values = [
                'routes' => [],
                'data' => [],
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
