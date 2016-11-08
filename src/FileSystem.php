<?php
/**
 * Some methods like in Composer\Util\Filesystem because Composer too big.
 * @package GitFixture\Util
 */

namespace Cognitive\FileSystem;

/**
 * Class to work with filesystem.
 */
class FileSystem
{
    const MAKE_DIR_MODE_DEFAULT = 0777;
    /**
     * Empty directory content.
     *
     * @param string $directory             Path to directory.
     * @param bool   $ensureDirectoryExists If true then create empty directory.
     *
     * @return void
     */
    public function emptyDirectory($directory, $ensureDirectoryExists = true)
    {
        if ($ensureDirectoryExists) {
            $this->ensureDirectoryExists($directory);
        }

        if (is_dir($directory)) {
            $it = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } elseif ($file->isFile()) {
                    unlink($file->getRealPath());
                } elseif ($file->isLink()) {
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        rmdir($file->getPathName());
                    } else {
                        unlink($file->getPathName());
                    }
                }
            }
        }
    }

    /**
     * Ensure directory exists. Create if not.
     *
     * @param string $directory Path to directory.
     *
     * @return void
     * @throws FileSystemException If cannot be sure existing directory.
     * @since 0.0.2 introduced.
     */
    public function ensureDirectoryExists($directory)
    {
        if (!is_dir($directory)) {
            if (file_exists($directory)) {
                throw new FileSystemException($directory.' exists and is not a directory.');
            }
            if (!@mkdir($directory, self::MAKE_DIR_MODE_DEFAULT, true)) {
                throw new FileSystemException($directory.' does not exist and could not be created.');
            }
        }
    }

    /**
     * Is path absolute.
     *
     * @param string $path Path.
     *
     * @return bool
     * @since 0.0.3 introduced.
     */
    public function isAbsolutePath($path)
    {
        return substr($path, 0, 1) === '/' || substr($path, 1, 1) === ':';
    }

    /**
     * Normalize path.
     *
     * @param string $path Path.
     *
     * @return string
     * @since 0.0.3 introduced.
     */
    public function normalizePath($path)
    {
        $parts = array();
        $path = strtr($path, '\\', '/');
        $prefix = '';
        $absolute = false;

        if (preg_match('{^([0-9a-z]+:(?://(?:[a-z]:)?)?)}i', $path, $match)) {
            $prefix = $match[1];
            $path = substr($path, strlen($prefix));
        }

        if (substr($path, 0, 1) === '/') {
            $absolute = true;
            $path = substr($path, 1);
        }

        $up = false;
        foreach (explode('/', $path) as $chunk) {
            if ('..' === $chunk && ($absolute || $up)) {
                array_pop($parts);
                $up = !(empty($parts) || '..' === end($parts));
            } elseif ('.' !== $chunk && '' !== $chunk) {
                $parts[] = $chunk;
                $up = '..' !== $chunk;
            }
        }

        return $prefix.($absolute ? '/' : '').implode('/', $parts);
    }

    /**
     * Find shortest relative path.
     *
     * @param string $from        Path from.
     * @param string $to          Path to.
     * @param bool   $directories Is directories.
     *
     * @return string
     * @throws \FileSystemException If arguments wrong.
     * @since 0.0.3 introduced.
     */
    public function findShortestPath($from, $to, $directories = false)
    {
        if (!$this->isAbsolutePath($from) || !$this->isAbsolutePath($to)) {
            throw new \FileSystemException(sprintf('$from (%s) and $to (%s) must be absolute paths.', $from, $to));
        }

        $from = lcfirst($this->normalizePath($from));
        $to = lcfirst($this->normalizePath($to));

        if ($directories) {
            $from = rtrim($from, '/') . '/dummy_file';
        }

        if (dirname($from) === dirname($to)) {
            $shortestPath = './'.basename($to);
        } else {
            $commonPath = $to;
            while (strpos($from . '/', $commonPath . '/') !== 0 &&
                '/' !== $commonPath &&
                !preg_match('{^[a-z]:/?$}i', $commonPath)
            ) {
                $commonPath = strtr(dirname($commonPath), '\\', '/');
            }

            if (0 !== strpos($from, $commonPath) || '/' === $commonPath) {
                $shortestPath = $to;
            } else {
                $commonPath = rtrim($commonPath, '/') . '/';
                $sourcePathDepth = substr_count(substr($from, strlen($commonPath)), '/');
                $commonPathCode = str_repeat('../', $sourcePathDepth);

                $shortestPath = ($commonPathCode . substr($to, strlen($commonPath))) ?: './';
            }
        }
        $shortestPath = strtr($shortestPath, '/', DIRECTORY_SEPARATOR);
        return $shortestPath;
    }

    /**
     * Create relative symlink under unix and windows.
     *
     * @param string $target Path to existing.
     * @param string $link   Path to new file/directory which links to $target.
     *
     * @return bool
     * @since 0.0.3 introduced.
     */
    public function relativeSymlink($target, $link)
    {
        $cwd = getcwd();

        $relativePath = $this->findShortestPath($target, $link);
        chdir(dirname($target));
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = 'mklink /d';
            exec("$command $relativePath $target", $output, $returnVar);
            $result = $returnVar > 0 ? false : true;
        } else {
            $result = symlink($target, $relativePath);
        }
        chdir($cwd);
        return (bool)$result;
    }
}
