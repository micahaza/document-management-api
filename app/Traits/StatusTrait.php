<?php
/**
 * Created by PhpStorm.
 * User: pezo
 * Date: 2016.04.15.
 * Time: 13:36
 */

namespace App\Traits;

trait StatusTrait
{
    /**
     * @param $statusId
     * @return bool
     */
    public function getDocumentStatusById($statusId)
    {
        if(array_key_exists($statusId, config('api.document-statuses'))) {
            return config('api.document-statuses')[$statusId];
        }
        return false;
    }

    /**
     * @param $statusId
     * @return bool
     */
    public function getFileStatusById($statusId)
    {
        if(array_key_exists($statusId, config('api.file-statuses'))) {
            return config('api.file-statuses')[$statusId];
        }
        return false;
    }

    /**
     * @param $statusName
     * @return mixed
     */
    public function getDocumentStatusIdByName($statusName)
    {
        return array_search($statusName, config('api.document-statuses'));
    }

    /**
     * @param $statusName
     * @return mixed
     */
    public function getFileStatusIdByName($statusName)
    {
        return array_search($statusName, config('api.file-statuses'));
    }

    /**
     * @return mixed
     */
    public function getAllDocumentStatuses()
    {
        return config('api.document-statuses');
    }

    /**
     * @return mixed
     */
    public function getAllFileStatuses()
    {
        return config('api.file-statuses');
    }

    /**
     * @param $statusName
     * @return bool
     */
    public function isValidDocumentStatus($statusName)
    {
        return in_array($statusName, config('api.document-statuses'));
    }

    /**
     * @param $statusName
     * @return bool
     */
    public function isValidFileStatus($statusName)
    {
        return in_array($statusName, config('api.file-statuses'));
    }
}