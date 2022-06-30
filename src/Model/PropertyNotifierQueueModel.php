<?php

namespace ContaoEstateManager\PropertyNotifier\Model;

use Contao\Database;
use Contao\Model;

/**
 * Reads and writes notifier items
 *
 * @property integer $id
 * @property integer $realEstateId
 * @property integer $notifierId
 * @property integer $sentOn
 *
 * @method static PropertyNotifierQueueModel|null findById($id, array $opt=array())
 * @method static PropertyNotifierQueueModel|null findOneBy($col, $val, array $opt=array())
 *
 * @method static \Model\Collection|PropertyNotifierQueueModel[]|PropertyNotifierQueueModel|null findMultipleByIds($var, array $opt=array())
 * @method static \Model\Collection|PropertyNotifierQueueModel[]|PropertyNotifierQueueModel|null findBy($col, $val, array $opt=array())
 * @method static \Model\Collection|PropertyNotifierQueueModel[]|PropertyNotifierQueueModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class PropertyNotifierQueueModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_property_notifier_queue';
}
