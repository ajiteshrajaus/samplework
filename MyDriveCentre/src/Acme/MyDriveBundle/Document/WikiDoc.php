<?php
/**
 * Created by PhpStorm.
 * User: Ajitesh
 * Date: 6/16/14
 * Time: 11:04 AM
 */
namespace Acme\MyDriveBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class WikiDoc
{

    protected $id;
    protected $userId;
    protected $docNo;
    protected $content;
    protected $docName;
    protected $companyId;
    protected $parent;
    protected $lock;
    protected $autoSave;
    protected $isDraftCopy;

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
     * Set name
     *
     * @param timestamp $surveyDate
     * @return self
     */
    public function setDocName($docName)
    {
        $this->docName = $docName;
        return $this;
    }

    /**
     * Get DocName
     *
     * @return $DocName
     */
    public function getDocName()
    {
        return $this->docName;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Get DocName
     *
     * @return $DocName
     */
    public function getUserId()
    {
        return $this->userId;
    }
    public function setDocNo($docNo)
    {
        $this->docNo = $docNo;
        return $this;
    }

    /**
     * Get DocName
     *
     * @return $DocName
     */
    public function getDocNo()
    {
        return $this->docNo;
    }


    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get surveyDate
     *
     * @return timestamp $surveyDate
     */
    public function getContent()
    {
        return $this->content;
    }
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
        return $this;
    }

    /**
     * Get surveyDate
     *
     * @return timestamp $surveyDate
     */

    public function getParent()
    {
        return $this->parent;
    }
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get surveyDate
     *
     * @return timestamp $surveyDate
     */

    public function getCompanyId()
    {
        return $this->companyId;
    }
    public function getLock()
    {
        return $this->lock;
    }
    public function setLock($lock)
    {
        $this->lock = $lock;
        return $this;
    }
    public function getAutoSave()
    {
        return $this->autoSave;
    }
    public function setAutoSave($autoSave)
    {
        $this->autoSave = $autoSave;
        return $this;
    }
    public function getIsDraftCopy()
    {
        return $this->isDraftCopy;
    }
    public function setIsDraftCopy($isDraftCopy)
    {
        $this->isDraftCopy = $isDraftCopy;
        return $this;
    }
}