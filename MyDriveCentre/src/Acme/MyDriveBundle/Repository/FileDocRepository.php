<?php
/**
 * Created by PhpStorm.
 * User: Ajitesh
 * Date: 6/15/14
 * Time: 6:58 PM
 */

namespace Acme\MyDriveBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class FileDocRepository extends DocumentRepository
{
    public function findFiles($parentId,$userId)
    {
        // Search for a sub-folder
        if($parentId)
        {
            return $this->createQueryBuilder()
                ->field('parent')->equals(new \MongoId($parentId))
                ->field('refrenceInWiki')->equals(null)
                ->getQuery()
                ->execute();
        }
        else
        {
            // Search the main index
                return $this->createQueryBuilder()
                ->field('parent')->exists(FALSE)
                ->field('userId')->equals($userId)// Parent field will not exist for folders in index.
                ->field('refrenceInWiki')->equals(null)
                ->getQuery()
                ->execute();
        }
    }


}