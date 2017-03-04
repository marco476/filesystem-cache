<?php
namespace Helper;

class DateHelper
{
    //Check if $date is in the future.
    public static function isDateInFuture(\DateTime $date)
    {
        return $date->getTimestamp() > date_create()->getTimestamp();
    }
}