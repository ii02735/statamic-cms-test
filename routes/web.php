<?php

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Support\Facades\Route;
use Statamic\Contracts\Taxonomies\Term as TaxonomiesTerm;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Fields\Blueprint as FieldsBlueprint;
use Statamic\Tags\Taxonomy\Taxonomy as TaxonomyTaxonomy;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/create-taxonomy', function() {
    

    $taxonomyIdentifier = 'tags';
    $collectionIdentifier = 'articles';
    $termIdentifier = 'first-term';
    $firstEntryIdentifier = 'first-article';

    dump("create taxonomy");
    $taxonomy = Taxonomy::make($taxonomyIdentifier);
    $taxonomy->title(ucfirst($taxonomyIdentifier));
    $taxonomy->save();

    $taxonomy2 = Taxonomy::make($taxonomyIdentifier."2");
    $taxonomy2->title(ucfirst($taxonomyIdentifier."2"));
    $taxonomy2->save();

    $blueprint = $taxonomy2->termBlueprint($taxonomyIdentifier."2");
    $blueprint->fields()->addValues([$taxonomy]);
    $blueprint->save();



    dump("create term");
    $term = Term::make($termIdentifier)
                ->taxonomy($taxonomyIdentifier)
                ->inDefaultLocale()
                ->merge(['title' => 'My First Term', 'tags' => ["$termIdentifier"."proto"]]);

    $term->save();

    # Création d'une collection
    dump("create collection");
    $collection = Collection::make($collectionIdentifier)
                        ->title(ucfirst($collectionIdentifier))
                        ->routes('/articles/{slug}')
                        ->dated(true)
                        ->taxonomies([$taxonomyIdentifier]);

    $collection->save();


    # Création d'un terme

    $entry = Entry::make('my-second-entry')->collection($collectionIdentifier);

    $entry
            ->slug('my-first-entry')
            ->data(['title' => 'My first entry', 'tags' => [$termIdentifier]]);

    $entry->save();


    die('ok');
             
});

Route::get('/blueprint-taxonomy', function() {
   
    $taxonomy = Taxonomy::find('tags2');
   /**
    * @var FieldsBlueprint
    */
   $blueprint = $taxonomy->termBlueprint('tags2');
   /**
    * On récupère le contenu initial du blueprint
    * pour ensuite ajouter un nouveau champ à la structure, qui sera
    * le champ de termes de la taxonomie "taxonomy terms field"
    */
   $blueprintContent = $blueprint->contents();

   // On initialise les paramètres du champ de la structure
   $taxonomyTermFieldContent = [
    'handle' => 'taxonomy_terms_fields',
    'field' => [
            'mode' => 'default',
            'create' => true,
            'taxonomies' => [ // IMPORTANT : contient les taxonomies enfants !!!
                'tags',
                'test-tags'
            ],
            'type' => 'terms', // pour indiquer qu'on souhaite paramétrer le champ de type "taxonomy terms field",
            'icon' => 'taxonomy', // icône du champ (visible seulement côté BO),
            // Champs se trouvant dans la fenêtre d'édition du paramétrage
            'display' => 'Taxonomy Terms Field', // nom qu'on souhaite associer à notre nouveau champ (pour la représentation dans la structure)
            'handle' => 'taxonomy_terms_field',
            'listable' => 'hidden',
            'instructions_position' => 'above',
            'hide_display' => false
        ]
    ];
    
    // On doit ajouter ce champ au niveau de tabs.main.sections[0].fields

    $blueprintContent['tabs']['main']['sections'][0]['fields'][] = $taxonomyTermFieldContent;
    // On écrase le contenu initial de notre blueprint
    $blueprint->setContents($blueprintContent);
    $blueprint->save();

    dd('ok');

    /**
     * Raisonnement final :
     * On doit ajouter un champ Taxonomy Term Field Content VIDE (s'il n'existe pas) dans un premier temps (le tableau taxonomies devra être vide)
     * Par la suite, ajouter la taxonomie enfant
     */
});


Route::get('/debug-output', function() {
    echo xdebug_info();
    die;
});
function setBlueprintContents(Request $request, Blueprint $blueprint)
{
    $tabs = collect($request->tabs)->mapWithKeys(function ($tab) {
        return [array_pull($tab, 'handle') => [
            'display' => $tab['display'],
            'sections' => $this->tabSections($tab['sections']),
        ]];
    })->all();

    $blueprint
        ->setHidden($request->hidden)
        ->setContents(array_merge($blueprint->contents(), array_filter([
            'title' => $request->title,
            'tabs' => $tabs,
        ])));

    return $blueprint;
}