## General 
|**Plugins** | **PointAPI** |
| -- | -- |
|**API** | **4.0.0** |
<br>
- That is new currency unit for PocketMine beyond EconomyAPI 
<br>

## PointAPI Command
|**Command** | **Description** | **Aliases** | **Permission** |
| -- | -- |
|**/givepoint** | **Allows player to give point to others** | **/givepoint `<player>` `<amount>`** | **OP** |
| -- | -- |
|**/mypoint** | **Allows player to see his/her point** | **/mypoint** | **DEFAULT** |
| -- | -- |
|**/setpoint** | **Allows to set player's point** | **/setpoint `<player>` `<amount>`** | **OP** |
| -- | -- |
|**/setlangpoint** | **Allows player to set his/her language** | **/setlangpoint `<language>`** | **OP** |
| -- | -- |
|**/seepoint** | **Allows player to see others' point** | **/seepoint `<player>`** | **OP** |
| -- | -- |
|**/toppoint** | **Allows player to see top point list** | **/toppoint** | **DEFAULT** |
| -- | -- |
|**/mystatuspoint** | **Allows player to see his/her financial status** | **/mystatuspoint** | **OP** |
| -- | -- |
|**/takepoint** | **Allows player to take point from others** | **/takepoint `<player>` `<amount>`** | **OP** |
| -- | -- |
|**/paypoint** | **Allows player to pay to someone** | **/paypoint `<player>` `<amount>`** | **DEFAULT** |
| -- | -- |

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
