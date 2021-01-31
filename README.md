# 🌼 TP Pictures 🌷

Votre collègue a commencé un site de photos puis il s'est barré. À vous de reprendre le projet.

## Préparation
Vous devez installer son projet.  Deux options :  

#### Option 1, quick and dirty [![Generic badge](https://img.shields.io/badge/level-facile-white.svg)](https://shields.io/)  

<details>

<summary>Procédure</summary>

- récupérez le .zip fourni dans Teams et dézippez le dans votre www/
- dans PHPMyAdmin, importez la bdd fournie (fichier eni_pics.sql dans le zip)

</details>

#### Option 2, IRL [![Generic badge](https://img.shields.io/badge/level-bah_facile_aussi-white.svg)](https://shields.io/)  
<details>

<summary>Procédure</summary>

Dans le terminal : 

```
cd /wamp64/www/
git clone https://github.com/gsylvestre/eni-pics.git  
cd eni-pics/  
composer install
```

Dans PHPStorm : 
Ouvrez le projet et configurez le plugin Symfony.  
Créer le fichier `.env.local` en s'inspirant du `.env`  
Y configurer la connexion à la base de donnée, puis dans le terminal : 

```
php bin/console doctrine:database:create
php bin/console app:import-data
```  

</details>  

#### Ensuite...
Naviguez vers http://localhost/eni_pics/public/ et vous devriez voir le site. Le formulaire n'est pas encore fonctionnel, c'est normal. 

Voici ci-dessous ce qu'il reste à faire... L'ordre n'est pas important, faites-en une ou plus, peu importe, vous ne pouvez pas tout faire... Toutes les tâches qui restent sont reliées à Doctrine, aux entités et aux requêtes !


## Recherche par mot-clef [![Generic badge](https://img.shields.io/badge/level-jouable-blue.svg)](https://shields.io/)


Le formulaire ne fait rien du tout, et vous devez corriger ça. Si le formulaire est soumis avec un mot-clef, seulement les photos dont le titre ou la description contiennent ce mot-clef devraient être affichées !  

Pour réaliser cette fonctionnalité, vous devez abandonner le findBy() utilisé dans le contrôleur, et développer votre propre méthode dans le PictureRepository, avec du DQL ou du QueryBuilder ! Le QueryBuilder est recommandé ici en passant.

<details>
    <summary>
        Indices ?
    </summary>

- Un bout de code du contrôleur vous montre comment récupérer le mot-clef recherché. 
- Il faut créer une méthode dans PictureRepository.php, qui `return` les résultats
- Depuis le contrôleur, cette méthode doit être appelée au lieu du findBy()
- Pour réaliser la recherche par mot-clef, il faudra utiliser un LIKE dans la requête, combiné avec des %% 
- Il est grave possible de passer le mot-clef à la fonction, si on lui ajoute un argument : on récupère le mot-clef dans le contrôleur, et on le passe au repository en appelant la méthode. 

</details>

<details>
    <summary>
        Envie d'un bonus ? 
    </summary>
  
