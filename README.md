# Advanced Feedback Extension for Magento2

## Installation

Download the extension as a ZIP file from this repository and extract in magento Root directory.

Run below command
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

###
Install with composer
```
composer require krishaweb/feedback
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```
### Prerequise
Please make sure the magento default cron has set in store.
Follow below step to set cron 

```
*/1 * * * * php -c <ini-file-path> <your Magento install dir>/bin/magento cron:run

*/1 * * * * php -c <ini-file-path> <your Magento install dir>/update/cron.php

*/1 * * * * php -c <ini-file-path> <your Magento install dir>/bin/magento setup:cron:run
```

Follow below link for more info about cron.

https://devdocs.magento.com/guides/v2.2/config-guide/cli/config-cli-subcommands-cron.html



##Requirements
PHP >= 5.6
Magento >= 2.1


##Configuration
After installation has completed go to Stores > Settings > Configuration > Feedback > General Configuration

## Functionalities
Feedback is a magento 2 extension that enables the merchant to send remainder mail for feedback to customer.

The mail will be sent automatically after X days of order complete.

The days can be configured from admin.

Also admin can fire email anytime with the button in admin.


##License
Feedback's Magento 2 module is released under the Open Software License 3.0 (OSL-3.0).

##Support
If you have concerns or questions, please send an email to support@krishaweb.com with all relevant details that are needed to investigate or resolve an issue.










