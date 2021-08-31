# Модуль ConcordPay для Prestashop

## Установка
 
1. Извлечь из архива с модулем папку **concordpay** и поместить её в каталог *{YOUR_SITE}/modules*.

2. В административной части сайта зайти в раздел *«Modules -> Module catalog»*.

3. Найти модуль **«ConcordPay Payment Gateway»** и нажать кнопку *«Установить» («Install»)*.

4. Перейти в раздел *«Modules -> Module manager»*, из выпадающего списка *«Category»* выбрать *«Payment»*.

5. Зайти в настройки модуля, указать данные вашего продавца, полученные от платёжной системы, и сохранить изменения настроек.
    - *Идентификатор продавца (Merchant ID)*;
    - *Секретный ключ (Secret Key)*;
    - Статусы заказа на различных этапах его существования;
    - Язык страницы оплаты **ConcordPay**.

Модуль готов к работе.

*Модуль протестирован для работы с Prestashop 1.7.7.5 и PHP 7.2.*
