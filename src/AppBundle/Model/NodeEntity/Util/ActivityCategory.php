<?php


namespace AppBundle\Model\NodeEntity\Util;


/**
 * Class ActivityCategory
 * @package AppBundle\Model\NodeEntity
 */
abstract class ActivityCategory extends BasicEnum
{
    // repeatable activities
    const COURSE     = "course";
    const SEMINAR    = "seminar";
    const LABORATORY = "laboratory";

    //others
    const EXAM       = "exam";
    const COLLOQUIUM = "coloquim";
    const PROJECT    = "project";
}