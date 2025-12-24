<?php

namespace App\Command;

use App\Elasticsearch\ListingIndexer;
use App\Repository\ListingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'listings:reindex',
    description: 'Reindex all listings to Elasticsearch'
)]
class ReindexListingsCommand extends Command
{
    public function __construct(
        private readonly ListingRepository $listingRepository,
        private readonly ListingIndexer $listingIndexer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('recreate', 'r', InputOption::VALUE_NONE, 'Recreate index before reindexing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Listing Reindex Command');

        try {
            if ($input->getOption('recreate')) {
                $io->info('Recreating index...');
                $this->listingIndexer->recreateIndex();
            } else {
                $this->listingIndexer->ensureIndexExists();
            }

            $io->info('Fetching listings from database...');
            $listings = $this->listingRepository->findAll();
            $total = count($listings);

            $io->info("Found {$total} listings. Starting indexing...");

            $indexed = $this->listingIndexer->bulkIndex($listings);

            $io->success("Successfully indexed {$indexed} active listings out of {$total} total.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Reindex failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

