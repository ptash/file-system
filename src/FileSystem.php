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
    const DIRECTORY_UP = '..';
    const DIRECTORY_CURRENT = '.';
    const PATH_PREFIX = '{^([0-9a-z]+:(?://(?:[a-z]:)?)?)}i';
    /** @var string */
    protected $dirSeparator = DIRECTORY_SEPARATOR;

    /**
     * Set directory separator.
     *
     * @param string $directorySeparator Directory separator.
     *
     * @return void
     */
    public function setDirectorySeparator($directorySeparator)
    {
        $this->dirSeparator = $directorySeparator;
    }

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
                if ($file->isLink()) {
                    if ($this->isOSWindows()) {
                        rmdir($file->getPathName());
                    } else {
                        unlink($file->getPathName());
                    }
                } elseif ($file->isDir()) {
                    rmdir($file->getRealPath());
                } elseif ($file->isFile()) {
                    unlink($file->getRealPath());
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
        $path = $this->getPathWithoutPrefix($path);
        return (0 === strpos($path, $this->dirSeparator));
    }

    /**
     * Get path without prefix and replace directory separators. For example 'http://' or 'C:'.
     *
     * @param string $path Path.
     *
     * @return string
     */
    private function getPathWithoutPrefix($path)
    {
        $prefix = $this->getPathPrefix($path);
        if ('' !== $prefix) {
            $path = substr($path, strlen($prefix));
        }
        $path = strtr($path, '\\', $this->dirSeparator);
        $path = strtr($path, '/', $this->dirSeparator);
        return $path;
    }

    /**
     * Get path prefix.
     *
     * @param string $path Path.
     *
     * @return string
     */
    private function getPathPrefix($path)
    {
        if (preg_match(self::PATH_PREFIX, $path, $match)) {
            list(, $prefix) = $match;
            return $prefix;
        }
        return '';
    }

    /**
     * Normalize path.
     * This replaces backslashes and slashes with dirSeparator @see setDirectorySeparator,
     * removes ending slash and collapses redundant separators and up-level references.
     *
     * @param string $path Path.
     *
     * @return string
     * @since 0.0.3 introduced.
     */
    public function normalizePath($path)
    {
        $parts = array();

        $absolute = $this->isAbsolutePath($path);
        $prefix = $this->getPathPrefix($path);
        $path = $this->getPathWithoutPrefix($path);

        $up = false;
        foreach (explode($this->dirSeparator, $path) as $chunk) {
            if (self::DIRECTORY_UP === $chunk && ($absolute || $up)) {
                array_pop($parts);
                $up = !(empty($parts) || self::DIRECTORY_UP === end($parts));
            } elseif (self::DIRECTORY_CURRENT !== $chunk && '' !== $chunk) {
                $parts[] = $chunk;
                $up = (self::DIRECTORY_UP !== $chunk);
            }
        }

        return $prefix.($absolute ? $this->dirSeparator : '').implode($this->dirSeparator, $parts);
    }

    /**
     * Find shortest relative path.
     *
     * @param string $from        Path from.
     * @param string $to          Path to.
     * @param bool   $directories Is directories.
     *
     * @return string
     * @throws FileSystemException If arguments wrong.
     * @since 0.0.3 introduced.
     */
    public function findShortestPath($from, $to, $directories = false)
    {
        if (!$this->isAbsolutePath($from) || !$this->isAbsolutePath($to)) {
            throw new FileSystemException(sprintf('$from (%s) and $to (%s) must be absolute paths.', $from, $to));
        }

        $from = lcfirst($this->normalizePath($from));
        $to = lcfirst($this->normalizePath($to));

        if ($directories) {
            $from = rtrim($from, $this->dirSeparator) . $this->dirSeparator . 'dummy_file';
        }

        if (dirname($from) === dirname($to)) {
            $shortestPath = self::DIRECTORY_CURRENT . $this->dirSeparator . basename($to);
        } else {
            $commonPath = $to;
            while (strpos($from . $this->dirSeparator, $commonPath . $this->dirSeparator) !== 0 &&
                $this->dirSeparator !== $commonPath &&
                !preg_match('{^[a-z]:/?$}i', $commonPath)
            ) {
                $commonPath = strtr(dirname($commonPath), '\\', $this->dirSeparator);
                $commonPath = strtr($commonPath, '/', $this->dirSeparator);
            }

            if (0 !== strpos($from, $commonPath) || $this->dirSeparator === $commonPath) {
                $shortestPath = $to;
            } else {
                $commonPath = rtrim($commonPath, $this->dirSeparator) . $this->dirSeparator;
                $sourcePathDepth = substr_count(substr($from, strlen($commonPath)), $this->dirSeparator);
                $commonPathCode = str_repeat(self::DIRECTORY_UP . $this->dirSeparator, $sourcePathDepth);

                $shortestPath = ($commonPathCode . substr($to, strlen($commonPath))) ?
                    : self::DIRECTORY_CURRENT . $this->dirSeparator;
            }
        }
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

        $relativePath = $this->findShortestPath($link, $target);
        chdir(dirname($target));
        if ($this->isOSWindows()) {
            $command = 'mklink /d';
            exec("$command $relativePath $target", $output, $returnVar);
            $result = $returnVar > 0 ? false : true;
        } else {
            $result = symlink($relativePath, $link);
        }
        chdir($cwd);
        return (bool)$result;
    }

    /**
     * Is Windows?
     *
     * @return bool
     */
    private function isOSWindows()
    {
        return strtoupper(substr(PHP_OS, 0, strlen('WIN'))) === 'WIN';
    }
}
