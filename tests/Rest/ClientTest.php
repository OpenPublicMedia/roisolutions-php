<?php
declare(strict_types=1);


namespace OpenPublicMedia\RoiSolutions\Test\Rest;

use DateTime;
use GuzzleHttp\Psr7\Response;
use OpenPublicMedia\RoiSolutions\Rest\Exception\NotFoundException;
use OpenPublicMedia\RoiSolutions\Rest\Resource\Donor;
use OpenPublicMedia\RoiSolutions\Rest\SearchResults\DonorSearchResults;
use OpenPublicMedia\RoiSolutions\Test\TestCaseBase;

/**
 * Class ClientTest
 *
 * @coversDefaultClass \OpenPublicMedia\RoiSolutions\Rest\Client
 *
 * @package OpenPublicMedia\RoiSolutions\Test
 */
class ClientTest extends TestCaseBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockHandler->append($this->jsonFixtureResponse('postLogon'));
    }

    /**
     * @covers ::getToken
     */
    public function testCacheClient(): void
    {
        $this->mockHandler->append(
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'roi_system_datetime' => (new DateTime())->format('c'),
                'utc_datetime' => (new DateTime())->format('c'),
            ]))
        );
        $this->mockHandler->append($this->jsonFixtureResponse('getDonor'));
        $result1 = $this->restClientWithCache->getDonor('1234567');
        $this->assertInstanceOf(Donor::class, $result1);
        $this->mockHandler->append($this->jsonFixtureResponse('getDonor'));
        $result2 = $this->restClientWithCache->getDonor('1234567');
        $this->assertInstanceOf(Donor::class, $result2);
        $cache_file = __DIR__  . '/../data/test.db.php';
        if (is_writable($cache_file)) {
            unlink($cache_file);
        }
    }

    public function testGetDonor(): void
    {
        $this->mockHandler->append($this->jsonFixtureResponse('getDonor'));
        $donor = $this->restClient->getDonor('1234567');
        $this->assertInstanceOf(Donor::class, $donor);
        $this->mockHandler->append($this->jsonFixtureResponse('postLogon'), $this->apiErrorResponse(404));
        $this->expectException(NotFoundException::class);
        $this->restClient->getDonor("9999999999");
    }

    public function testSearchDonors(): void {
        $this->mockHandler->append(
            $this->jsonFixtureResponse('searchDonors-1'),
            $this->jsonFixtureResponse('postLogon'),
            $this->jsonFixtureResponse('searchDonors-2')
        );
        $results = $this->restClient->searchDonors(lastName: 'Doe');
        $this->assertInstanceOf(DonorSearchResults::class, $results);
        $this->assertContainsOnlyInstancesOf(Donor::class, $results->getItems());
        $this->assertEquals(2, $results->getTotalPages());
        $this->assertEquals(22, $results->getTotalRecords());
        $this->assertEquals(1, $results->getPage());
        $this->assertCount(20, $results->getItems());
        $results->getNextPage();
        $this->assertEquals(2, $results->getPage());
        $this->assertCount(2, $results->getItems());
        $this->assertContainsOnlyInstancesOf(Donor::class, $results->getItems());
    }
}
