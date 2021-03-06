<?php
/**
 * Base class for unit test classes with build in Cognitive\FileSystem\FileSystem
 * and creating temporary directory.
 *
 * @package Cognitive\FileSystem\Tests
 */

namespace Cognitive\FileSystem\Tests;

use Cognitive\FileSystem\FileSystem;

/**
 * Class FileSystemPHPUnitTestCase
 * @since 0.0.4 introduced.
 */
class FileSystemPHPUnitTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $dirRoot;
    /** @var string */
    protected $dirTmp;
    /** @var FileSystem */
    protected $fileSystem;

    /**
     * ReleaseCheckerTest constructor.
     *
     * @param  string $name     See @see PHPUnit_Framework_TestCase.
     * @param  array  $data     See @see PHPUnit_Framework_TestCase.
     * @param  string $dataName See @see PHPUnit_Framework_TestCase.
     *
     * @return void
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $this->fileSystem = new FileSystem();
        $this->dirTmp = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fileSystemTmp';
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Init tmp directory.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->dirRoot = realpath(getcwd());
        $this->fileSystem->emptyDirectory($this->dirTmp);
        chdir($this->dirTmp);
    }

    /**
     * Clear tmp directory.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->fileSystem->emptyDirectory($this->dirTmp);
        chdir($this->dirRoot);
        parent::tearDown();
    }
}
