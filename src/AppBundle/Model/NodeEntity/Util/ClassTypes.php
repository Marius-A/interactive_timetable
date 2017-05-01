<?php

namespace AppBundle\Model\NodeEntity\Util;

/**
 * Class ClassTypes
 * @package AppBundle\Model\NodeEntity\Util
 */
abstract class ClassTypes extends BasicEnum
{
    const COURSE     = 'course';
    const LABORATORY = 'laboratory';
    const SEMINARY   = 'seminary';
    const PROJECT    = 'project';
}