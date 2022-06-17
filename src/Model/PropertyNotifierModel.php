<?php

namespace ContaoEstateManager\PropertyNotifier\Model;

use Contao\Model;

/**
 * Reads and writes notifier items
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $email
 * @property string  $properties
 * @property integer $member
 * @property integer $interval
 * @property integer $sentOn
 *
 * @method static PropertyNotifierModel|null findById($id, array $opt=array())
 * @method static PropertyNotifierModel|null findOneBy($col, $val, array $opt=array())
 * @method static PropertyNotifierModel|null findOneByTstamp($val, array $opt=array())
 *
 * @method static \Model\Collection|PropertyNotifierModel[]|PropertyNotifierModel|null findByTstamp($val, array $opt=array())
 * @method static \Model\Collection|PropertyNotifierModel[]|PropertyNotifierModel|null findMultipleByIds($var, array $opt=array())
 * @method static \Model\Collection|PropertyNotifierModel[]|PropertyNotifierModel|null findBy($col, $val, array $opt=array())
 * @method static \Model\Collection|PropertyNotifierModel[]|PropertyNotifierModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class PropertyNotifierModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_property_notifier';
}
