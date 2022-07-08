<?php

namespace App\Console\Commands;

use App\Models\Attack;
use App\Models\Pokemon as ModelsPokemon;
use App\Models\Resistance;
use App\Models\RetreatCost;
use App\Models\SubType;
use App\Models\Type;
use App\Models\Weakness;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pokemon')->truncate();
        DB::table('attacks')->truncate();
        DB::table('media')->truncate();
        DB::table('pokemon_type')->truncate();
        DB::table('resistances')->truncate();
        DB::table('retreat_costs')->truncate();
        DB::table('sets')->truncate();
        DB::table('sub_types')->truncate();
        DB::table('types')->truncate();
        DB::table('weaknesses')->truncate();
        DB::table('pokemon_type')->truncate();
        DB::table('pokemon_sub_type')->truncate();
        DB::table('pokemon_retreat_cost')->truncate();

        File::deleteDirectory(public_path('media'));
        //Get how many pages we need to go through
        $this->output->info('Getting net ready!');

        // dd('truncating');
        
        // Create progress bar and print some console shiz
        $this->output->info('Looking for 15050 Pokemon');
        $bar = $this->output->createProgressBar(15050);

        // totalCount 15050
        for($i=1; 2; $i++){

            $cards = Http::withHeaders([ 'X-Api-Key' => env('POKEMON_API_KEY') ])
            ->get('https://api.pokemontcg.io/v2/cards?page='.$i.'&pageSize=250')
            ->json();

            foreach($cards['data'] as $x => $card){
                
                    $pokemon = new ModelsPokemon();
                    $pokemon->uuid = $card['id'];
                    $pokemon->name = $card['name'];
                    $pokemon->super_type = $card['supertype'];
                    $pokemon->hp = $card['hp'] ?? 0; // may need to make this nullable in the migration?
                    $pokemon->evolves_from = $card['evolvesFrom'] ?? null;
                    $pokemon->evolves_to = $card['evolvesTo'] ?? null;
                    $pokemon->converted_retreat_cost = $card['convertedRetreatCost'] ?? null;
                    $pokemon->set_number = $card['number'];
                    $pokemon->artist = $card['artist'] ?? null;
                    $pokemon->rarity = $card['rarity'] ?? null;
                    $pokemon->flavor_text = $card['flavorText'] ?? null;

                    $pokemon->save();

                    if(isset($card['subtypes'])){
                        foreach($card['subtypes'] as $subType){

                            $newSubType = SubType::where('name', $subType)->first();
                            if(!$newSubType){

                                $newSubType = new SubType();
                                $newSubType->name = $subType;
                                $newSubType->save();
                            }

                            $pokemon->subTypes()->save($newSubType);
                        }
                    }
                    if(isset($card['types'])){
                        foreach($card['types'] as $type){

                            $newType = Type::where('name', $type)->first();
                            if(!$newType){

                                $newType = new Type();
                                $newType->name = $type;
                                $newType->save();

                            }

                            $pokemon->types()->save($newType);
                        }
                    }
                    if(isset($card['attacks'])){
                        foreach($card['attacks'] as $attacks){
                            $newAttack = new Attack();
                            $newAttack->name = $attacks['name'];
                            $newAttack->cost = $attacks['cost'];
                            $newAttack->convertedEnergyCost = $attacks['convertedEnergyCost'];
                            $newAttack->damage = $attacks['damage'];
                            $newAttack->text = $attacks['text'];

                            $pokemon->attacks()->save($newAttack);
                        }
                    }
                    if(isset($card['weaknesses'])){
                        foreach($card['weaknesses'] as $weakness){
                            $newWeakness = new Weakness();
                            $newWeakness->name = $weakness['type'];
                            $newWeakness->value = $weakness['value'];

                            $pokemon->weakness()->save($newWeakness);
                        }
                    }
                    if(isset($card['resistances'])){
                        foreach($card['resistances'] as $resistance){
                            $newResistance = new Resistance();
                            $newResistance->name = $resistance['type'];
                            $newResistance->value = $resistance['value'];

                            $pokemon->resistance()->save($newResistance);
                        }
                    }
                    if(isset($card['retreatCost'])){
                        foreach($card['retreatCost'] as $retreatCost){
                            $newRetreatCost = RetreatCost::where('name', $retreatCost)->first();
                            if(!$newRetreatCost){

                                $newRetreatCost = new RetreatCost();
                                $newRetreatCost->name = $retreatCost;
                                $newRetreatCost->save();

                            }

                            $pokemon->retreatCost()->save($newRetreatCost);
                        }
                    }
                    if(isset($card['images'])){
                        foreach($card['images'] as $image){
                            $pokemon->addMediaFromUrl($image)->toMediaCollection('media');
                        }
                    }

                    /**
                     * set and pricing to think about
                     */
                    $pokemon->save();
                    $bar->advance();
    
            }
        }      
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');  

        $this->output->info('Caught them all!');

        // Pokemon::Card()->all();

        // Some varables
        // $page = 1;
        // $perPage = 1000;

        // Get how many pages we need to go through
        // $this->output->info('Getting net ready!');
        // $pagination = Pokemon::Card()->where([
        //     'set.legalities.standard' => 'legal'
        // ])->pagination();

        // // Create progress bar and print some console shiz
        // $this->output->info('Looking for '. $pagination->getTotalCount() * 1000 .' Pokemon');
        // $bar = $this->output->createProgressBar($pagination->getTotalCount() * 1000);

        // // Start catching some pokemon
        // while ($page < $pagination->getTotalCount()) {

        //     $cards = Pokemon::Card()->where([
        //         'set.legalities.standard' => 'legal'
        //     ])->page($page)->pageSize($perPage)->all();

        //     foreach($cards as $card){

        //         $card = $card->toArray();
        //         try {
        //             $pokemon = new ModelsPokemon();
        //             $pokemon->uuid = $card['id'];
        //             $pokemon->name = $card['name'];
        //             $pokemon->super_type = $card['supertype'];
        //             $pokemon->hp = $card['hp'] ?? 0; // may need to make this nullable in the migration?
        //             $pokemon->evolves_from = $card['evolvesFrom'];
        //             $pokemon->evolves_to = $card['evolvesTo'];
        //             $pokemon->converted_retreat_cost = $card['convertedRetreatCost'];
        //             $pokemon->set_number = $card['number'];
        //             $pokemon->artist = $card['artist'];
        //             $pokemon->rarity = $card['rarity'];
        //             $pokemon->flavor_text = $card['flavorText'];

        //             $pokemon->save();
        //             $bar->advance();
        //         }
        //         catch(Exception $error){

        //             $this->output->info('This pokemon fled with an error of: '.$error);
        //         }

        //     }
        //     sleep(4);

        //     $page++;
        // }

        // $this->output->info('Caught them all!');

    }
}
