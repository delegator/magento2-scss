<?php
/**
 * Copyright © Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Delegator\Scss\Preprocessor\Adapter\Scss;

use Leafo\ScssPhp\Compiler;
use Leafo\ScssPhp\Formatter;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\View\Asset\ContentProcessorInterface;
use Magento\Framework\View\Asset\ContentProcessorException;

/**
 * Class Processor
 */
class Processor implements ContentProcessorInterface
{
    const FORMATTER_EXPANDED = Formatter\Expanded::class;
    const FORMATTER_CRUNCHED = Formatter\Crunched::class;

    const LINE_NUMBERS_OFF = 0;
    const LINE_NUMBERS_ON = Compiler::LINE_COMMENTS;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var Source
     */
    public $assetSource;

    /**
     * @var State
     */
    public $appState;

    /**
     * @var Compiler
     */
    public $scssCompiler;

    /**
     * Constructor
     *
     * @param Source $assetSource
     * @param LoggerInterface $logger
     */
    public function __construct(
        Source $assetSource,
        LoggerInterface $logger,
        State $appState,
        Compiler $scssCompiler
    ) {
        $this->assetSource = $assetSource;
        $this->logger = $logger;
        $this->appState = $appState;
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

            $this->setCompilerOptions($asset);

            gc_disable();
            $content = $this->scssCompiler->compile($content);
            gc_enable();

            return $content;
        } catch (\Exception $e) {
            $errorMessage = PHP_EOL . self::ERROR_MESSAGE_PREFIX . PHP_EOL . $path . PHP_EOL . $e->getMessage();
            $this->logger->critical($errorMessage);

            throw new ContentProcessorException(__($errorMessage));
        }
    }

    public function setCompilerOptions(File $asset)
    {
        // Set import path
        $importPath = dirname($asset->getSourceFile()); // @codingStandardsIgnoreLine
        $this->scssCompiler->setImportPaths($importPath);

        // Set debug / output mode
        if ($this->appState->getMode() === State::MODE_PRODUCTION) {
            $this->scssCompiler->setFormatter(self::FORMATTER_CRUNCHED);
            $this->scssCompiler->setLineNumberStyle(self::LINE_NUMBERS_OFF);
        } else {
            $this->scssCompiler->setFormatter(self::FORMATTER_EXPANDED);
            $this->scssCompiler->setLineNumberStyle(self::LINE_NUMBERS_ON);
        }
    }
}
