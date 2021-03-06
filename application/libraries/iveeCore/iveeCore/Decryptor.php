<?php
/**
 * Decryptor class file.
 *
 * PHP version 5.3
 *
 * @category IveeCore
 * @package  IveeCoreClasses
 * @author   Aineko Macx <ai@sknop.net>
 * @license  https://github.com/aineko-m/iveeCore/blob/master/LICENSE GNU Lesser General Public License
 * @link     https://github.com/aineko-m/iveeCore/blob/master/iveeCore/Decryptor.php
 *
 */

namespace iveeCore;

/**
 * Class for invention decryptors.
 * Inheritance: Decryptor -> Sellable -> Type -> SdeTypeCommon
 *
 * @category IveeCore
 * @package  IveeCoreClasses
 * @author   Aineko Macx <ai@sknop.net>
 * @license  https://github.com/aineko-m/iveeCore/blob/master/LICENSE GNU Lesser General Public License
 * @link     https://github.com/aineko-m/iveeCore/blob/master/iveeCore/Decryptor.php
 *
 */
class Decryptor extends Sellable
{
    /**
     * @var int the material efficiency modifier
     */
    protected $meModifier;

    /**
     * @var int the production efficiency modifier
     */
    protected $teModifier;

    /**
     * @var int the production run modifier
     */
    protected $runModifier;

    /**
     * @var float the invention chance factor
     */
    protected $probabilityModifier;

    /**
     * @var array to hold the decryptor groups which in turn hold the decryptor IDs
     */
    protected static $decryptorGroups = array();

    /**
     * Constructor. Use \iveeCore\Type::getById() to instantiate Decryptor objects instead.
     * 
     * @param int $id of the Decryptor object
     * 
     * @return \iveeCore\Decryptor
     * @throws \iveeCore\Exceptions\UnexpectedDataException when loading Decryptor data fails
     */
    protected function __construct($id)
    {
        //call parent constructor
        parent::__construct($id);

        //lookup SDE class
        $sdeClass = Config::getIveeClassName('SDE');

        //fetch decryptor modifiers from DB
        $res = $sdeClass::instance()->query(
            "SELECT
            attributeID,
            valueFloat
            FROM dgmTypeAttributes
            WHERE
            typeID = " . $this->id . "
            AND attributeID IN (1112, 1113, 1114, 1124);"
        );

        //set modifiers to object
        while ($row = $res->fetch_assoc()) {
            switch ($row['attributeID']) {
            case 1112:
                $this->probabilityModifier = (float) $row['valueFloat'];
                break;
            case 1113:
                $this->meModifier = (int) $row['valueFloat'];
                break;
            case 1114:
                $this->teModifier = (int) $row['valueFloat'];
                break;
            case 1124:
                $this->runModifier = (int) $row['valueFloat'];
                break;
            default:
                self::throwException('UnexpectedDataException', "Error loading data for Decryptor ID=" . $this->id);
            }
        }
    }

    /**
     * Returns the ME modifier
     * 
     * @return int the material efficiency modifier
     */
    public function getMEModifier()
    {
        return $this->teModifier;
    }

    /**
     * Returns the TE modifier
     * 
     * @return int the time efficiency modifier
     */
    public function getTEModifier()
    {
        return $this->teModifier;
    }

    /**
     * Returns the run modifier
     * 
     * @return int the production run modifier
     */
    public function getRunModifier()
    {
        return $this->runModifier;
    }

    /**
     * Returns the invention chance modifier
     * 
     * @return float the invention chance factor
     */
    public function getProbabilityModifier()
    {
        return $this->probabilityModifier;
    }

    /**
     * Returns the compatible decryptor IDs for a given groupID
     * 
     * @param int $groupID specifies the decryptor group to return
     * 
     * @return array with the decryptor IDs
     * @throws \iveeCore\Exceptions\InvalidDecryptorGroupException if decryptor group is not found
     */
    public static function getIDsFromGroup($groupID)
    {
        //lazy load data from DB
        if (empty(static::$decryptorGroups)) {
            //lookup SDE class
            $sdeClass = Config::getIveeClassName('SDE');
            $res = $sdeClass::instance()->query(
                "SELECT it.groupID, it.typeID FROM invGroups as ig
                JOIN invTypes as it ON ig.groupID = it.groupID
                WHERE categoryID = 35
                AND it.published = 1"
            );
            while ($row = $res->fetch_assoc())
                static::$decryptorGroups[(int) $row['groupID']][] = $row['typeID'];
        }

        if (!isset(static::$decryptorGroups[$groupID]))
            self::throwException('InvalidDecryptorGroupException', "Decryptor group " . (int) $groupID . " not found");
        
        return static::$decryptorGroups[$groupID];
    }

    /**
     * Returns whether this Type is reprocessable
     * 
     * @return bool if the item is reprocessable. Decryptors never are.
     */
    public function isReprocessable()
    {
        return false;
    }
}
