<?php

namespace jakubsacha\Rumi\Commands;

interface ReturnCodes {
    const SUCCESS = 0;
    const FAILED = 1;

    const RUMI_YML_DOES_NOT_EXIST = 2;
}