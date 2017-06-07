<?php


namespace AppBundle\Model\NodeEntity;


use AppBundle\Model\NodeEntity\Util\BasicEnum;

/**
 * Class ActivityCategory
 * @package AppBundle\Model\NodeEntity
 */
abstract class ActivityCategory extends BasicEnum
{
    // repeatable activities
    const COURSE     = "COURSE";
    const SEMINAR    = "SEMINAR";
    const LABORATORY = "LABORATORY";

    //others
    const EXAM       = "EXAM";
    const COLLOQUIUM = "COLLOQUIUM";
    const PROJECT    = "PROJECT";
}