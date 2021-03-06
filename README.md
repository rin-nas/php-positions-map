# Упаковка/распаковка массивов (сериализация) в/из бинарный компактный формат, где каждое последующее числовое значение больше или равно предыдущему.

Пример массива:
```
$a = array(
    0 => 0,
    1 => 5,
    2 => 12,
    3 => 18,
    4 => 33,
    5 => 100,
    6 => 228,
    7 => 3256,
    8 => 3289,
    9 => 3311,
    10 => 3315,
    ...
);
```
Пример использования: массив распределения абсолютных позиций слов
к абсолютным байтовым позициям в нормализованном тексте.
Эти данные могут использоваться для подсветки найденных слов
во фрагменте текста в результатах поиска по ключевым словам.

Алгоритм -- дельта кодирование + UnaryBinaryNumeric + gzip.

## Примечание

UTF-8 не самый оптимальный, весь юникод можно закодировать более экономно:
```
  2^7  0xxxxxxx
  2^14 10xxxxxx xxxxxxxx
  2^21 110xxxxx xxxxxxxx xxxxxxxx
```
В итоге: 3 байта на символ максимум, 2^7 + 2^14 + 2^21 = 2 113 664 символов.

## Ссылки
* http://ru.wikipedia.org/wiki/UTF-8
* http://ru.wikipedia.org/wiki/.ape
* http://users.omsknet.ru/kolpash/TTA/index_ru.htm
