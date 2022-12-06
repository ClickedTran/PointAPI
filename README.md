## General 
|**Plugins** | **PointAPI** |
| -- | -- |
|**API** | **4.0.0** |
<br>
- That is new currency unit for PocketMine beyond EconomyAPI 
<br>

## PointAPI Command
| Default command | Parameter | Description | Default Permission |

| :-----: | :-------: | :---------: | :-------: |

| /mypoint | | Shows your money | `All` |

| /toppoint | `<page>` | Shows server's top money | `All` |

| /setpoint | `<player>` `<point>` | Sets `<player>`'s money to `<money>` | `OP` `Console` |

| /givepoint | `<player>` `<point>` | Gives `<point>` `<player>` | `OP` `Console` |

| /takepoint | `<player>` `<point>` | Takes `<point>` from `<player>` | `OP` `Console` |

| /seepoint | `<player>` | Shows `<player>`'s point | `All` |

| /mystatus | | Shows your point status | `All` |
| /paypoint | | Transfer `<points>` to other `<players>` | | `All`|

## For Developers

You can access to EconomyAPI using `EconomyAPI::getInstance()`

Basic Usage:

```php

EconomyAPI::getInstance()->addMoney($player, $amount);

```

Currency specified:

```php

$api = EconomyAPI::getInstance();

$currency = $api->getDefaultCurrency();

$api->addMoney($player, $amount, $currency);

```
