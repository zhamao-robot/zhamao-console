<?php


namespace ZM\Console;


use Swoole\Atomic;

class Console
{
    private static $server = null;

    private static $info_level = null;

    public static $theme = "default";

    private static $default_theme = [
        "success" => "green",
        "info" => "lightblue",
        "warning" => "yellow",
        "error" => "red",
        "verbose" => "blue",
        "debug" => "gray",
        "trace" => "gray"
    ];

    private static $theme_config = [];

    /**
     * 初始化服务器的控制台参数
     * @param $server
     * @param int $info_level
     * @param string $theme
     * @param array $theme_config
     */
    public static function init(int $info_level, $server = null, $theme = "default", $theme_config = []) {
        self::$server = $server;
        self::$info_level = new Atomic($info_level);
        self::$theme = $theme;
        self::$theme_config = $theme_config;
    }

    public static function setLevel(int $level) {
        if (self::$info_level === null) self::$info_level = new Atomic($level);
        self::$info_level->set($level);
    }

    public static function getLevel() {
        return self::$info_level->get();
    }

    public static function setColor($string, $color = "") {
        $string = self::stringable($string);
        switch ($color) {
            case "black":
                return TermColor::color8(30) . $string . TermColor::RESET;
            case "red":
                return TermColor::color8(31) . $string . TermColor::RESET;
            case "green":
                return TermColor::color8(32) . $string . TermColor::RESET;
            case "yellow":
                return TermColor::color8(33) . $string . TermColor::RESET;
            case "blue":
                return TermColor::color8(34) . $string . TermColor::RESET;
            case "pink": // I really don't know what stupid color it is.
            case "lightpurple":
                return TermColor::color8(35) . $string . TermColor::RESET;
            case "lightblue":
                return TermColor::color8(36) . $string . TermColor::RESET;
            case "white":
                return TermColor::color8(37) . $string . TermColor::RESET;
            case "gold":
                return TermColor::frontColor256(214) . $string . TermColor::RESET;
            case "gray":
                return TermColor::frontColor256(59) . $string . TermColor::RESET;
            case "lightlightblue":
                return TermColor::frontColor256(63) . $string . TermColor::RESET;
            case "":
                return $string;
            default:
                return TermColor::frontColor256($color) . $string . TermColor::RESET;
        }
    }

    public static function error($obj, $head = null) {
        if ($head === null) $head = self::getHead("E");
        if (self::$info_level !== null && in_array(self::$info_level->get(), [3, 4])) {
            $trace = debug_backtrace()[1] ?? ['file' => '', 'function' => ''];
            $trace = "[" . ($trace["class"] ?? '') . ":" . ($trace["function"] ?? '') . "] ";
        }
        $obj = self::stringable($obj);
        echo(self::setColor($head . ($trace ?? "") . $obj, self::getThemeColor(__FUNCTION__)) . "\n");
    }

    public static function trace($color = null) {
        $log = "Stack trace:\n";
        $trace = debug_backtrace();
        //array_shift($trace);
        foreach ($trace as $i => $t) {
            if (!isset($t['file'])) {
                $t['file'] = 'unknown';
            }
            if (!isset($t['line'])) {
                $t['line'] = 0;
            }
            if (!isset($t['function'])) {
                $t['function'] = 'unknown';
            }
            $log .= "#$i {$t['file']}({$t['line']}): ";
            if (isset($t['object']) and is_object($t['object'])) {
                $log .= get_class($t['object']) . '->';
            }
            $log .= "{$t['function']}()\n";
        }
        if ($color === null) $color = self::getThemeColor("trace");
        $log = Console::setColor($log, $color);
        echo $log;
    }

    public static function log($obj, $color = "") {
        $obj = self::stringable($obj);
        echo(self::setColor($obj, $color) . "\n");
    }

    public static function debug($msg) {
        if (self::$info_level !== null && self::$info_level->get() >= 4) {
            $msg = self::stringable($msg);
            $trace = debug_backtrace()[1] ?? ['file' => '', 'function' => ''];
            $trace = "[" . ($trace["class"] ?? '') . ":" . ($trace["function"] ?? '') . "] ";
            Console::log(self::getHead("D") . ($trace) . $msg, self::getThemeColor(__FUNCTION__));
        }
    }

