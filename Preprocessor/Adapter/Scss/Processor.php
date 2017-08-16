<?php
/**
 * Copyright © Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Delegator\Scss\Preprocessor\Adapter\Scss;

use Leafo\ScssPhp\Compiler;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\View\Asset\ContentProcessorInterface;

/**
 * Class Processor
 */
class Processor implements ContentProcessorInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Source
     */
    protected $assetSource;

    /**
     * @var Compiler
     */
    protected $scssCompiler;

    /**
     * Constructor
     *
     * @param Source $assetSource
     * @param LoggerInterface $logger
     */
    public function __construct(
        Source $assetSource,
        LoggerInterface $logger,
        Compiler $scssCompiler
    ) {
        $this->assetSource = $assetSource;
        $this->logger = $logger;
        $this->scssCompiler = $scssCompiler;
    }

    /**
     * Process file content
     *
     * @param File $asset
     * @return string
     */
    public function processContent(File $asset)
    {
        $path = $asset->getPath();
        try {
            $content = $this->assetSource->getContent($asset);

            if (trim($content) === '') {
                return '';
            }

            return $this->scssCompiler->compile($content);
        } catch (\Exception $e) {
            $errorMessage = PHP_EOL . self::ERROR_MESSAGE_PREFIX . PHP_EOL . $path . PHP_EOL . $e->getMessage();
            $this->logger->critical($errorMessage);

            return $errorMessage;
        }
    }
}
