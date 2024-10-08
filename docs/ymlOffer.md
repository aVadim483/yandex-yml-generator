YmlOffer Class
============
Унаследован от класса [DomElement](http://php.net/manual/ru/class.domelement.php).

`protected $type`    - переменная, в которую в конструкторе записывается тип нашего Offer, т.е. 'упрощенный', '
произвольный', 'книги' и т.д.

`protected $permitted` - массив, в который в конструкторе записывается список разрешенных полей для данного типа
товарного предложения.

`protected $aliases` - в этом массиве записываются короткие имена для некоторых полей, например *origin* вместо
*country_of_origin*. Методы можно вызывать и по оригинальным названием полей. Но! Если псевдоним сделан вне этого
массива, то вызов возможен только по псевдониму.

----------------------

`__construct($type,$enc)`  - в нем мы только записываем тип предложения и какие функции разрешены для этого типа.
Переменная `$p` создавалась из таблицы [Обзор_полей.ods](Обзор_полей.ods). Некоторых полей здесь нет, они просто
объявлены как `public`. Если поле обязательное, то оно вообще прячется в конструкторе данного типа предложения в
*YmlDocument.php*, а здесь запрещается, чтобы повторно не вызвали.

----------------
Дальше идет небольшой блок полей, доступных всем предложениям. Некоторые записываются как атрибут поля `offer`.

`available($val=true)` - boolean, атрибут.

`bid($bid)` - integer, атрибут.

`cbid($cbid)` - integer, атрибут.

`fee($fee)` - integer, атрибут.

`url($url)` - не длиннее 512 символов.

`oldprice($oldprice)` - целое и положительное.

`dlvOption($cost,$days,$before = -1)` - добавляет опцию доставки, проверят их количество и типы. Невозможно вызвать
как `delivery-options` или `option`.

`description($txt,$tags = false)` - проверяет длину и добавляет, при необходимости через CDATA.

---------------

### Вот с этой функцией очень надо разобраться ###

`__call($method, $args)` - "волшебный метод", в котором спрятано всё разграничение полей между типами офферов. Сначала
мы расшифровываем псевдоним функции, если он есть. Потом проверяем, а разрешено ли данное поле у данного типа оффера.
Потом идут "значения, которые просто добавляем" - чтобы не создавать десятки однотипных функций без проверок, мы просто
добавляем поле по имени. Потом флаги, у которых тоже проверка только на boolean. Ну и если до сих пор мы не нашли ничего
нужного - значит этой функции нужны более сложные проверки, добавляем подчеркивание и вызываем.

---------------
Далее идут те самые функции с чуть более сложными проверками. Все они принимают **массив** входных параметров из
функции `__call()`. Также они все `protected`, чтобы нельзя было вызвать минуя ограничение.

`_minq($args)` - int>0, нельзя вызвать как min-quantity. Есть возможность вызвать для лекарств, которой тоже не должно
быть.

`_stepq($args)` - int>0, нельзя вызвать как step-quantity.

`_page_extent($args)` - int>0.

`_sales_notes($args)` - не длиннее 50 символов.

`_age($args)` - проверка на список допустимых значений.

`_param($args)` - нет проверок, но специфично добавляется.

`_picture( $args )` - проверка на количество и длину адреса.

`_group_id($args)` - int не длиннее 9 цифр.

`_barcode($args)` - список допустимых длин.

`_year($args)` - int.

`_dimensions($args)` - float 3 раза.

`_weight($args)` - float.

`_cpa($args)` - boolean, на выходе 0 и 1

------------
Далее несколько служебных функций.

`check($expr , $msg)` - сокращение для выкидывания исключений.

`addStr( $name,$val,$limit )` - добавить строчку с ограничением длины.

`add( $name,$val=false )` - просто добавить элемент.
