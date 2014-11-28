<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Deployment configuration writer
 */
class Writer
{
    /**
     * Deployment config reader
     *
     * @var Reader
     */
    private $reader;

    /**
     * Application filesystem
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Formatter
     *
     * @var Writer\FormatterInterface
     */
    private $formatter;

    /**
     * Constructor
     *
     * @param Reader $reader
     * @param Filesystem $filesystem
     * @param Writer\FormatterInterface $formatter
     */
    public function __construct(Reader $reader, Filesystem $filesystem, Writer\FormatterInterface $formatter = null)
    {
        $this->reader = $reader;
        $this->filesystem = $filesystem;
        $this->formatter = $formatter ?: new Writer\PhpFormatter;
    }

    /**
     * Creates the deployment configuration file
     *
     * Will overwrite a file, if it exists.
     *
     * @param SegmentInterface[] $segments
     * @return void
     * @throws \InvalidArgumentException
     */
    public function create($segments)
    {
        $data = [];
        foreach ($segments as $segment) {
            if (!($segment instanceof SegmentInterface)) {
                throw new \InvalidArgumentException('An instance of SegmentInterface is expected.');
            }
            $data[$segment->getKey()] = $segment->getData();
        }
        $this->write($data);
    }

    /**
     * Update data in the configuration file using specified segment object
     *
     * @param SegmentInterface $segment
     * @return void
     */
    public function update(SegmentInterface $segment)
    {
        $key = $segment->getKey();
        $data = $this->reader->load();
        $data[$key] = $segment->getData();
        $this->write($data);
    }

    /**
     * Check if configuration file is writable
     *
     * @return bool
     */
    public function checkIfWritable()
    {
        $configDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG);
        if ($configDirectory->isWritable($this->reader->getFile())) {
            return true;
        }
        return false;
    }

    /**
     * Persists the data into file
     *
     * @param array $data
     * @return void
     */
    private function write($data)
    {
        $contents = $this->formatter->format($data);
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile($this->reader->getFile(), $contents);
    }
}