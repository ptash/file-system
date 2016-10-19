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
     * @param string $dir                   Path to directory.
     * @param bool   $ensureDirectoryExists If true then create empty directory.
     *
     * @return void
     */
    public function emptyDirectory($dir, $ensureDirectoryExists = true)
    {
        if (!is_dir($dir) && $ensureDirectoryExists) {
            mkdir($dir, self::MAKE_DIR_MODE_DEFAULT, true);
        }

        if (is_dir($dir)) {
            $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
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
}
