<?php

/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 25/10/16
 * Time: 18:56
 */
use Symfony\Component\Process\Process;
use Trivago\Rumi\Commands\ReturnCodes;
use Trivago\Rumi\Process\GitProcess;

/**
 * @covers \Trivago\Rumi\Process\GitProcess
 */
class GitProcessTest extends PHPUnit_Framework_TestCase
{

    public function testGivenProcessRuns_whenStatusIsSuccessfull_thenReturnCodeIsSuccess() {
        //given
        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->isSuccessful()->willReturn(true);
        $gitProcess = new GitProcess($symfonyProcess->reveal());

        //when
        $actualStatus = $gitProcess->checkStatus();

        //then
        $this->assertEquals(ReturnCodes::SUCCESS, $actualStatus);
    }

    public function testGivenProcessRuns_whenExitCodeIs128andOutputIncludesPermissions_thenReturnCodeIsNoPermissions() {
        //given
        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->isSuccessful()->willReturn(false);
        $symfonyProcess->getExitCode()->willReturn(128);
        $symfonyProcess->getErrorOutput()->willReturn("no permissions");
        $gitProcess = new GitProcess($symfonyProcess->reveal());

        //when
        $actualStatus = $gitProcess->checkStatus();

        //then
        $this->assertEquals(ReturnCodes::FAILED_DUE_TO_REPOSITORY_PERMISSIONS, $actualStatus);
    }

    public function testGivenProcessRuns_whenStatusIsNotSuccessful_thenReturnCodeFail() {
        //given
        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->isSuccessful()->willReturn(false);
        $symfonyProcess->getExitCode()->willReturn(128);
        $symfonyProcess->getErrorOutput()->willReturn("repo does not exist");
        $gitProcess = new GitProcess($symfonyProcess->reveal());

        //when
        $actualStatus = $gitProcess->checkStatus();

        //then
        $this->assertEquals(ReturnCodes::FAILED, $actualStatus);
    }
}