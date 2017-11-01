<?php
/**
 * Copyright © Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Delegator\Scss\Test\Unit\Preprocessor\Adapter\Scss;

use Leafo\ScssPhp\Compiler;
use Magento\Framework\View\Asset\ContentProcessorException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Source;
use Delegator\Scss\Preprocessor\Adapter\Scss\Processor;

/**
 * Class ProcessorTest
 *
 * @see \Delegator\Scss\Preprocessor\Adapter\Scss\Processor
 */
class ProcessorTest extends TestCase
{
    const TEST_FILE = '/_files/test.scss';

    const RESULT_FILE = '/_files/result.css';

    const TEST_PATH = 'test/path/to/file';

    const TEST_EXCEPTION_MESSAGES = 'Test exception messages';

    private $compiler;

    /**
     * Setup before each test
     */
    public function setUp()
    {
        $this->compiler = new Compiler();
    }

    /**
     * SCSS should be processed successfully under normal conditions
     */
    public function testProcessContent()
    {
        $fileMock = $this->getFileMock();

        $assetSourceMock = $this->getSourceMock();
        $loggerMock = $this->getLoggerMock();
        $stateMock = $this->getStateMock();

        $assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($fileMock)
            ->willReturn(file_get_contents(__DIR__ . self::TEST_FILE)); // @codingStandardsIgnoreLine

        $loggerMock->expects(self::never())
            ->method('critical');

        $processor = new Processor($assetSourceMock, $loggerMock, $stateMock, $this->compiler);

        $content = $processor->processContent($fileMock);

        $search = [' ', "\t", "\n", "\r", "\0", "\x0B"];
        $expectedContent = <<<'EOT'
/*line12*/h1{color:#009a82;margin:0;padding:0;}/*line18*/#container{width:460px;margin:0pxauto;}
EOT;

        self::assertEquals($expectedContent, str_replace($search, '', $content));
    }

    /**
     * When a file is bad, the preprocessor should throw a ContentProcessorException
     */
    public function testProcessContentException()
    {
        $fileMock = $this->getFileMock();
        $assetSourceMock = $this->getSourceMock();
        $loggerMock = $this->getLoggerMock();
        $stateMock = $this->getStateMock();

        $message = PHP_EOL
            . Processor::ERROR_MESSAGE_PREFIX . PHP_EOL
            . self::TEST_PATH . PHP_EOL
            . self::TEST_EXCEPTION_MESSAGES;

        $assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($fileMock)
            ->willThrowException(new \Exception(self::TEST_EXCEPTION_MESSAGES));

        $loggerMock->expects(self::once())
            ->method('critical')
            ->with($message);

        $this->expectException(ContentProcessorException::class);

        // Run compiler with file that blows up
        $processor = new Processor($assetSourceMock, $loggerMock, $stateMock, $this->compiler);
        $content = $processor->processContent($fileMock);

        self::assertEquals($message, $content);
    }

    /**
     * When a file is empty, the preprocessor should return an empty string
     */
    public function testEmptyFile()
    {
        $fileMock = $this->getFileMock();
        $assetSourceMock = $this->getSourceMock();
        $loggerMock = $this->getLoggerMock();
        $stateMock = $this->getStateMock();

        $assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($fileMock)
            ->willReturn('');

        // Run compiler with empty file
        $processor = new Processor($assetSourceMock, $loggerMock, $stateMock, $this->compiler);
        $content = $processor->processContent($fileMock);

        self::assertEquals('', $content);
    }

    /**
     * When the application is in production mode, the preprocessor should set the relevant options on the SCSS compiler
     */
    public function testProductionModeCompilerOptions()
    {
        $assetSourceMock = $this->getSourceMock();
        $loggerMock = $this->getLoggerMock();
        $stateMock = $this->getStateMock();
        $compilerMock = $this->getCompilerMock();

        $stateMock->expects(self::once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

        $compilerMock->expects(self::once())
            ->method('setFormatter')
            ->with(Processor::FORMATTER_CRUNCHED);

        $compilerMock->expects(self::once())
            ->method('setLineNumberStyle')
            ->with(Processor::LINE_NUMBERS_OFF);

        $processor = new Processor($assetSourceMock, $loggerMock, $stateMock, $compilerMock);

        self::assertNotNull($processor);
    }

    /**
     * @return File|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getFileMock()
    {
        $fileMock = $this->createMock(File::class);

        $fileMock->expects(self::once())
            ->method('getPath')
            ->willReturn(self::TEST_PATH);

        return $fileMock;
    }

    /**
     * @return Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSourceMock()
    {
        return $this->createMock(Source::class);
    }

    /**
     * @return LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }

    /**
     * @return State|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStateMock()
    {
        return $this->createMock(State::class);
    }

    /**
     * @return Compiler|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCompilerMock()
    {
        return $this->createMock(Compiler::class);
    }
}
