<?php

namespace jakubsacha\Rumi\Process;

/**
 * @covers jakubsacha\Rumi\Process\GitCheckoutProcessFactory
 */
class GitCheckoutProcessFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GitCheckoutProcessFactory
     */
    private $oSUT;

    public function setUp()
    {
        $this->oSUT = new GitCheckoutProcessFactory();
    }

    public function testGivenCheckoutUrl_WhenGetCheckoutProcessCalled_ThenProperGitCommandReturnedAndProperTimeoutsAreSet()
    {
        //given

        //when
        $oProcess = $this->oSUT->getFullCloneProcess('abc');

        //then
        $this->assertEquals('git init && git remote add origin abc && GIT_SSH_COMMAND="ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" git fetch origin', $oProcess->getCommandLine());
        $this->assertEquals(600, $oProcess->getTimeout());
        $this->assertEquals(600, $oProcess->getIdleTimeout());
    }

    public function testGiven_WhenGetFetchProcessCalled_ThenProperGitCommandReturnedAndProperTimeoutsAreSet()
    {
        //given

        //when
        $oProcess = $this->oSUT->getFetchProcess();

        //then
        $this->assertEquals('GIT_SSH_COMMAND="ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" git fetch origin', $oProcess->getCommandLine());
        $this->assertEquals(600, $oProcess->getTimeout());
        $this->assertEquals(600, $oProcess->getIdleTimeout());
    }

    public function testGivenCommitSha_WhenGetCheckoutProcessCalled_ThenProperGitCommandReturnedAndProperTimeoutsAreSet()
    {
        //given

        //when
        $oProcess = $this->oSUT->getCheckoutCommitProcess('abc');

        //then
        $this->assertEquals('git reset --hard && git checkout abc', $oProcess->getCommandLine());
        $this->assertEquals(600, $oProcess->getTimeout());
        $this->assertEquals(600, $oProcess->getIdleTimeout());
    }

    public function testGivenMergeBranch_WhenGetMergeProcessCalled_ThenProperGitCommandReturnedAndProperTimeoutsAreSet()
    {
        //given

        //when
        $oProcess = $this->oSUT->getMergeProcess('abc');

        //then
        $this->assertEquals('git merge --no-edit abc', $oProcess->getCommandLine());
        $this->assertEquals(60, $oProcess->getTimeout());
        $this->assertEquals(60, $oProcess->getIdleTimeout());
    }
}
