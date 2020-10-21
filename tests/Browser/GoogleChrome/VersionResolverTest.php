<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\CommandLine\CommandLineEnvironment;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VersionResolverTest extends TestCase
{
    private VersionResolver $versionResolver;
    /** @var MockObject&CommandLineEnvironment */
    private $commandLineEnvMock;

    protected function setUp() : void
    {
        $this->commandLineEnvMock = $this->getMockBuilder(CommandLineEnvironment::class)->getMock();
        $this->versionResolver = new VersionResolver($this->commandLineEnvMock);
    }

    public function testSupportChrome() : void
    {
        self::assertTrue($this->versionResolver->supports(BrowserName::GOOGLE_CHROME()));
    }

    public function testDoesNotSupportFirefox() : void
    {
        self::assertFalse($this->versionResolver->supports(BrowserName::FIREFOX()));
    }

    public function testFromLinux() : void
    {
        $this->mockCommandLineCommandOutput('google-chrome --version', 'Google Chrome 86.0.4240.80');

        self::assertEquals(
            Version::fromString('86.0.4240.80'),
            $this->versionResolver->from(OperatingSystem::LINUX(), 'google-chrome')
        );
    }

    public function testFromMac() : void
    {
        $this->mockCommandLineCommandOutput(
            '/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --version',
            'Google Chrome 86.0.4240.80'
        );

        self::assertEquals(
            Version::fromString('86.0.4240.80'),
            $this->versionResolver->from(OperatingSystem::MACOS(), '/Applications/Google\ Chrome.app')
        );
    }

    public function testFromWindows() : void
    {
        $this->mockCommandLineCommandOutput(
            'wmic datafile where name="C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" get Version /value',
            'Google Chrome 86.0.4240.80'
        );

        self::assertEquals(
            Version::fromString('86.0.4240.80'),
            $this->versionResolver->from(
                OperatingSystem::WINDOWS(),
                'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe'
            )
        );
    }

    private function mockCommandLineCommandOutput(string $command, string $output) : void
    {
        $this->commandLineEnvMock
            ->expects(self::any())
            ->method('getCommandLineSuccessfulOutput')
            ->with($command)
            ->willReturn($output);
    }
}