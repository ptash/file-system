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
                } else {
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
}
