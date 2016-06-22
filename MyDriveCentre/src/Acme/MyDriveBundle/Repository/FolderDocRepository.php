<?php
/**
 * Created by PhpStorm.
 * User: Ajitesh
 * Date: 6/19/14
 * Time: 8:28 PM
 */

namespace Acme\MyDriveBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class FolderDocRepository extends DocumentRepository
{
    public function findFolders($parentId, $userId)
    {
        // Search for a sub-folder
        if($parentId)
        {
            return $this->createQueryBuilder()
                ->field('parent')->equals(new \MongoId($parentId))
                ->field('userId')->equals($userId)
                ->getQuery()
                ->execute();
        }
        else
        {
            // Search the main index
                return $this->createQueryBuilder()
                ->field('parent')->exists(FALSE)		// Parent field will not exist for folders in index.
                ->field('userId')->equals($userId)
                ->getQuery()
                ->execute();
        }
    }

}