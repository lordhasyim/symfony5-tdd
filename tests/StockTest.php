<?php
namespace App\Tests;

use App\Entity\Stock;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;

class StockTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        DatabasePrimer::prime($kernel);
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function teardown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testItWorks()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function a_stock_can_be_created_in_the_database()
    {
        //setup

        $stock = new Stock();
        $stock->setSymbol('AMZN');
        $stock->setShortName('Amazon Inc');
        $stock->setCurrency('USD');
        $stock->setExchangeName('Nasdaq');
        $stock->setRegion('US');
        $price = 1000;
        $previousClose = 1100;
        $priceChange = $price - $previousClose;
        $stock->setPrice($price);
        $stock->setPreviousClose($previousClose);
        $stock->setPriceChange($priceChange);

        $this->entityManager->persist($stock);

        // do something
        $this->entityManager->flush();
        $stockRepository = $this->entityManager->getRepository(Stock::class);
        $stockRecord = $stockRepository->findOneBy(['symbol' => 'AMZN']);

        //make assertion
        $this->assertEquals('Amazon Inc', $stockRecord->getShortName());
        $this->assertEquals('USD', $stockRecord->getCurrency());
        $this->assertEquals('Nasdaq', $stockRecord->getExchangeName());
        $this->assertEquals('US', $stockRecord->getRegion());
        $this->assertEquals(1000, $stockRecord->getPrice());
        $this->assertEquals(1100, $stockRecord->getPreviousClose());
        $this->assertEquals(-100, $stockRecord->getPriceChange());


        



    }

}