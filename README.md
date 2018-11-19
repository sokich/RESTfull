# RESTfull
Restfull service for 220
controller - Вызов контроллера - объекта с которым хотим работать.
action - Вызов метода который должен выполнится, действие
PARAMETERS (опционально) — входные параметры соответствующего метода API, последовательность пар name=value, разделенных амперсандом. 
API_KEY - ключ доступа.


Параметры могут передаваться как методом GET, так и POST. Если вы будете передавать большие данные (больше 2 килобайт), следует использовать POST.

Добавления товара 
- http://220v.ru/product/create?title=Test Product &vendor_id=2CIF&price=300&description=dfsdffdfasdfasdf&quantity=89&api_key=ASDad4qtDAS2t342

Удаление товара
- http://220v.ru/product/delete?api_key=ASDad4qtDAS2t342&id=9965

Добавления бренда
- http://220v.ru/vendor/create?title=Test vendor &vendor_id=2CIF&description=dfsdffdfasdfasdf&api_key=ASDad4qtDAS2t342

Удаление бренда
- http://220v.ru/vendor/delete?api_key=ASDad4qtDAS2t342&id=45  

Выборки марок, имеющих > N товаров
- http://220v.ru/product/getVendor?api_key=ASDad4qtDAS2t342&quantity=10000

Выборки всех товаров определенной марки.
- http://220v.ru/product/getByMark?api_key=ASDad4qtDAS2t342&name=Fashion 

Выборка товаров определенной марки с конвертацией на доллары
-http://220v.ru/product/getByMark?api_key=ASDad4qtDAS2t342&name=Fashion&currency=USD 

Выборка товаров определенной марки с конвертацией на KZT
- http://220v.ru/product/getByMark?api_key=ASDad4qtDAS2t342&name=Fashion&currency=KZT

Выборка товаров определенной марки с конвертацией на Белорусские рубли
- http://220v.ru/product/getByMark?api_key=ASDad4qtDAS2t342&name=Fashion&currency=BYN

Стандартно все выводится в рублях.
