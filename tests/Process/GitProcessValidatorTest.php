<?php

/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 25/10/16
 * Time: 18:56
 */
use Symfony\Component\Process\Process;
use Trivago\Rumi\Commands\ReturnCodes;
use Trivago\Rumi\Process\GitCheckoutValidator;

/**
 * @covers \Trivago\Rumi\Process\GitCheckoutValidator
 */
class GitProcessValidatorTest extends PHPUnit_Framework_TestCase
{

    public function testGivenProcessRuns_whenStatusIsSuccessfull_thenNothingHappens() {
        //given
        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->isSuccessful()->willReturn(true);

        $gitProcess = new GitCheckoutValidator();

        //when
        $gitProcess->checkStatus($symfonyProcess->reveal());

        //then
        $this->assertTrue(true);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Your repository is not public. Please check permissions
     * @expectedExceptionCode 4
     */
    public function testGivenProcessRuns_whenExitCodeIs128andOutputIncludesPermissions_thenReturnCodeIsNoPermissions() {
        //given
        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->isSuccessful()->willReturn(false);
        $symfonyProcess->getExitCode()->willReturn(128);
        $symfonyProcess->getErrorOutput()->willReturn("no permissions");

        $gitProcess = new GitCheckoutValidator();

        //when
        $gitProcess->checkStatus($symfonyProcess->reveal());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Repository does not exist
     * @expectedExceptionCode 1
     */
    public function testGivenProcessRuns_whenStatusIsNotSuccessful_thenReturnCodeFail() {
        //given
        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->isSuccessful()->willReturn(false);
        $symfonyProcess->getExitCode()->willReturn(128);
        $symfonyProcess->getErrorOutput()->willReturn("Repository does not exist");

        $gitProcess = new GitCheckoutValidator();

        //when
        $gitProcess->checkStatus($symfonyProcess->reveal());
    }
}