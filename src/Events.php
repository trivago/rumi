<?php
/**
 * @author jsacha
 *
 * @since 28/04/16 19:56
 */

namespace jakubsacha\Rumi;

interface Events
{
    const RUN_STARTED = 'run_started';
    const RUN_FINISHED = 'run_completed';

    const STAGE_STARTED = 'stage_started';
    const STAGE_FINISHED = 'stage_finished';

    const JOB_STARTED = 'job_started';
    const JOB_FINISHED = 'job_finished';
}
