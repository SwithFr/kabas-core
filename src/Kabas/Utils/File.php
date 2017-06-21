<?php

namespace Kabas\Utils;

use Kabas\App;
use Kabas\Exceptions\JsonException;
use Kabas\Exceptions\FileNotFoundException;

class File
{
    /**
     * Get the contents of a json file
     * @param  string $file
     * @return object
     */
    static function loadJson($file)
    {
        if(!file_exists($file)) throw new FileNotFoundException($file);
        $string = file_get_contents($file);
        $json = json_decode($string);
        if(!$json) throw new JsonException($file, $string);
        return $json;
    }

    /**
     * Get the contents of a json file without throwing exceptions
     * @param  string $file
     * @return object|null
     */
    static function loadJsonIfValid($file)
    {
        try {
            $content = self::loadJson($file);
        } catch (\Exception $e) {
            return;
        }
        return $content;
    }

    /**
     * Writes data to a json file.
     * @param  mixed $data
     * @param  string $path
     * @return void
     */
    static function writeJson($data, $path)
    {
        self::write(json_encode($data, JSON_PRETTY_PRINT), $path . '.json');
    }

    /**
     * Write data to a file
     * @param  string $data
     * @param  string $path
     * @return void
     */
    static function write($data, $path)
    {
        file_put_contents($path, $data);
    }

    /**
     * Get the contents of a file
     * @param  string $path
     * @return string
     */
    static function read($path)
    {
        return file_get_contents($path);
    }

    static function deleteJson($path)
    {
        unlink($path . '.json');
    }

    /**
     * Get directory and subdirectory structure
     * @param  string $path
     * @return array
     */
    static function parseDirectory($path)
    {
        $data = scandir($path);
        $items = [];

        foreach($data as $item) {
            if($item !== '.' && $item !== '..') {
                if(is_dir($path . DS . $item)) {
                    $items[$item] = self::parseDirectory($path . DS . $item);
                } else {
                    $items[] = $item;
                }
            }
        }
        return $items;
    }

    /**
     * Returns an associative array containing
     * content from all valid JSON files for given directory
     * @param  string $path
     * @param  boolean $recursive
     * @return array
     */
    static function loadJsonFromDir($path, $recursive = false)
    {
        $items = [];
        foreach (static::scanJsonFromDir($path, $recursive) as $file => $name) {
            if($content = static::loadJsonIfValid($file)) $items[$file] = $content;
        }
        return $items;
    }

    /**
     * Returns an associative array containing
     * all JSON files for given directory.
     * @param  string $path
     * @param  boolean $recursive
     * @return array
     */
    static function scanJsonFromDir($path, $recursive = false)
    {
        $items = [];
        if(!($path = realpath($path))) return $items;
        foreach (scandir($path) as $item) {
            if(in_array($item, ['.', '..'])) continue;
            $item = $path . DS . $item;
            if(is_dir($item)) {
                if($recursive) $items = array_merge($items, static::scanJsonFromDir($item, true));
                continue;
            }
            $info = pathinfo($item, PATHINFO_EXTENSION|PATHINFO_FILENAME);
            if($info['extension'] != 'json') continue;
            $items[$item] = $info['filename'];
        }
        return $items;
    }

    /**
     * Check if file has .json extension
     * @param  string  $path
     * @return boolean
     */
    static function isJson($path)
    {
        $path_parts = pathinfo($path);
        return $path_parts['extension'] === 'json';
    }

}
