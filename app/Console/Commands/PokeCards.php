<?php

namespace App\Console\Commands;

use App\Models\Pokemon as ModelsPokemon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Pokemon\Pokemon;

class PokeCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:get-cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ini_set('memory_limit', -1);
        $this->output->title('Pokemon Catcher');

        // Truncate DB
        $this->output->info('Creating Pokeballs');
        DB::table('pokemon')->truncate();

        // API Keys and shizzle
        Pokemon::Options(['verify' => true]);
        Pokemon::ApiKey(env('POKEMON_API_KEY'));

        // Some varables
        $page = 1;
        $perPage = 250;

        // Get how many pages we need to go through
        $this->output->info('Getting net ready!');
        $pagination = Pokemon::Card()->where([
            'set.legalities.standard' => 'legal'
        ])->pagination();

        // Create progress bar and print some console shiz
        $this->output->info('Looking for '. $pagination->getTotalCount() * 250 .' Pokemon');
        $bar = $this->output->createProgressBar($pagination->getTotalCount() * 250);

        // Start catching some pokemon
        while ($page < $pagination->getTotalCount()) {

            $cards = Pokemon::Card()->where([
                'set.legalities.standard' => 'legal'
            ])->page($page)->pageSize($perPage)->all();

            foreach($cards as $card){

                $card = $card->toArray();
                try {
                    $pokemon = new ModelsPokemon();
                    $pokemon->uuid = $card['id'];
                    $pokemon->name = $card['name'];
                    $pokemon->super_type = $card['supertype'];
                    $pokemon->hp = $card['hp'] ?? 0; // may need to make this nullable in the migration?
                    $pokemon->evolves_from = $card['evolvesFrom'];
                    $pokemon->evolves_to = $card['evolvesTo'];
                    $pokemon->converted_retreat_cost = $card['convertedRetreatCost'];
                    $pokemon->set_number = $card['number'];
                    $pokemon->artist = $card['artist'];
                    $pokemon->rarity = $card['rarity'];
                    $pokemon->flavor_text = $card['flavorText'];

                    $pokemon->save();
                    $bar->advance();
                }
                catch(Exception $error){

                    $this->output->info('This pokemon fled with an error of: '.$error);
                }

            }

            $page++;
        }

        $this->output->info('Caught them all!');

    }
}
