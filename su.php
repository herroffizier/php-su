<?php
/**
 * PHP String Utils (php-su)
 * 
 * php-su представляет собой классс, объединивший в себе операции 
 * со строками, которых нет в PHP или которые некорректно работают 
 * с multibyte-кодировками.
 * Был написан давно и представляет собой своеобразную оду лени, 
 * поскольку единственная причина появления php-su
 * на свет заключается в крайнем нежелании писать для каждого
 * проекта рутину заново.
 * На оригинальность и эффективность не претендует.
 * 
 * @author Martin Stolz <herr.offizier@gmail.com>
 * @link https://github.com/herroffizier/php-su
 */

final class su
{
    protected static $sizeSuffixes = array(
            array('байт', 'байта', 'байт'),
            'Кб',
            'МБ',
            'ГБ',
            'ТБ',
            'ПБ',
    );

    protected static $intervalSuffixes = array(
        'd' => 86400,
        'h' => 3600,
        'm' => 60,
        's' => 1,
    );

    protected static $intervalSuffixFullNames = array(
        'd' => array(' день', ' дня', ' дней'),
        'h' => array(' час', ' часа', ' часов'),
        'm' => array(' минута', ' минуты', ' минут'),
        's' => array(' секунда', ' секунды', ' секунд'),
    );

    protected static $intervalSuffixShortNames = array(
        'd' => 'д',
        'h' => 'ч',
        'm' => 'м',
        's' => 'с',
    );

