
## General 


| **Plugin** | **PointAPI** |
| :-----: | :-----: |
| **API** | **4.0.0** |


## Feature

- That is new currency unit for PocketMine beyond EconomyAPI 

<br>

## PointAPI Command
| Command | Description | Aliases | Default Permission |
| :-----: | :-------: | :---------: | :-------: |
| /mypoint | Show your `Point` | /mypoint | `All` |
| /setpoint | Set `player` `point` to `point`| /setpoint `<player>` `<point>` | `All` `Console` |
| /paypoint | Pay `point` to other `player` | /paypoint `<player>` | `All` |
| /givepoint | Give `point` to `player` | /givepoint `<player>` `<point>` | `OP` `Console` |
| /mystatuspoint | Show your status point | /mystatuspoint | `OP` `Console` |
| /setlangpoint | Set language for plugin | /setlangpoint | `OP` `Console` |
| /takepoint | Take `point` from `player` | /takepoint `<player>` `<point>` | `OP` `Console` |
| /seepoint | See your or other player's points | /seepoint `<player>` | `OP` `Console` |


## For Developers

You can access to PointAPI using `PointAPI::getInstance()`

Basic Usage:

```php

PointAPI::getInstance()->addPoint($player, $amount);

```

Currency specified:

```php

$api = PointAPI::getInstance();

$currency = $api->getDefaultCurrency();

$api->addPoint($player, $amount, $currency);

```



