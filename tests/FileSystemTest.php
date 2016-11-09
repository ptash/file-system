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
        $this->assertNotEquals(true, is_dir($dirName));

        $fileSystem->emptyDirectory($dirName);
        $this->assertEquals(true, is_dir($dirName));
    }

    /**
     * Testing normalizePath.
     *
     * @return void
     */
    public function testNormalizePath()
    {
        $fileSystem = new FileSystem();
        $fileSystem->setDirectorySeparator('/');
        $checkVals = array(
            'C:\rrr' => 'C:/rrr',
            '/fff/..\\ddd' => '/ddd',
            'http://dddd\\dddd/rrr.gif' => 'http://dddd/dddd/rrr.gif'
        );
        foreach ($checkVals as $key => $val) {
            $res = $fileSystem->normalizePath($key);
            $this->assertEquals($res, $val);
        }

        $fileSystem->setDirectorySeparator('\\');
        $checkVals = array(
            'C:\rrr' => 'C:\\rrr',
            '/fff/../.\\ddd' => '\\ddd'
        );
        foreach ($checkVals as $key => $val) {
            $res = $fileSystem->normalizePath($key);
            $this->assertEquals($res, $val);
        }
    }

    /**
     * Testing relativeSymlink.
     *
     * @return void
     */
    public function testRelativeSymlink()
    {
        $fileSystem = new FileSystem();
        $dirName = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fileSystemTmpDir';
        $fileName = $dirName . DIRECTORY_SEPARATOR . 'testFile';
        $newLinkFileName = $dirName . DIRECTORY_SEPARATOR . 'testLink';

        $fileSystem->emptyDirectory($dirName);
        file_put_contents($fileName, '');

        $fileSystem->relativeSymlink($fileName, $newLinkFileName);
        $this->assertEquals(true, is_file($newLinkFileName), 'Check create link');
    }
}
