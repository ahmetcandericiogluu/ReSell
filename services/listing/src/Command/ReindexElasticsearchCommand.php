<?php

namespace App\Command;

use App\Elasticsearch\ListingIndexer;
use App\Repository\ListingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reindex-elasticsearch',
    description: 'Recreate Elasticsearch index and reindex all listings',
)]
class ReindexElasticsearchCommand extends Command
{
    public function __construct(
        private readonly ListingIndexer $indexer,
        private readonly ListingRepository $listingRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Reindexing Elasticsearch');

        // Step 1: Recreate index with new mapping
        $io->section('Step 1: Recreating index...');
        try {
            $this->indexer->recreateIndex();
            $io->success('Index recreated successfully');
        } catch (\Exception $e) {
            $io->error('Failed to recreate index: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Step 2: Fetch all active listings
        $io->section('Step 2: Fetching listings from database...');
        $listings = $this->listingRepository->findBy([
            'status' => 'active',
            'deletedAt' => null
        ]);
        
        $total = count($listings);
        $io->info(sprintf('Found %d listings to index', $total));

        if ($total === 0) {
            $io->warning('No listings found to index');
            return Command::SUCCESS;
        }

        // Step 3: Bulk index all listings
        $io->section('Step 3: Indexing listings...');
        $io->progressStart($total);

        try {
            $indexed = $this->indexer->bulkIndex($listings);
            $io->progressFinish();
            $io->success(sprintf('Successfully indexed %d listings', $indexed));
        } catch (\Exception $e) {
            $io->error('Failed to index listings: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->success('Reindexing completed successfully!');

        return Command::SUCCESS;
    }
}

