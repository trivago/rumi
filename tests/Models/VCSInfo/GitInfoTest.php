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

namespace Trivago\Rumi\Models\VCSInfo;

/**
 * @covers Trivago\Rumi\Models\VCSInfo\GitInfo
 */
class GitInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testGiveNewInstanceIsCreated_WhenGetTriggered_ThenItReturnsProperValues()
    {
        // given
        $inputUrl = 'a';
        $inputCommit = 'b';
        $inputBranch = 'c';
        $SUT = new GitInfo($inputUrl, $inputCommit, $inputBranch);

        //when
        $url = $SUT->getUrl();
        $commit = $SUT->getCommitId();
        $branch = $SUT->getBranch();

        // then
        $this->assertEquals($inputUrl, $url);
        $this->assertEquals($inputCommit, $commit);
        $this->assertEquals($inputBranch, $branch);
    }
}