    public static function verbose($obj, $head = null) {
        if ($head === null) $head = self::getHead("V");
        if (self::$info_level !== null && in_array(self::$info_level->get(), [4])) {
            $trace = debug_backtrace()[1] ?? ['file' => '', 'function' => ''];
            $trace = "[" . ($trace["class"] ?? '') . ":" . ($trace["function"] ?? '') . "] ";
        }
        if (self::$info_level !== null && self::$info_level->get() >= 3) {
            $obj = self::stringable($obj);
            echo(self::setColor($head . ($trace ?? "") . $obj, self::getThemeColor(__FUNCTION__)) . "\n");
        }
    }

    public static function success($obj, $head = null) {
        if ($head === null) $head = self::getHead("S");
        if (self::$info_level !== null && in_array(self::$info_level->get(), [4])) {
            $trace = debug_backtrace()[1] ?? ['file' => '', 'function' => ''];
            $trace = "[" . ($trace["class"] ?? '') . ":" . ($trace["function"] ?? '') . "] ";
        }
        if (self::$info_level->get() >= 2) {
            $obj = self::stringable($obj);
            echo(self::setColor($head . ($trace ?? "") . $obj, self::getThemeColor(__FUNCTION__)) . "\n");
        }
    }

    public static function info($obj, $head = null) {
        if ($head === null) $head = self::getHead("I");
        if (self::$info_level !== null && in_array(self::$info_level->get(), [4])) {
            $trace = debug_backtrace()[1] ?? ['file' => '', 'function' => ''];
            $trace = "[" . ($trace["class"] ?? '') . ":" . ($trace["function"] ?? '') . "] ";
        }
        if (self::$info_level->get() >= 2) {
            $obj = self::stringable($obj);
            echo(self::setColor($head . ($trace ?? "") . $obj, self::getThemeColor(__FUNCTION__)) . "\n");
        }
    }

    static function warning($obj, $head = null) {
        if ($head === null) $head = self::getHead("W");
        if (self::$info_level !== null && in_array(self::$info_level->get(), [4])) {
            $trace = debug_backtrace()[1] ?? ['file' => '', 'function' => ''];
            $trace = "[" . ($trace["class"] ?? '') . ":" . ($trace["function"] ?? '') . "] ";
        }
        if (self::$info_level->get() >= 1) {
            $obj = self::stringable($obj);
            echo(self::setColor($head . ($trace ?? "") . $obj, self::getThemeColor(__FUNCTION__)) . "\n");
        }
    }

    private static function getHead($mode) {
        $head = date("[H:i:s] ") . "[{$mode[0]}] ";
        if ((self::$server->setting["worker_num"] ?? 1) > 1 && self::$server->worker_id != -1) {
            $head .= "[#" . self::$server->worker_id . "] ";
        }
        return $head;
    }

    private static function getThemeColor(string $function) {
        return self::$theme_config[self::$theme][$function] ?? self::$default_theme[$function];
    }

    private static function stringable($str) {
        if (is_object($str) && method_exists($str, "__toString")) {
            return $str;
        } elseif (is_string($str) || is_numeric($str)) {
            return $str;
        } elseif (is_callable($str)) {
            return "{Closure}";
        } elseif (is_bool($str)) {
            return $str ? "*True*" : "*False*";
        } elseif (is_array($str)) {
            /** @noinspection PhpComposerExtensionStubsInspection */
            return json_encode($str, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS);
        } elseif (is_resource($str)) {
            return "{Resource}";
        } elseif (is_null($str)) {
            return "NULL";
        } else {
            return "{Not Stringable Object:" . get_class($str) . "}";
        }
    }

    public static function printProps(array $out, $tty_width) {
        $store = "";
        foreach ($out as $k => $v) {
            $line = $k . ": " . $v;
            if (strlen($line) > 19 && $store == "" || $tty_width < 53) {
                Console::log($line);
            } else {
                if ($store === "") $store = str_pad($line, 19, " ", STR_PAD_RIGHT);
                else {
                    $store .= (" |   " . $line);
                    Console::log($store);
                    $store = "";
                }
            }
        }
        if ($store != "") Console::log($store);
    }
}
