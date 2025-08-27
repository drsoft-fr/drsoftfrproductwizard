<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\UI\Hook\Controller;

use DrSoftFr\Module\ProductWizard\Config;
use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use DrSoftFr\Module\ProductWizard\Repository\ConfiguratorRepository;
use DrSoftFr\PrestaShopModuleHelper\Controller\Hook\AbstractHookController;
use DrSoftFr\PrestaShopModuleHelper\Controller\Hook\HookControllerInterface;
use Exception;
use Throwable;

final class ActionOutputHTMLBeforeController extends AbstractHookController implements HookControllerInterface
{
    private const CONFIGURATOR_REPOSITORY = 'drsoft_fr.module.product_wizard.repository.configurator_repository';
    private const WIZARD_TAG_PATTERN = '/\[drsoft-fr-product-wizard id="(\d+)"]/';
    private const SCRIPT_TEMPLATE = '
        <div>
          <script>
              window.drsoftfrproductwizard = window.drsoftfrproductwizard || {}
              window.drsoftfrproductwizard.data = window.drsoftfrproductwizard.data || {}
              window.drsoftfrproductwizard.data[%d] = %s
          </script>
        </div>
        <div class="js-drsoft-fr-product-wizard" data-configurator="%d"></div>
    ';

    /**
     * Checks if the object is valid.
     *
     * @return bool True if the object is valid, false otherwise.
     */
    private function checkObject(): bool
    {
        if (empty($this->props['html']) || !isset($this->props['html'])) {
            return false;
        }

        return true;
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
            if (false === $this->checkObject()) {
                return '';
            }

            $this->props['html'] = preg_replace_callback(
                self::WIZARD_TAG_PATTERN,
                [$this, 'replace'],
                $this->props['html']
            );

            return $this->props['html'];
        } catch (Throwable $t) {
            $this->handleException($t);

            return '';
        }
    }

    /**
     * @param array $matches
     *
     * @return string
     *
     * @throws Exception
     */
    private function replace(array $matches): string
    {
        $configuratorId = (int)$matches[1];

        if (0 >= $configuratorId) {
            return '';
        }

        $repository = $this->getRepository();

        /** @var Configurator $obj */
        $obj = $repository->findOneBy([
            'id' => $configuratorId,
            'active' => true
        ]);

        if (null === $obj) {
            return '';
        }

        return $this->generateScriptTag($configuratorId, $obj->toArray());
    }

    /**
     * Generates the script tag for the configurator.
     *
     * @param int $configuratorId Configurator ID.
     * @param array $data Configurator data.
     *
     * @return string
     */
    private function generateScriptTag(int $configuratorId, array $data): string
    {
        return sprintf(self::SCRIPT_TEMPLATE, $configuratorId, json_encode($data), $configuratorId);
    }


    /**
     * @throws Exception
     */
    private function getRepository(): ConfiguratorRepository
    {
        /** @type ConfiguratorRepository */
        return $this->module->get(self::CONFIGURATOR_REPOSITORY);
    }
}
