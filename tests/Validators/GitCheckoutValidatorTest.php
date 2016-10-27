<?php

/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 25/10/16
 * Time: 18:56.
 */
use Symfony\Component\Process\Process;
use Trivago\Rumi\Validators\GitCheckoutValidator;

/**
 * @covers \Trivago\Rumi\Validators\GitCheckoutValidator
 */
class GitCheckoutValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testGivenProcessRuns_whenStatusIsSuccessfull_thenNothingHappens()
    {
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
     * @expectedExceptionMessage Rumi has no permissions to your repository
     * @expectedExceptionCode 4
     */
    public function testGivenProcessRuns_whenExitCodeIs128andOutputIncludesPermissions_thenReturnCodeIsNoPermissions()
    {
        //given
        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->isSuccessful()->willReturn(false);
        $symfonyProcess->getExitCode()->willReturn(128);
        $symfonyProcess->getErrorOutput()->willReturn('no permissions');

        $gitProcess = new GitCheckoutValidator();

        //when
        $gitProcess->checkStatus($symfonyProcess->reveal());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Repository does not exist
     * @expectedExceptionCode 1
     */
    public function testGivenProcessRuns_whenStatusIsNotSuccessful_thenReturnCodeFail()
    {
        //given
        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->isSuccessful()->willReturn(false);
        $symfonyProcess->getExitCode()->willReturn(128);
        $symfonyProcess->getErrorOutput()->willReturn('Repository does not exist');

        $gitProcess = new GitCheckoutValidator();

        //when
        $gitProcess->checkStatus($symfonyProcess->reveal());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Rumi has no permissions to your repository
     * @expectedExceptionCode 4
     */
    public function testGivenProcessFailed_whenErrorOutputContainsPermissionsWithUpperCase_thenThrowException()
    {
        //given
        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->isSuccessful()->willReturn(false);
        $symfonyProcess->getExitCode()->willReturn(128);
        $symfonyProcess->getErrorOutput()->willReturn('no PermisSions');

        $gitProcess = new GitCheckoutValidator();

        //when
        $gitProcess->checkStatus($symfonyProcess->reveal());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Rumi has no permissions to your repository
     * @expectedExceptionCode 4
     */
    public function testGivenProcessFailed_whenErrorOutputContainsPermissionsWithLowerCase_thenThrowException()
    {
        //given
        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->isSuccessful()->willReturn(false);
        $symfonyProcess->getExitCode()->willReturn(128);
        $symfonyProcess->getErrorOutput()->willReturn('no permissions');

        $gitProcess = new GitCheckoutValidator();

        //when
        $gitProcess->checkStatus($symfonyProcess->reveal());
    }
}
