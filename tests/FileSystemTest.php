<?php
/**
 * Testing FileSystem.
 *
 * @package Cognitive\FileSystem\Tests
 */

namespace Cognitive\FileSystem\Tests;

use Cognitive\FileSystem\FileSystem;

/**
 * Class FileSystemTest
 */
class FileSystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing emptyDirectory.
     *
     * @return void
     */
    public function testEmptyDirectory()
    {
        $fileSystem = new FileSystem();
        $dirName = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fileSystemTmpDir';
        $fileName = $dirName . DIRECTORY_SEPARATOR . 'test';

        $fileSystem->emptyDirectory($dirName);
        
        $this->assertEquals(true, is_dir($dirName));

        file_put_contents($fileName, '');
        $this->assertEquals(true, is_file($fileName));
        $fileSystem->emptyDirectory($dirName);
        $this->assertNotEquals(true, is_file($fileName));

        mkdir($fileName);
        $this->assertEquals(true, is_dir($fileName));
        $fileSystem->emptyDirectory($dirName);
        $this->assertNotEquals(true, is_dir($fileName));

        rmdir($dirName);
        $fileSystem->emptyDirectory($dirName, false);
        $this->assertNotEquals(true, is_dir($fileName));

        $fileSystem->emptyDirectory($dirName);
        $this->assertEquals(true, is_dir($fileName));
    }
}
