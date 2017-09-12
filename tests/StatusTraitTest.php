<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Repositories\DocumentRepository;

/**
 * This trait is for translating and validating possible document and file statuses
 *
 * Class StatusTraitTest
 */
class StatusTraitTest extends TestCase
{
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->repository = new DocumentRepository();
    }

    /**
     * Test if we can get back all available document statuses
     *
     * @test
     */
    public function we_can_get_all_available_document_statuses()
    {

        $statuses = $this->repository->getAllDocumentStatuses();
        $this->assertEquals(count($statuses), 5);
    }

    /**
     * Test if we can get back all available file statuses
     *
     * @test
     */
    public function we_can_get_all_available_file_statuses()
    {

        $statuses = $this->repository->getAllFileStatuses();
        $this->assertEquals(count($statuses), 4);
    }

    /**
     * @dataProvider validDocumentStatusIdProvider
     * @test
     */
    public function we_can_get_document_status_by_id($statusId)
    {
        $statusName = $this->repository->getDocumentStatusById($statusId);
        $this->assertNotFalse(is_string($statusName));
    }

    /**
     * @dataProvider validFileStatusIdProvider
     * @test
     */
    public function we_can_get_file_status_by_id($statusId)
    {
        $statusName = $this->repository->getFileStatusById($statusId);
        $this->assertNotFalse(is_string($statusName));
    }

    /**
     * @dataProvider inValidStatusIdProvider
     * @test
     */
    public function we_can_not_get_killed_by_invalid_document_status_ids($invalidStatusId)
    {
        $statusName = $this->repository->getDocumentStatusById($invalidStatusId);
        $this->assertFalse($statusName);
    }

    /**
     * @dataProvider inValidStatusIdProvider
     * @test
     */
    public function we_can_not_get_killed_by_invalid_file_status_ids($invalidStatusId)
    {
        $statusName = $this->repository->getFileStatusById($invalidStatusId);
        $this->assertFalse($statusName);
    }

    /**
     * @dataProvider validDocumentStatusNameProvider
     * @test
     * @param $statusName
     */
    public function we_can_get_document_status_id_by_name($statusName)
    {
        $statusId = $this->repository->getDocumentStatusIdByName($statusName);
        $this->assertTrue(is_int($statusId));
    }

    /**
     * @dataProvider validFileStatusNameProvider
     * @test
     * @param $statusName
     */
    public function we_can_get_file_status_id_by_name($statusName)
    {
        $statusId = $this->repository->getFileStatusIdByName($statusName);
        $this->assertTrue(is_int($statusId));
    }

    /**
     * @dataProvider inValidStatusNameProvider
     * @test
     * @param $statusName
     */
    public function we_can_not_get_killed_by_invalid_file_status_names($statusName)
    {
        $this->assertFalse($this->repository->isValidFileStatus($statusName));
    }

    /**
     * @dataProvider inValidStatusNameProvider
     * @test
     * @param $statusName
     */
    public function we_can_not_get_killed_by_invalid_document_status_names($statusName)
    {
        $this->assertFalse($this->repository->isValidDocumentStatus($statusName));
    }

    public function validDocumentStatusIdProvider()
    {
        return [
            [1],
            [2],
            [3],
            [4],
            [5]
        ];
    }

    public function validFileStatusIdProvider()
    {
        return [
            [1],
            [2],
            [3],
            [4]
        ];
    }

    public function inValidStatusIdProvider()
    {
        return [
            [23],
            ['mama'],
            [null],
        ];
    }

    public function validDocumentStatusNameProvider()
    {
        return [
            ['processing'],
            ['approved'],
            ['rejected'],
            ['cpnn'],
            ['deactivated']
        ];
    }

    public function validFileStatusNameProvider()
    {
        return [
            ['processing'],
            ['approved'],
            ['rejected'],
            ['cpnn'],
        ];
    }

    public function inValidStatusNameProvider()
    {
        return [
            ['processingd'],
            [2],
            [new stdClass()],
            [-1],
            ["I'm sure you're not gonna find that"]
        ];
    }

}
