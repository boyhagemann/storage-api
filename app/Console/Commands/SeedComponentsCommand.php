<?php namespace App\Console\Commands;

use Helpers\ComponentHelper;
use Boyhagemann\Storage\Contracts\EntityRepository;
use Boyhagemann\Storage\Contracts\Record;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\SplFileInfo;

class SeedComponentsCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'seed:components';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Seed components";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $folder = storage_path('app/components');
        $files = File::allFiles($folder);

        /** @var \PDO $pdo */
        $pdo = App::make('pdo');

        /** @var Record $records */
        $records = App::make('records');

        /** @var EntityRepository $entities */
        $entities = App::make('entities');

        // Reset the state of the database
        $path = storage_path('app/seed.sql');
        $sql = file_get_contents($path);
        $pdo->exec($sql);

        // Get the Component entity to add the records
        $entity = $entities->get('component');

        // Sort by file and folder name.
        // We need that because the revision must look up other revisions in use
        // by the component. So order is important.
        Collection::make($files)
            ->sortBy(function(SplFileInfo $file) {
                return $file->getPathname();
            })
            ->map(function(SplFileInfo $file) use ($entity, $records) {

                try {
                    $this->info(sprintf('Seeding "%s"', $file->getFilename()));

                    // Get the contents of the file
                    $component = json_decode($file->getContents(), true);
                    $properties = $component['properties'];

                    // Find the component usages inside the component
                    $uses = ComponentHelper::getDependencies($component, $entity, $records);

                    // Create a new component record based on the JSON file
                    $records->insert($entity, [
                        '_id' => $properties['__id'],
                        'name' => $properties['__id'],
                        'label' => $properties['label'],
                        'data' => json_encode($component),
                        'uses' => json_encode($uses),
                    ]);
                }
                catch(\Exception $e) {
                    $this->error('Error: ' . $e->getMessage());
                }
            });

    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
//            ['host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on.', 'localhost'],
//            ['port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on.', 8000],
        ];
    }

}