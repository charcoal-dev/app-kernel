<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support;

use Charcoal\Base\Support\Helpers\DtoHelper;
use Charcoal\Filesystem\Exceptions\FilesystemException;
use Charcoal\Filesystem\Exceptions\PathNotFoundException;
use Charcoal\Filesystem\Exceptions\PathTypeException;
use Charcoal\Filesystem\Exceptions\PermissionException;
use Charcoal\Filesystem\Node\FileNode;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Filesystem\Path\FilePath;

/**
 * A helper class for processing and decoding JSON data with support for importing nested structures
 * from directories and various safety checks.
 * @api
 */
abstract readonly class JsonHelper
{
    /**
     * @throws FilesystemException
     * @throws \JsonException
     * @api
     */
    public static function jsonDecodeImports(
        DirectoryPath $directory,
        string        $jsonFile,

    ): mixed
    {
        $seed = self::decodeJsonFromDirectory($directory, $jsonFile);
        if (is_scalar($seed) || is_null($seed)) {
            return $seed;
        }

        $dto = DtoHelper::createFrom($seed);
        if (!$dto) {
            return $dto;
        }

        return self::scan($seed, $dto, $directory);
    }

    /**
     * @throws FilesystemException
     * @throws \JsonException
     */
    private static function scan(object|array $depth, array $dto, DirectoryPath $directory): array
    {
        $scanned = [];
        foreach (array_keys($dto) as $key) {
            $vo = is_object($depth) ? $depth->{$key} : $depth[$key];
            if (is_scalar($vo) || is_null($vo)) {
                $scanned[$key] = $vo;
                continue;
            }

            if (is_array($vo)) {
                for ($i = 0; $i < count($vo); $i++) {
                    if (is_scalar($vo[$i]) || is_null($vo[$i])) {
                        $scanned[$key][] = $vo[$i];
                        continue;
                    }

                    if (is_array($vo[$i]) || is_object($vo[$i])) {
                        $scanned[$key][] = self::scan($vo[$i], $dto[$key][$i], $directory);
                    }
                }

                continue;
            }

            if (is_object($vo)) {
                $buffer = null;
                if (array_key_exists("\$imports", $dto[$key])) {
                    $importer = $vo;
                    $baggage = self::jsonImporter($directory, $importer);
                    if ($baggage) {
                        if (empty((array)$importer)) {
                            $scanned[$key] = $baggage;
                            continue;
                        }
                    }

                    $dto[$key] = DtoHelper::createFrom($importer);
                }

                if ($buffer === null) {
                    $buffer = $vo;
                }

                foreach (self::scan($buffer, $dto[$key], $directory) as $ck => $cv) {
                    $scanned[$key][$ck] = $cv;
                }
            }
        }

        return $scanned;
    }

    /**
     * @throws FilesystemException
     * @throws \JsonException
     */
    private static function jsonImporter(DirectoryPath $directory, object $importer): array|object
    {
        $imports = $importer->{"\$imports"} ?? null;
        if (!is_array($imports) || !$imports) {
            return [];
        }

        $buffer = [];
        foreach ($imports as $import) {
            $buffer[] = self::decodeJsonFromDirectory($directory, $import);
        }

        unset($importer->{"\$imports"});
        $baggage = [];
        foreach ($buffer as $imported) {
            if (is_scalar($imported) || is_null($imported)) {
                $baggage[] = $imported;
                continue;
            }

            if (is_array($imported)) {
                if (array_is_list($imported)) {
                    $baggage = [...$baggage, ...$imported];
                } else {
                    foreach (array_keys(DtoHelper::createFrom($imported, 1)) as $item) {
                        if (is_string($item) && str_starts_with($item, "$")) {
                            continue;
                        }
                        if (property_exists($importer, (string)$item)
                            && is_array($importer->{$item})
                            && is_array($imported->{$item})) {
                            $importer->{$item} = array_is_list($importer->{$item}) && array_is_list($imported->{$item})
                                ? array_values([...$importer->{$item}, ...$imported->{$item}])
                                : array_replace_recursive($importer->{$item}, $imported->{$item});
                        } else {
                            $importer->{$item} = $imported->{$item};
                        }
                    }
                }

                continue;
            }

            if (is_object($imported)) {
                if (property_exists($imported, "\$imports") && is_array($imported->{"\$imports"})) {
                    self::jsonImporter($directory, $imported);
                }

                if (property_exists($imported, "\$imports")) {
                    unset($imported->{"\$imports"});
                }

                foreach (array_keys(DtoHelper::createFrom($imported, 1)) as $item) {
                    if (is_string($item) && str_starts_with($item, "$")) {
                        continue;
                    }
                    $importer->{$item} = $imported->{$item};
                }
            }
        }

        if (property_exists($importer, "\$imports")) {
            unset($importer->{"\$imports"});
        }

        return $baggage;
    }

    /**
     * @throws PermissionException
     * @throws FilesystemException
     * @throws \JsonException
     */
    public static function decodeJsonFromDirectory(DirectoryPath $directory, string $filename): mixed
    {
        return self::decodeJsonFromFile($directory->join($filename . ".json")->isFile());
    }

    /**
     * @throws PermissionException
     * @throws PathNotFoundException
     * @throws PathTypeException
     * @throws FilesystemException
     * @throws \JsonException
     */
    public static function decodeJsonFromFile(FileNode|FilePath $path): mixed
    {
        $path = match (true) {
            $path instanceof FileNode => $path->read(),
            default => $path->node()->read()
        };

        return json_decode(trim($path), false, flags: JSON_THROW_ON_ERROR);
    }
}