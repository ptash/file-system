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

        mkdir($fileName);
        mkdir($fileName . DIRECTORY_SEPARATOR . 'testRecursive');
        $fileSystem->emptyDirectory($dirName);
        $this->assertNotEquals(true, is_dir($fileName), 'Check recursive delete content');

        rmdir($dirName);
        $fileSystem->emptyDirectory($dirName, false);
        $this->assertNotEquals(true, is_dir($dirName));

        $fileSystem->emptyDirectory($dirName);
        $this->assertEquals(true, is_dir($dirName));
    }

    /**
     * Testing normalizePath.
     *
     * @param string $dirSeparator Separator.
     * @param string $checkValues  Check values as array('somePath' => 'normalizePath').
     *
     * @return void
     *
     * @dataProvider getProviderTestCreateLinkToJSDirs
     */
    public function testNormalizePath($dirSeparator, $checkValues)
    {
        $fileSystem = new FileSystem();
        $fileSystem->setDirectorySeparator($dirSeparator);
        foreach ($checkValues as $key => $val) {
            $res = $fileSystem->normalizePath($key);
            $this->assertEquals($res, $val);
        }
    }

    /**
     * Data provider for testCreateLinkToJSDirs test.
     * @return array
     */
    public function getProviderTestCreateLinkToJSDirs()
    {
        return array(
            array(
                '/',
                array(
                    'C:\rrr' => 'C:/rrr',
                    '/fff/..\\ddd' => '/ddd',
                    'http://dddd\\dddd/rrr.gif' => 'http://dddd/dddd/rrr.gif'
                )
            ),
            array(
                '\\',
                array(
                    'C:\rrr' => 'C:\\rrr',
                    '/fff/../.\\ddd' => '\\ddd'
                )
            )
        );
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
