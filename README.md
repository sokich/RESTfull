# RESTfull
Restfull service for 220
controller - Вызов контроллера - объекта с которым хотим работать.
action - Вызов метода который должен выполнится, действие
PARAMETERS (опционально) — входные параметры соответствующего метода API, последовательность пар name=value, разделенных амперсандом. 
API_KEY - ключ доступа.


Параметры могут передаваться как методом GET, так и POST. Если вы будете передавать большие данные (больше 2 килобайт), следует использовать POST.

Код

Описание

50

Произошла неизвестная ошибка

101

Запись не найдена в БД 

300

Успешное удаление записи

301

Ошибка при удалении записи

400

Запись успешно добавлена

401

Не удалось произвести запись в таблицу

501

Не существующий контроллер

601

Не существующий экшен - действие класса 

701

Не правильный API KEY

 Далее запросы по заданию.

Добавления товара 
- http://220v.inwebit.ru/product/create?title=Test Product &vendor_id=2CIF&price=300&description=dfsdffdfasdfasdf&quantity=89&api_key=ASDad4qtDAS2t342

Удаление товара
- http://220v.inwebit.ru/product/delete?api_key=ASDad4qtDAS2t342&id=9965

Добавления бренда
- http://220v.inwebit.ru/vendor/create?title=Test vendor &vendor_id=2CIF&description=dfsdffdfasdfasdf&api_key=ASDad4qtDAS2t342

Удаление бренда
- http://220v.inwebit.ru/vendor/delete?api_key=ASDad4qtDAS2t342&id=45  

Выборки марок, имеющих > N товаров
- http://220v.inwebit.ru/product/getVendor?api_key=ASDad4qtDAS2t342&quantity=10000

Выборки всех товаров определенной марки.
- http://220v.inwebit.ru/product/getByMark?api_key=ASDad4qtDAS2t342&name=Fashion 

Выборка товаров определенной марки с конвертацией на доллары
-http://220v.inwebit.ru/product/getByMark?api_key=ASDad4qtDAS2t342&name=Fashion&currency=USD 

Выборка товаров определенной марки с конвертацией на KZT
- http://220v.inwebit.ru/product/getByMark?api_key=ASDad4qtDAS2t342&name=Fashion&currency=KZT

Выборка товаров определенной марки с конвертацией на Белорусские рубли
- http://220v.inwebit.ru/product/getByMark?api_key=ASDad4qtDAS2t342&name=Fashion&currency=BYN

Стандартно все выводится в рублях.
