<?php

namespace App\Command;

use App\Entity\Stock;
use App\Http\FinanceApiClientInterface;
use App\Http\YahooFinanceApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RefreshStockProfileCommand extends Command
{
    protected static $defaultName = 'app:refresh-stock-profile';
    protected static $defaultDescription = 'Retrieve a stock profile from the Yahoo finance APIU. update record in teh DB';

    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /** @var */
    private YahooFinanceApiClient $yahooFinanceApiClient;

    public function __construct(EntityManagerInterface $entityManager, YahooFinanceApiClient $yahooFinanceApiClient)
    {
        $this->entityManager = $entityManager;
        $this->yahooFinanceApiClient = $yahooFinanceApiClient;
        parent::__construct();

    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('symbol', InputArgument::REQUIRED, 'Stock Symbol e.g AMZN for Amazon')
            ->addArgument('region', InputArgument::REQUIRED, 'The region of the company e.g US for United States');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 1. ping yahoo api and grap the response (a stock profile)
        $stockProfile = $this->yahooFinanceApiClient
            ->fetchStockProfile(
                $input->getArgument('symbol'),
                $input->getArgument('region')
        );


        // 2.a. use response to update a record if exists

        // use response to create a record if it doesn't exist

        $stock = new Stock();
        $stock->setSymbol($stockProfile->symbol);
        $stock->setShortName($stockProfile->shortName);
        $stock->setCurrency($stockProfile->currency);
        $stock->setExchangeName($stockProfile->exchangeName);
        $stock->setRegion($stockProfile->region);
        $stock->setPreviousClose($stockProfile->previousClose);
        $stock->setPrice($stockProfile->price);
        $priceChange = $stockProfile->price - $stockProfile->previousclose;
        $stock->setPriceChange($priceChange);

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
