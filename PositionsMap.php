<?php
/**
 * Упаковка/распаковка массивов (сериализация) в/из бинарный компактный формат,
 * где каждое последующее числовое значение больше или равно предыдущему.
 *
 * Пример массива:
 * $a = array(
 *     0 => 0,
 *     1 => 5,
 *     2 => 12,
 *     3 => 18,
 *     4 => 33,
 *     5 => 100,
 *     6 => 228,
 *     7 => 3256,
 *     8 => 3289,
 *     9 => 3311,
 *     10 => 3315,
 *     ...
 * );
 *
 * Пример использования: массив распределения абсолютных позиций слов
 * к абсолютным байтовым позициям в нормализованном тексте.
 * Эти данные могут использоваться для подсветки найденных слов
 * во фрагменте текста в результатах поиска по ключевым словам.
 *
 * Алгоритм -- дельта кодирование + UnaryBinaryNumeric + gzip.
 *
 * ПРИМЕЧАНИЕ
 *   UTF-8 не самый оптимальный, весь юникод можно закодировать более экономно:
 *   2^7  0xxxxxxx
 *   2^14 10xxxxxx xxxxxxxx
 *   2^21 110xxxxx xxxxxxxx xxxxxxxx
 *   В итоге: 3 байта на символ максимум, 2^7 + 2^14 + 2^21 = 2 113 664 символов.
 *
 * ССЫЛКИ
 *   http://ru.wikipedia.org/wiki/UTF-8
 *   http://ru.wikipedia.org/wiki/.ape
 *   http://users.omsknet.ru/kolpash/TTA/index_ru.htm
 *
 * @created  2009-08-12
 * @license  http://creativecommons.org/licenses/by-sa/3.0/
 * @author   https://github.com/rin-nas
 * @charset  UTF-8
 * @version  2.0.1
 */
class PositionsMap extends UnaryBinaryNumeric
{
	#запрещаем создание экземпляра класса, вызов методов этого класса только статически!
	private function __construct() {}

	/**
	 * Упаковка массива в строку
	 *
	 * @param   array  $a
	 * @return  string|bool  строка (бинарные данные) или FALSE в случае ошибки
	 */
	public static function pack($a)
	{
		if (! ReflectionTypeHint::isValid()) return false;
		if (! $a) return '';
		if (($a = self::_delta($a, $type = 1)) === false) return false;
		if (($s = parent::encode($a)) === false) return false;
		return gzcompress($s, 9);
	}

	/**
	 * Распаковка строки в массив
	 *
	 * @param   string|null  $s  строка (бинарные данные)
	 * @return  array|bool       FALSE if error occurred
	 */
	public static function unpack($s)
	{
		if (! ReflectionTypeHint::isValid()) return false;
		if ($s === '' or $s === null) return array();
		if (($s = gzuncompress($s)) === false) return false;
		if (($a = parent::decode($s)) === false) return false;
		return self::_delta($a, $type = 2);
	}

	#дельта кодирование/декодирование массива, где каждое последующее числовое значение >= предыдущему
	#$type = 1 -- кодирование, $type = 2 -- декодирование
	#возвращает массив или FALSE в случае ошибки
	private static function _delta(array $a, $type = 1)
	{
		if (! $a) return $a;
		if (! assert('is_int($a[0]) || ctype_digit($a[0])')) return false;
		if (! assert('$type === 1 || $type === 2')) return false;
		for ($i = 1, $c = count($a); $i < $c; $i++)
		{
			if (! assert('is_int($a[$i]) || ctype_digit($a[$i])')) return false;
			if ($type === 1)
			{
				#encode
				if ($a[$i] < $a[$i - 1])
				{
					trigger_error('Following number less previous!', E_USER_WARNING);
					return false;
				}
				$a[$i] = $a[$i] - $a[$i - 1];
			}
			#decode
			else $a[$i] += $a[$i - 1];
		}
		return $a;
	}

}
