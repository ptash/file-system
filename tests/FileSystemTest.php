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
        mkdir($fileName . DIRECTORY_SEPARATOR . 'testRecursive' . DIRECTORY_SEPARATOR . 'testRecursive2');
        $fileSystem->emptyDirectory($dirName);
        $this->assertNotEquals(true, is_dir($fileName), 'Check recursive delete content');

        mkdir($fileName);
        mkdir($fileName . DIRECTORY_SEPARATOR . 'testD');
        mkdir($fileName . DIRECTORY_SEPARATOR . 'testD2');
        file_put_contents(implode(DIRECTORY_SEPARATOR, array($fileName, 'testD2', 'file')), '');
        mkdir($fileName . DIRECTORY_SEPARATOR . 'testD3');
        mkdir(implode(DIRECTORY_SEPARATOR, array($fileName, 'testD3', 'testD3D1')));
        file_put_contents(implode(DIRECTORY_SEPARATOR, array($fileName, 'testD3', 'testD3D1', 'file')), '');
        $fileSystem->relativeSymlink(
            implode(DIRECTORY_SEPARATOR, array($fileName, 'testD3', 'testD3D1')),
            implode(DIRECTORY_SEPARATOR, array($fileName, 'testD', 'link'))
        );
        $fileSystem->relativeSymlink(
            $fileName . DIRECTORY_SEPARATOR . 'testD2',
            implode(DIRECTORY_SEPARATOR, array($fileName, 'testD3', 'testD3D1', 'link2'))
        );
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
        $dirName = implode(DIRECTORY_SEPARATOR, [\sys_get_temp_dir(), 'fileSystemTmpDir', 'd1']);
        $fileName = $dirName . DIRECTORY_SEPARATOR . 'testFile';
        $newLinkFileName = $dirName . DIRECTORY_SEPARATOR . 'testLink';

        $fileSystem->emptyDirectory($dirName);
        file_put_contents($fileName, '');

        $fileSystem->relativeSymlink($fileName, $newLinkFileName);
        $this->assertTrue(is_file($newLinkFileName) || is_link($newLinkFileName), 'Check create link');

        $dirNameNew = implode(DIRECTORY_SEPARATOR, [\sys_get_temp_dir(), 'fileSystemTmpDir', 'd2']);
        $fileSystem->emptyDirectory($dirNameNew);
        rmdir($dirNameNew);
        rename($dirName, $dirNameNew);
        $linkAfterMove = implode(DIRECTORY_SEPARATOR, [$dirNameNew, 'testLink']);
        $this->assertTrue(
            is_file($linkAfterMove) || is_link($linkAfterMove),
            'Check relative link after move'
        );
    }

    /**
     * Testing relativeSymlink for link.
     *
     * @return void
     */
    public function testRelativeSymlinkForDir()
    {
        $fileSystem = new FileSystem();
        $dirName = implode(DIRECTORY_SEPARATOR, [\sys_get_temp_dir(), 'fileSystemTmpDir', 'd1']);
        $fileName = $dirName . DIRECTORY_SEPARATOR . 'testFile';
        $newLinkFileName = $dirName . DIRECTORY_SEPARATOR . 'testLink';

        $fileSystem->emptyDirectory($dirName);
        file_put_contents($fileName, '');
        $relativeNameLink = $fileSystem->findShortestPath(getcwd(), $newLinkFileName);
        $fileSystem->relativeSymlink($fileName, $relativeNameLink);
        $this->assertTrue(is_file($newLinkFileName) || is_link($newLinkFileName), 'Check create link');

        $dirNameNew = implode(DIRECTORY_SEPARATOR, [\sys_get_temp_dir(), 'fileSystemTmpDir', 'd2']);
        $fileSystem->emptyDirectory($dirNameNew);
        rmdir($dirNameNew);
        rename($dirName, $dirNameNew);
        $linkAfterMove = implode(DIRECTORY_SEPARATOR, [$dirNameNew, 'testLink']);
        $this->assertTrue(
            is_file($linkAfterMove) || is_link($linkAfterMove),
            'Check relative link after move'
        );
    }
}
