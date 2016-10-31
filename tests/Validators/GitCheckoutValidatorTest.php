<?php

/*
 * Copyright 2016 trivago GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
