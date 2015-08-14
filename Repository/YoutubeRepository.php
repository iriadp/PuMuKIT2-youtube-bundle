<?php

namespace Pumukit\YoutubeBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * YoutubeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class YoutubeRepository extends DocumentRepository
{
    /**
     * Get with any status query builder
     *
     * @param array $statusArray
     * @return QueryBuilder
     */
    public function getWithAnyStatusQueryBuilder($statusArray=array())
    {
        return $this->createQueryBuilder()
            ->field('status')->in($statusArray);
    }

    /**
     * Get with any status query
     *
     * @param array $statusArray
     * @return Query
     */
    public function getWithAnyStatusQuery($statusArray=array())
    {
        return $this->getWithAnyStatusQueryBuilder($statusArray)->getQuery();
    }

    /**
     * Get with any status
     *
     * @param array $statusArray
     * @return Cursor
     */
    public function getWithAnyStatus($statusArray=array())
    {
        return $this->getWithAnyStatusQuery($statusArray)->execute();
    }

    /**
     * Get distinct Multimedia Object Ids with any status
     *
     * @param array $statusArray
     * @return Cursor
     */
    public function getDistinctMultimediaObjectIdsWithAnyStatus($statusArray=array())
    {
        return $this->getWithAnyStatusQueryBuilder($statusArray)
            ->distinct('multimediaObjectId')
            ->getQuery()
            ->execute();
    }

    /**
     * Get with status and force query builder
     *
     * @param string $status
     * @param boolean $force
     * @return QueryBuilder
     */
    public function getWithStatusAndForceQueryBuilder($status, $force=false)
    {
        return $this->createQueryBuilder()
            ->field('status')->equals($status)
            ->field('force')->equals($force);
    }

    /**
     * Get with status and force query
     *
     * @param string $status
     * @param boolean $force
     * @return Query
     */
    public function getWithStatusAndForceQuery($status, $force=false)
    {
        return $this->getWithStatusAndForceQueryBuilder($status, $force)->getQuery();
    }

    /**
     * Get with status and force
     *
     * @param string $status
     * @param boolean $force
     * @return Cursor
     */
    public function getWithStatusAndForce($status, $force=false)
    {
        return $this->getWithStatusAndForceQuery($status, $force)->execute();
    }

    /**
     * Get distinct Multimedia Object Ids with status and force
     *
     * @param string $status
     * @param boolean $force
     * @return Cursor
     */
    public function getDistinctMultimediaObjectIdsWithStatusAndForce($status, $force=false)
    {
        return $this->getWithStatusAndForceQueryBuilder($status, $force)
            ->distinct('multimediaObjectId')
            ->getQuery()
            ->execute();
    }

    /**
     * Get distinct field with status and force
     *
     * @param string $field
     * @param string $status
     * @param boolean $force
     * @return Cursor
     */
    public function getDistinctFieldWithStatusAndForce($field, $status, $force=false)
    {
        return $this->getWithStatusAndForceQueryBuilder($status, $force)
            ->distinct($field)
            ->getQuery()
            ->execute();
    }

    /**
     * Get without any status query builder
     *
     * @param array $statusArray
     * @return QueryBuilder
     */
    public function getWithoutAnyStatusQueryBuilder($statusArray=array())
    {
        return $this->createQueryBuilder()
            ->field('status')->notIn($statusArray);
    }

    /**
     * Get without any status query
     *
     * @param array $statusArray
     * @return Query
     */
    public function getWithoutAnyStatusQuery($statusArray=array())
    {
        return $this->getWithoutAnyStatusQueryBuilder($statusArray)->getQuery();
    }

    /**
     * Get without any status
     *
     * @param array $statusArray
     * @return Cursor
     */
    public function getWithoutAnyStatus($statusArray=array())
    {
        return $this->getWithoutAnyStatusQuery($statusArray)->execute();
    }

    /**
     * Get distinct Multimedia Object Ids without any status
     *
     * @param array $statusArray
     * @return Cursor
     */
    public function getDistinctMultimediaObjectIdsWithoutAnyStatus($statusArray=array())
    {
        return $this->getWithoutAnyStatusQueryBuilder($statusArray)
            ->distinct('multimediaObjectId')
            ->getQuery()
            ->execute();
    }

    /**
     * Get with status and updatePlaylist query builder
     *
     * @param string $status
     * @param boolean $updatePlaylist
     * @return QueryBuilder
     */
    public function getWithStatusAndUpdatePlaylistQueryBuilder($status, $updatePlaylist=false)
    {
        return $this->createQueryBuilder()
            ->field('status')->equals($status)
            ->field('updatePlaylist')->equals($updatePlaylist);
    }

    /**
     * Get with status and updatePlaylist query
     *
     * @param string $status
     * @param boolean $updatePlaylist
     * @return Query
     */
    public function getWithStatusAndUpdatePlaylistQuery($status, $updatePlaylist=false)
    {
        return $this->getWithStatusAndUpdatePlaylistQueryBuilder($status, $updatePlaylist)->getQuery();
    }

    /**
     * Get with status and updatePlaylist
     *
     * @param string $status
     * @param boolean $updatePlaylist
     * @return Cursor
     */
    public function getWithStatusAndUpdatePlaylist($status, $updatePlaylist=false)
    {
        return $this->getWithStatusAndUpdatePlaylistQuery($status, $updatePlaylist)->execute();
    }

    /**
     * Get by multimedia object update date
     * greater than sync metadata date query builder
     *
     * @return QueryBuilder
     */
    public function getNotMetadataUpdatedQueryBuilder()
    {
        return $this->createQueryBuilder()
            ->where("this.multimediaObjectUpdateDate > this.syncMetadataUpdate");
    }

    /**
     * Get by multimedia object update date
     * greater than sync metadata date query
     *
     * @return Query
     */
    public function getNotMetadataUpdatedQuery()
    {
        return $this->getNotMetadataUpdatedQueryBuilder()
            ->getQuery();
    }

    /**
     * Get by multimedia object update date
     * greater than sync metadata date
     *
     * @return Cursor
     */
    public function getNotMetadataUpdated()
    {
        return $this->getNotMetadataUpdatedQuery()
            ->execute();
    }

    /**
     * Get distinct ids by multimedia object update date
     * greater than sync metadata date query builder
     *
     * @return QueryBuilder
     */
    public function getDistinctIdsNotMetadataUpdatedQueryBuilder()
    {
        return $this->getNotMetadataUpdatedQueryBuilder()
            ->distinct('_id');
    }

    /**
     * Get  distinct ids by multimedia object update date
     * greater than sync metadata date query
     *
     * @return Query
     */
    public function getDistinctIdsNotMetadataUpdatedQuery()
    {
        return $this->getDistinctIdsNotMetadataUpdatedQueryBuilder()
            ->getQuery();
    }

    /**
     * Get distinct ids by multimedia object update date
     * greater than sync metadata date
     *
     * @return Cursor
     */
    public function getDistinctIdsNotMetadataUpdated()
    {
        return $this->getDistinctIdsNotMetadataUpdatedQuery()
            ->execute();
    }
}