    protected static $translitParsed = false;
    protected static $translit = array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'ts',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sh',
            'ъ' => '',
            'ы' => 'i',
            'ь' => '',
            'э' => 'e',
            'ю' => 'ju',
            'я' => 'ya',
    );

    /**
     * Проверить, начинается ли строка $s с подстроки $p.
     *
     * @param  string $s
     * @param  string $p
     * @return bool
     */
    public static function startsWith($s, $p)
    {
        return mb_substr($s, 0, mb_strlen($p)) === $p;
    }

    /**
     * Проверить, заканчивается ли строка $s подстрокой $p.
     *
     * @param  string $s
     * @param  string $p
     * @return bool
     */
    public static function endsWith($s, $p)
    {
        return mb_substr($s, -mb_strlen($p)) === $p;
    }

    /**
     * Объединить две строки $s1 и $s2 в одну, используя
     * соединительную строку $p. При этом результирующая строка
     * на стыке $s1 и $s2 будет содержать ровно одну $p.
     *
     * Если необязательные аргументы $start и $end равны true,
     * строка $p будет добавлена в начале и конце результирующей
     * строки соответственно.
     *
     * Метод удобен при создании путей, например:
     *   su::glue(DIRECTORY_SEPARATOR, $path, $fileName);
     *
     * @param  string         $p
     * @param  string         $s1
     * @param  string         $s2
     * @param  bool[optional] $start
     * @param  bool[optional] $end
     * @return string
     */
    public static function glue($p, $s1, $s2, $start = false, $end = false)
    {
        if (
            self::endsWith($s1, $p)
            && self::startsWith($s2, $p)
        ) {
            $s = mb_substr($s1, 0, -mb_strlen($p)).$s2;
        } else {
            $s = $s1.(self::endsWith($s1, $p) || self::startsWith($s2, $p) ? '' : $p).$s2;
        }

        if ($start && !self::startsWith($s, $p)) $s = $p.$s;
        if ($end && !self::endsWith($s, $p)) $s .= $p;
        return $s;
    }

    /**
     * Сократить строку до $len символов.
     * Сокращение производится за счёт вырезания середины строки таким образом,
     * чтобы длина конечной строки была равна или меньше $len.
     * Левая и правая части строки объединяются строкой $separator, кроме того
     * в местах объединения строк удаляются пробелы.
     *
     * @param  string           $s
     * @param  integer          $len
     * @param  string[optional] $separator
     * @return string
     */
    public static function shorten($s, $len, $glue = '...')
    {
        $slen = mb_strlen($s);
        $glen = mb_strlen($glue);
        if ($len <= $glen) $len += $glen;

        if ($slen <= $len) return $s;

        $len -= $glen;
        $left = round($len / 2);
        $right = $len - $left;

        return rtrim(mb_substr($s, 0, $left)).$glue.ltrim(mb_substr($s, -$right));
    }

    /**
     * Находится ли строка в нижнем регистре.
     *     
     * @param  string  $s
     * @return boolean
     */
    public static function isLowercase($s)
    {
        return mb_strtolower($s) === $s;
    }

    /**
     * Находится ли строка в верхнем регистре.
     * 
     * @param  string  $s 
     * @return boolean
     */
    public static function isUppercase($s)
    {
        return mb_strtoupper($s) === $s;
    }

    /**
     * Привести первый символ строки к верхнему регистру.
     *
     * @param  string $s
     * @param  bool[optional] $ifNotLowercase
     * @return string
     */
    public static function ucfirst($s, $ifNotLowercase = false)
    {
        if ($ifNotLowercase && self::isLowercase($s)) return $s;
        return mb_strtoupper(mb_substr($s, 0, 1)).mb_substr($s, 1);
    }

    /**
     * Привести первый символ строки к нижнему регистру.
     * Флаг $ifNotUppercase нужен для того, чтобы не переводить первый символ
     * в нижний регистр, если вся строка находится в верхнем.
     * По умолчанию $ifNotUpperspace = false.
     *
     * @param  string $s
     * @param  bool[optional] $ifNotUppercase
     * @return string
     */
    public static function lcfirst($s, $ifNotUppercase = false)
    {
        if ($ifNotUppercase && self::isUppercase($s)) return $s;
        return mb_strtolower(mb_substr($s, 0, 1)).mb_substr($s, 1);
    }

    /**
     * Привести к верхнему регистру первый символ каждого слова в строке.
     *
     * @param  string $s
     * @param  integer[optional] $limit
     * @param  bool[optional] $ifNotLowercase
     * @return string
     */
    public static function ucwords($s, $limit = -1, $ifNotLowercase = false)
    {
        return preg_replace_callback('/\w+/u', function ($matches) use ($ifNotLowercase) { 
            return su::ucfirst($matches[0], $ifNotLowercase); 
        }, $s, $limit);
    }

    /**
     * Привести к нижнему регистру первый символ каждого слова в строке.
     *
     * @param  string $s
     * @param  integer[optional] $limit
     * @param  bool[optional] $ifNotUppercase
     * @return string
     */
    public static function lcwords($s, $limit = -1, $ifNotUppercase = false)
    {
        return preg_replace_callback('/\w+/u', function ($matches) use ($ifNotUppercase) {
            return su::lcfirst($matches[0], $ifNotUppercase);
        }, $s, $limit);
    }

    /**
     * Обрезать строку по ближайшему справа от позиции $len пробелу
     * и добавить к концу результирующей строки $append.
     * Если пробел не найден, будет выведена вся строка.
     *
     * @param  string           $s
     * @param  integer          $len
     * @param  string[optional] $append
     * @return string
     */
    public static function cutOnSpace($s, $len, $append = '...')
    {
        $slen = mb_strlen($s);

        if ($slen <= $len) {
            return $s;
        }

        $pos = mb_strpos($s, ' ', $len);
        if ($pos === false) {
            $pos = $slen;
        }

        return mb_substr($s, 0, $pos).$append;
    }

    /**
     * Выбрать подходящую для числа $number словоформу из $cases.
     * $cases имеет вид:
     *   array(
     *     'штука',
     *     'штуки',
     *     'штук'
     *   );
     *
     * @param  string $number
     * @param  array  $cases
     * @return string
     */
    public static function caseForNumber($number, array $cases)
    {
        $number = abs($number % 100);
        if ($number > 10 && $number < 20) return $cases[2];

        $number = $number % 10;

        return $cases[(int) ($number !== 1) + (int) ($number >= 5 || !$number)];
    }

    /**
     * Транслитерировать строку.
     *
     * @param  string $s
     * @return string
     */
    public static function translit($s)
    {
        if (!self::$translitParsed) {
            $upper = array();
            foreach (self::$translit as $rus => $lat) {
                $upper[mb_strtoupper($rus, 'UTF-8')] = ucfirst($lat);
            }

            self::$translit = array_merge(self::$translit, $upper);
            self::$translitParsed = true;
        }

        return
            str_replace(
                array_keys(self::$translit),
                array_values(self::$translit),
                $s
            );
    }

    /**
     * Получить размер файла с единицами измерения.
     * Если размер дробный, он округляется до первого разряда после запятой.
     *
     * @param  integer $size
     * @return string
     */
    public static function fileSize($size)
    {
        $maxIndex = count(self::$sizeSuffixes) - 1;
        foreach (self::$sizeSuffixes as $index => $cases) {
            if ($size > 1024 && $index < $maxIndex) {
                $size /= 1024;
                continue;
            }

            $size = ($size - floor($size) === 0.0) ? $size : round($size, 1);

            return $size.' '.(is_array($cases) ? self::caseForNumber($size, $cases) : $cases);
        }
    }

    /**
     * Получить web-safe имя файла.
     * Во-первых исходное имя файла будет транслитерировано,
     * во-вторых все пробелы будут заменены на дефис,
     * в-третьих все символы, не входящие в набор a-z0-9._- будут
     * удалены.
     *
     * @param  string $s
     * @return string
     */
    public static function fileName($s)
    {
        return
            preg_replace(
                array('/\s+/', '/[^a-z0-9\._-]/i'),
                array('-', ''),
                self::translit($s)
            );
    }

    /**
     * Получить человекопонятное представление интервала в секундах.
     * Представление может быть в двух видах - полном (1 час 3 минуты 5 секунд)
     * или сокращённом (1ч 3м 5с).
     * По умолчанию используется первый вариант.
     * 
     * @param  int  $interval
     * @param  boolean[optional] $short
     * @return string
     */
    public static function duration($interval, $short = false)
    {
        $names = 
            $short 
                ? self::$intervalSuffixShortNames 
                : self::$intervalSuffixFullNames;

        $result = array();
        foreach (self::$intervalSuffixes as $index => $size) {
            if ($interval < $size) continue;

            $name = $names[$index];
            $left = $interval % $size;
            $current = ($interval - $left) / $size;
            $result[] = $current.(is_array($name) ? self::caseForNumber($current, $name) : $name);
            $interval = $left;
        }

        return implode(' ', $result);
    }

    /**
     * Проверить, является ли строка URL.
     * Допустимы следующие шаблоны:
     *   scheme://host/path
     *   scheme://host/
     *   scheme://host
     *   host/path
     *   host/
     *   host
     *
     * При этом scheme может состоять только из латиницы и цифр,
     * host может состоять из произвольных букв, цифр и некоторых спецсимволов (.-),
     * а path может содержать любые символы, кроме пробела.
     *
     * FIXME в host допускается наличие _
     *
     * @param  string $s
     * @return bool
     */
    public static function isUrl($s)
    {
        return (bool) preg_match('/^([a-z0-9]+:\/\/|)(\w+|\w[\w\.-]*\w)(\/\S*|)$/u', $s);
    }

    /**
     * Проверить, является ли строка e-mail.
     * Фактически проверяется только отсутствие пробелов в строке
     * и наличие символа @ внутри строки.
     *
     * @param  string $s
     * @return bool
     */
    public static function isEmail($s)
    {
        return (bool) preg_match('/^[\w\.-]+@[\w\.-]+$/u', $s);
    }

    /**
     * @param  string $s
     * @return string
     */
    protected static function preparePhone($s)
    {
        return preg_replace('/[\s\+\(\)-]+/u', '', $s);
    }

    /**
     * Проверить, является ли строка телефонным номером.
     * Для проверки из строки удаляются пробелы, дефисы, плюсы и скобки
     * и сравнивается длина полученной строки с предельными длинами,
     * указанными в аргументах.
     *
     * @param  string            $s
     * @param  integer[optional] $minLen
     * @param  integer[optional] $maxLen
     * @return bool
     */
    public static function isPhone($s, $minLen = 7, $maxLen = 11)
    {
        $len = mb_strlen(self::preparePhone($s));

        return ($len >= $minLen && $len <= $maxLen);
    }

    /**
     * Привести URL к стандартному виду.
     * Перед изменением URL строка проверяется через su::isUrl
     * и если проверка не пройдена, возвращается null.
     * Если у исходного URL не указана схема, подставляется схема
     * из аргумента $scheme.
     *
     * @param  string           $s
     * @param  string[optional] $scheme
     * @return string
     */
    public static function normalizeUrl($s, $scheme = 'http')
    {
        if (!self::isUrl($s)) return null;

        if (!preg_match('/[a-z0-9]+:\/\//u', $s)) $s = $scheme.'://'.$s;
        return $s;
    }

    /**
     * Привести URL к удобочитаемому виду.
     * Перед изменением URL строка проверяется через su::isUrl
     * и если проверка не пройдена, возвращается null.
     * Во-первых, декодируются все последовательности %xx.
     * Во-вторых, убирается схема, если она имелась в наличии.
     * В-третьих, путь сокращается до $len символов (хост выводится в любом случае полностью).
     *
     * @param  string            $s
     * @param  integer[optional] $len
     * @return string
     */
    public static function beautifyUrl($s, $len = 15)
    {
        if (!self::isUrl($s)) return null;

        $s = rawurldecode($s);

        return preg_replace_callback('/([a-z0-9]+:\/\/|)([^\/]+)(.*)/', function ($matches) use ($len) {
            return $matches[2].(mb_strlen($matches[3]) === 1 ? '' : su::shorten($matches[3], $len));
        }, $s);
    }

    /**
     * Обработать все URL в строке.
     * По умолчанию все найденные ссылки превращаются в HTML-ссылки.
     * Поведение по умолчанию применимо только для plain text'а, с HTML будут проблемы,
     * т.к. никаких проверок на конструкции вида <a href="http://link/"> нет.
     *
     * @param  string             $s
     * @param  callable[optional] $callback
     * @return string
     */
    public static function parseUrls($s, $callback = null)
    {
        if (!$callback) {
            $callback = function ($s) {
                if (!su::isUrl($s)) return $s;
                return '<a href="'.htmlspecialchars(su::normalizeUrl($s)).'">'.htmlspecialchars(su::beautifyUrl($s)).'</a>';
            };
        }

        return preg_replace_callback('/(?<=\s|^)([a-z0-9]+:\/\/|)([\w\.-]+\.\w+)(\/\S*[^\.\!\?\,\s]|)(?!@)(?=[\s\.\!\?\,\s]|$)/u', function ($matches) use ($callback) {
            return call_user_func_array($callback, array($matches[0]));
        }, $s);
    }

}
