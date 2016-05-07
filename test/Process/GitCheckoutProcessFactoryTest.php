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

namespace Trivago\Rumi\Process;

/**
 * @covers Trivago\Rumi\Process\GitCheckoutProcessFactory
 */
class GitCheckoutProcessFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GitCheckoutProcessFactory
     */
    private $SUT;

    public function setUp()
    {
        $this->SUT = new GitCheckoutProcessFactory();
    }

    public function testGivenCheckoutUrl_WhenGetCheckoutProcessCalled_ThenProperGitCommandReturnedAndProperTimeoutsAreSet()
    {
        //given

        //when
        $process = $this->SUT->getFullCloneProcess('abc');

        //then
        $this->assertEquals('git init && git remote add origin abc && GIT_SSH_COMMAND="ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" git fetch origin', $process->getCommandLine());
        $this->assertEquals(600, $process->getTimeout());
        $this->assertEquals(600, $process->getIdleTimeout());
    }

    public function testGiven_WhenGetFetchProcessCalled_ThenProperGitCommandReturnedAndProperTimeoutsAreSet()
    {
        //given

        //when
        $process = $this->SUT->getFetchProcess();

        //then
        $this->assertEquals('GIT_SSH_COMMAND="ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" git fetch origin', $process->getCommandLine());
        $this->assertEquals(600, $process->getTimeout());
        $this->assertEquals(600, $process->getIdleTimeout());
    }

    public function testGivenCommitSha_WhenGetCheckoutProcessCalled_ThenProperGitCommandReturnedAndProperTimeoutsAreSet()
    {
        //given

        //when
        $process = $this->SUT->getCheckoutCommitProcess('abc');

        //then
        $this->assertEquals('git reset --hard && git checkout abc', $process->getCommandLine());
        $this->assertEquals(600, $process->getTimeout());
        $this->assertEquals(600, $process->getIdleTimeout());
    }

    public function testGivenMergeBranch_WhenGetMergeProcessCalled_ThenProperGitCommandReturnedAndProperTimeoutsAreSet()
    {
        //given

        //when
        $process = $this->SUT->getMergeProcess('abc');

        //then
        $this->assertEquals('git merge --no-edit abc', $process->getCommandLine());
        $this->assertEquals(60, $process->getTimeout());
        $this->assertEquals(60, $process->getIdleTimeout());
    }
}