[![Generic badge](https://img.shields.io/badge/level-pas_si_compliqué_au_fond-orange.svg)](https://shields.io/)  
Ce serait pas mal si, lorsque la personne tapait 2 mots-clefs, ces 2 mots-clefs étaient traités indépendamment l'un de l'autre... En d'autres mots, si l'on pouvait récupérer les photos reliées à l'un OU l'autre des mots, ce serait le top. 

</details>

## Tags [![Generic badge](https://img.shields.io/badge/level-jouable-blue.svg)](https://shields.io/)  

Les photos ont des tags associés. Affichez ces tags sur la page de détail de la photo. 

Puis, faites en sorte que chacun de ces tags soit un lien menant vers une nouvelle page, affichant toutes les photos associées à ce tag. 

<details>
    <summary>
        Indices ?
    </summary>

- Sur la page de détails, il n'y a pas de requête SQL à faire pour récupérer les tags :  on y a déjà accès dans l'objet de la photo dans twig, sans rien faire :) 
- Pour pouvoir faire le lien vers la page d'un tag, il faut d'abord créer la route dans un contrôleur. Cette route contiendra un paramètre d'URL variable pour l'id du tag. 
- Sur cette page de "tag", il faudra récupérer l'objet Tag pour avoir accès aux Picture associées. La requête est très simple à faire (pas de requête perso ici). 

</details>

## Tri des photos [![Generic badge](https://img.shields.io/badge/level-faisable-green.svg)](https://shields.io/)  

Ce serait sympa de laisser à l'utilisateur le choix du critère de tri des photos sur la page d'accueil... Ajoutez un champ au formulaire de recherche permettant de sélectionner si l'on souhaite trier par : 
- Nombre de downloads
- Nombre de likes 
- Date de création 

Puis, ajustez la requête SQL en fonction de ce que l'utilisateur a choisi ! 

<details>
    <summary>
        Indices ?
    </summary>

- Le champ de formulaire devrait être un ChoiceType. Vous devrez renseigner l'option 'choices' pour définir les 3 choix possible. Voir la doc Symfony sur ChoiceType. 
-  Si la requête se fait avec le findBy(), utilisez le deuxième argument pour définir le tri. 
- Si la requête se fait avec une fonction perso dans le repository, utilisez le ORDER BY en DQL ou le ->orderBy() en querybuilder.

</details>


## Photos semblables [![Generic badge](https://img.shields.io/badge/level-jouable-blue.svg)](https://shields.io/) 
 
Sur la page de détail d'une photo, ce serait assez sympa si on affichait, dans le bas de la page, quelques photos semblables. 

Pour ce faire, vous pourriez utiliser les tags de la photo principale, et ainsi récupérer une liste d'autres photos ayant le ou les mêmes tags ! 

Il y a plusieurs manières de faire ! 

<details>
    <summary>
        Indices ?
    </summary>

- Je crois qu'il est possible d'utiliser la méthode getPictures() des tags de la photo principale pour récupérer des photos ! Même pas de requête à faire, on bosse directement dans Twig ! 
- Mais si vous voulez, le code serait plus souple si, dans le contrôleur de la page détail, vous déclenchiez une nouvelle requête à la bdd pour récupérer ces photos associées, puis les passer à Twig pour affichage.
- Si vous faites cette requête, vous pourriez utiliser des WHERE combinés avec des OR pour récupérer les photos ayant l'un OU l'autre des tags !

</details>


## Pagination [![Generic badge](https://img.shields.io/badge/level-chaud-red.svg)](https://shields.io/) 
 
On ne peut pas afficher plus de 200 photos sur une même page tout en ayant une page qui s'affiche rapidement... On a donc par défaut limité les résultats à 30 photos, c'est dommage. 

Faites en sorte que l'on puisse afficher les 30 photos suivantes ! Puis les 30 suivantes, et ainsi de suite. 

Vous aurez besoin de modifier l'URL de la route de la page d'accueil, afin qu'elle contienne le numéro de "page". Puis d'ajuster la requête SQL en fonction de ce paramètre. C'est l'"offset" qui vous permettra de réaliser cette pagination.

Puis, vous devrez ajouter des liens dans Twig menant à la page suivante et à la page précédente.

<details>
    <summary>
        Indices ?
    </summary>

- Vous vous démerdez c'est classé chaud.
- Mais puisque vous êtes là, ce serait pas mal si les liens "page suivante" et "page précédente" devenaient inactifs ou disparaissaient quand il le faut ! 

</details>

## Recherche de photographe [![Generic badge](https://img.shields.io/badge/level-hardcore-black.svg)](https://shields.io/) 
 
Les photographes ne sont pas assez mis en valeur sur notre site ! 

Créez et affichez un _nouveau formulaire_ permettant de faire une recherche par nom de photographe. Le formulaire est soumis **en AJAX** pendant que l'utilisateur tape son nom (à chaque lettre), et les suggestions de résultats sont affichées sous le champ de recherche en temps réel, comme dans Google quoi. 

Chaque nom de photographe affiché est un lien menant à la page de ce photographe (qui affiche toutes ses photos).

<details>
    <summary>
        Indices ?
    </summary>

- Euh comment dire...
- En JS, vous devez écouter sur l'événement "keyup" sur le nouveau champ de recherche
- À chaque fois que l'événement se produit, vous déclenchez une requête AJAX en envoyant au serveur ce qui est écrit dans le champ
- Vous devez créer une nouvelle Route + méthode dans un contrôleur, qui sera responsable de gérer cette requête AJAX ! 
- Cette méthode devra retourner soit les photographes trouvés en JSON, soit du HTML déjà formatté pour affichage (plus facile, moins de JS à faire)
- Les photographes ne sont pas stockés dans leur propre entité, donc ça nous complique la vie ! Arrangez-vous avec ça, _dura vita sed vita_.

</details>

## Filtres, plus de filtres [![Generic badge](https://img.shields.io/badge/level-jouable-blue.svg)](https://shields.io/) 
 
**Prérequis : avoir fait la recherche par mot-clef !**  

Ajoutez un nouveau champ au formulaire de recherche de photos permettant à l'utilisateur de renseigner le nombre de "likes" minimum. Adaptez ensuite la requête SQL pour ne récupérer que les photos ayant ce nombre de likes ou plus. 

Idem pour les downloads ! Ajoutez encore un champ !

<details>
    <summary>
        Indices ?
    </summary>

- Très important : c'est toujours la même fonction de repository que vous utilisez. Vous ne faites pas une fonction par filtre. 
- Pour pouvoir faire ça, on passe tous les filtres (mots-clefs, likes, downloads, tri) à la même fonction, en argument. 
- Ensuite, avec le queryBuilder, on peut ajouter des clauses WHERE à la requête seulement SI le filtre a été renseigné par l'utilisateur. 
- Attention, les filtres doivent s'additionner les uns aux autres ! 

</details>

## D'autres idées ?
 
Venez-me voir pour les proposer, je pourrais vous indiquer leur niveau de difficulté ! 
