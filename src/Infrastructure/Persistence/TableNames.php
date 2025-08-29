<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Infrastructure\Persistence;

final class TableNames
{
    const CONFIGURATOR = _DB_PREFIX_ . 'drsoft_fr_product_wizard_configurator';
    const STEP = _DB_PREFIX_ . 'drsoft_fr_product_wizard_step';
    const PRODUCT_CHOICE = _DB_PREFIX_ . 'drsoft_fr_product_wizard_product_choice';
}
