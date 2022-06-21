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
 * @property string  $hash
 * @property integer $member
 * @property integer $interval
 * @property integer $sentOn
 *
 * @method static PropertyNotifierModel|null findById($id, array $opt=array())
 * @method static PropertyNotifierModel|null findOneBy($col, $val, array $opt=array())
 * @method static PropertyNotifierModel|null findOneByHash($col, $val, array $opt=array())
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

    public static function findByMember($member, array $arrOptions = [])
    {
        if(null === $member)
        {
            return null;
        }

        $t = static::$strTable;

        return static::findBy(
            ["$t.member=? OR $t.email=?"],
            [$member->id, $member->email],
            $arrOptions
        );
    }

    /**
     * Find record by member and hash
     */
    public static function findByMemberAndHash($member, $hash, ?string $email = null, array $arrOptions = [])
    {
        $t = static::$strTable;

        return static::findOneBy(
            ["$t.hash=? AND ($t.member=? OR $t.email=?)"],
            [$hash, $member->id ?? 0, $email ?? ($member->email ?? '')],
            $arrOptions
        );
    }

    /**
     * Check if a member owned the record
     */
    public static function isOwnerOfRecord($member, $record): bool
    {
        // Check if the record belongs to the member
        if($member ?? null && (($member->id === $record->member) || ($record->email && ($member->email === $record->email))))
        {
            return true;
        }

        return false;
    }
}
