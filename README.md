# Netresearch OPS

**Extension for Magento 1**

Tested for Magento 1.9.3.1

Module version 16.12.14

---

*Please note that we are not the developer of this extension. In this repository, we only added modman and composer support. We will not provide any support for this repository. If you have any problems on integration, please use the official link provided below.*

## Overview

This module is the official Ingenico ePayments extension.

For more information, please visit https://www.magentocommerce.com/magento-connect/official-ingenico-epayments-extension-1.html.

## Installation

Add the `require` and `repositories` sections to your composer.json as shown below and run `composer update`

```
{
    ...
    
    "repositories": [
    
        ...
        
        {"type": "git", "url": "https://github.com/kirchbergerknorr/magento1_netresearch_ops"},
        
        ...
    ],
	
    ...
	
    "require": {
        
        ...
        
        "kirchbergerknorr/magento1_netresearch_ops": "^16.12.14",
        
        ...
    },
    
    ...
}
```