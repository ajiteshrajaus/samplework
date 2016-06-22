<?php
/**
 * Created by PhpStorm.
 * User: Ajitesh
 * Date: 6/19/14
 * Time: 8:27 PM
 */
namespace Acme\MyDriveBundle\Document;

class FolderDoc
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $folderName
     */
    protected $folderName;

    /**
     * @var int $companyId
     */
    protected $userId;

    /**
     * @var object_id $parent
     */
    protected $parent;


    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set folderName
     *
     * @param string $folderName
     * @return self
     */
    public function setFolderName($folderName)
    {
        $this->folderName = $folderName;
        return $this;
    }

    /**
     * Get folderName
     *
     * @return string $folderName
     */
    public function getFolderName()
    {
        return $this->folderName;
    }

    /**
     * Set companyId
     *
     * @param int $companyId
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Get companyId
     *
     * @return int $companyId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set parent
     *
     * @param object_id $parent
     * @return self
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent
     *
     * @return object_id $parent
     */
    public function getParent()
    {
        return $this->parent;
    }
}