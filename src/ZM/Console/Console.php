<?php


namespace ZM\Console;


use Swoole\Atomic;
use Swoole\Server;

class Console
{
    /** @var null|Server */
    private static $server = null;

    /** @var null|Atomic */
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
     * @param null|Server $server
     * @param int $info_level
     * @param string $theme
     * @param array $theme_config
     */
    public static function init(int $info_level, $server = null, $theme = "default", $theme_config = [])
    {
        self::$server = $server;
        self::$info_level = new Atomic($info_level);
        self::$theme = $theme;
        self::$theme_config = $theme_config;
    }

    public static function setLevel(int $level)
    {
        if (self::$info_level === null) self::$info_level = new Atomic($level);
        self::$info_level->set($level);
    }

    public static function getLevel()
    {
        return self::$info_level->get();
    }

    public static function setColor($string, $color = "")
    {
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

    public static function error($obj, $head = null)
    {
        if ($head === null) $head = self::getHead("E");
        if (self::$info_level !== null && in_array(self::$info_level->get(), [3, 4])) {
            $trace = debug_backtrace()[1] ?? ['file' => '', 'function' => ''];
            $trace = "[" . basename($trace["file"], ".php") . ":" . $trace["function"] . "] ";
        }
        if (!is_string($obj)) {
            if (isset($trace)) {
                var_dump($obj);
                return;
            } else $obj = "{Object}";
        }
        echo(self::setColor($head . ($trace ?? "") . $obj, self::getThemeColor(__FUNCTION__)) . "\n");
    }

    public static function trace($color = null)
    {
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

    public static function log($obj, $color = "")
    {
        if (!is_string($obj)) var_dump($obj);
        else echo(self::setColor($obj, $color) . "\n");
    }

    public static function debug($msg)
    {
        if (self::$info_level !== null && self::$info_level->get() >= 4) Console::log(self::getHead("D") . $msg, self::getThemeColor(__FUNCTION__));
    }

    public static function verbose($obj, $head = null)
    {
        if ($head === null) $head = self::getHead("V");
        if (self::$info_level !== null && self::$info_level->get() >= 3) {
            if (!is_string($obj)) {
                if (isset($trace)) {
                    var_dump($obj);
                    return;
                } else $obj = "{Object}";
            }
            echo(self::setColor($head . ($trace ?? "") . $obj, self::getThemeColor(__FUNCTION__)) . "\n");
        }
    }

    public static function success($obj, $head = null)
    {
        if ($head === null) $head = self::getHead("S");
        if (self::$info_level !== null && in_array(self::$info_level->get(), [1, 2])) {
            $trace = debug_backtrace()[1] ?? ['file' => '', 'function' => ''];
            $trace = "[" . basename($trace["file"], ".php") . ":" . $trace["function"] . "] ";
        }
        if (self::$info_level->get() >= 2) {
            if (!is_string($obj)) {
                if (isset($trace)) {
                    var_dump($obj);
                    return;
                } else $obj = "{Object}";
            }
            echo(self::setColor($head . ($trace ?? "") . $obj, self::getThemeColor(__FUNCTION__)) . "\n");
        }
    }

    public static function info($obj, $head = null)
    {
        if ($head === null) $head = self::getHead("I");
        if (self::$info_level !== null && in_array(self::$info_level->get(), [1, 2])) {
            $trace = debug_backtrace()[1] ?? ['file' => '', 'function' => ''];
            $trace = "[" . basename($trace["file"], ".php") . ":" . $trace["function"] . "] ";
        }
        if (self::$info_level->get() >= 2) {
            if (!is_string($obj)) {
                if (isset($trace)) {
                    var_dump($obj);
                    return;
                } else $obj = "{Object}";
            }
            echo(self::setColor($head . ($trace ?? "") . $obj, self::getThemeColor(__FUNCTION__)) . "\n");
        }
    }

    static function warning($obj, $head = null)
    {
        if ($head === null) $head = self::getHead("W");
        if (self::$info_level !== null && in_array(self::$info_level->get(), [1, 2])) {
            $trace = debug_backtrace()[1] ?? ['file' => '', 'function' => ''];
            $trace = "[" . basename($trace["file"], ".php") . ":" . $trace["function"] . "] ";
        }
        if (self::$info_level->get() >= 1) {
            if (!is_string($obj)) {
                if (isset($trace)) {
                    var_dump($obj);
                    return;
                } else $obj = "{Object}";
            }
            echo(self::setColor($head . ($trace ?? "") . $obj, self::getThemeColor(__FUNCTION__)) . "\n");
        }
    }

    private static function getHead($mode)
    {
        $head = date("[H:i:s] ") . "[{$mode}] ";
        if ((self::$server->setting["worker_num"] ?? 1) > 1) {
            $head .= "[#" . self::$server->worker_id . "] ";
        }
        return $head;
    }

    private static function getThemeColor(string $function)
    {
        return self::$theme_config[self::$theme][$function] ?? self::$default_theme[$function];
    }
}
