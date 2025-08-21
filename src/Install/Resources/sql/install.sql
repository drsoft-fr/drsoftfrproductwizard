CREATE TABLE IF NOT EXISTS `_DB_PREFIX_drsoft_fr_product_wizard_configurator`
(
    `id_configurator` INT(10) UNSIGNED              NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(255)                  NOT NULL,
    `reduction`       DECIMAL(20, 6)                NOT NULL DEFAULT 0,
    `reduction_tax`   TINYINT(1)                    NOT NULL DEFAULT 1,
    `reduction_type`  ENUM ('amount', 'percentage') NOT NULL DEFAULT 'amount',
    `active`          TINYINT(1) UNSIGNED           NOT NULL DEFAULT 1,
    `date_add`        DATETIME                      NOT NULL,
    `date_upd`        DATETIME                      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_configurator`)
) ENGINE = _MYSQL_ENGINE_
  DEFAULT CHARSET = utf8mb4
  AUTO_INCREMENT = 1;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_drsoft_fr_product_wizard_step`
(
    `id_step`         INT(10) UNSIGNED              NOT NULL AUTO_INCREMENT,
    `id_configurator` INT(10) UNSIGNED              NOT NULL,
    `label`           VARCHAR(255)                  NOT NULL,
    `reduction`       DECIMAL(20, 6)                NOT NULL DEFAULT 0,
    `reduction_tax`   TINYINT(1)                    NOT NULL DEFAULT 1,
    `reduction_type`  ENUM ('amount', 'percentage') NOT NULL DEFAULT 'amount',
    `position`        INT UNSIGNED                  NOT NULL DEFAULT 0,
    `active`          TINYINT(1) UNSIGNED           NOT NULL DEFAULT 1,
    `date_add`        DATETIME                      NOT NULL,
    `date_upd`        DATETIME                      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_step`),
    KEY `idx_dfpws_configurator` (`id_configurator`),
    CONSTRAINT `fk_dfpws_step_configurator`
        FOREIGN KEY (`id_configurator`) REFERENCES `_DB_PREFIX_drsoft_fr_product_wizard_configurator` (`id_configurator`)
            ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = _MYSQL_ENGINE_
  DEFAULT CHARSET = utf8mb4
  AUTO_INCREMENT = 1;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_drsoft_fr_product_wizard_product_choice`
(
    `id_product_choice`  INT(10) UNSIGNED              NOT NULL AUTO_INCREMENT,
    `id_step`            INT(10) UNSIGNED              NOT NULL,
    `label`              VARCHAR(255)                  NOT NULL,
    `id_product`         INT(10) UNSIGNED                       DEFAULT NULL,
    `is_default`         TINYINT(1) UNSIGNED           NOT NULL DEFAULT 0,
    `allow_quantity`     TINYINT(1) UNSIGNED           NOT NULL DEFAULT 1,
    `forced_quantity`    INT UNSIGNED                           DEFAULT NULL,
    `min_quantity`       INT UNSIGNED                           DEFAULT NULL,
    `max_quantity`       INT UNSIGNED                           DEFAULT NULL,
    `display_conditions` JSON                                   DEFAULT NULL,
    `reduction`          DECIMAL(20, 6)                NOT NULL DEFAULT 0,
    `reduction_tax`      TINYINT(1)                    NOT NULL DEFAULT 1,
    `reduction_type`     ENUM ('amount', 'percentage') NOT NULL DEFAULT 'amount',
    `active`             TINYINT(1) UNSIGNED           NOT NULL DEFAULT 1,
    `date_add`           DATETIME                      NOT NULL,
    `date_upd`           DATETIME                      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_product_choice`),
    KEY `idx_dfpwc_step` (`id_step`),
    KEY `idx_dfpwc_product` (`id_product`),
    CONSTRAINT `fk_dfpwc_product_choice_step`
        FOREIGN KEY (`id_step`) REFERENCES `_DB_PREFIX_drsoft_fr_product_wizard_step` (`id_step`)
            ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_dfpwc_product_choice_product`
        FOREIGN KEY (`id_product`) REFERENCES `_DB_PREFIX_product` (`id_product`)
            ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = _MYSQL_ENGINE_
  DEFAULT CHARSET = utf8mb4
  AUTO_INCREMENT = 1;
