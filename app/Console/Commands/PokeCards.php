<?php

namespace App\Console\Commands;

use App\Models\Pokemon as ModelsPokemon;
use Exception;
use Illuminate\Console\Command;
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
        Pokemon::Options(['verify' => true]);
        Pokemon::ApiKey(env('POKEMON_API_KEY'));

        $cards = Pokemon::Card()->all();
        // dd($cards);
        foreach($cards as $card){

            $card = $card->toArray();

            // try {
                $pokemon = new ModelsPokemon();
                $pokemon->uuid = $card['id'];
                $pokemon->name = $card['name'];
                $pokemon->super_type = $card['supertype'];
                $pokemon->hp = $card['hp'];
                // $pokemon->evolves_from = $card['evolvesFrom'];
                // $pokemon->evolves_to = $card['evolvesTo'];
                $pokemon->converted_retreat_cost = $card['convertedRetreatCost'];
                $pokemon->set_number = $card['number'];
                $pokemon->artist = $card['artist'];
                $pokemon->rarity = $card['rarity'];
                $pokemon->flavor_text = $card['flavorText'];

                $pokemon->save();
            // }
            // catch(Exception $error){

            //     dd($error, $card['id']);

            // }

        }
    }
}
