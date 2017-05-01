<?php

namespace AppBundle\Model\NodeEntity\Util;

/**
 * Class Frequency
 * @package AppBundle\Model\NodeEntity\Util
 */
abstract class Frequency extends BasicEnum
{
   const NO_REPEAT      = 'no_repeat';
   const REPEAT_DAILY   = 'daily';
   const REPEAT_WEEKLY  = 'weekly';
   const REPEAT_MONTHLY = 'monthly';
}