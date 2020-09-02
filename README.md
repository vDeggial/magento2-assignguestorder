# Assign Guest Order to Customer - Magento 2 Module

Upon placement of Guest Order, this module will check if a Customer with the same email address exists and try to assign the order to that Customer

## Requirements

*   Magento 2.x.x
*   [Hapex Core module](https://gitlab.com/deggial/magento2-core)

## Installation

*   Upload files to `Magento Home Directory`
*   Run `php bin/magento setup:upgrade` in CLI
*   Run `php bin/magento setup:di:compile` in CLI
*   Run `php bin/magento setup:static-content:deploy -f` in CLI
*   Run `php bin/magento cache:flush` in CLI
