<?php

namespace App\Controller;

use App\Form\SearchPictureType;
use App\Repository\PictureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PictureController extends AbstractController
{
    /**
     * Affiche la liste des photos et le formulaire de recherche
     * L'URL accepte un paramètre optionnel de numéro de page (égal à 1 si absent)
     *
     * @Route("/{page}", name="picture_home", requirements={"page": "\d+"}, defaults={"page": 1})
     */
    public function home(PictureRepository $pictureRepository, Request $request, int $page = 1): Response
    {
        //crée une instance du formulaire de recherche (il n'est pas associé à une entité)
        $searchForm = $this->createForm(SearchPictureType::class);

        //récupère les données soumises dans la requête
        $searchForm->handleRequest($request);

        //les données du form sont là (s'il a été soumis)
        $data = $searchForm->getData();
        dump($data);

        //récupère les photos (limit à 30 ici)
        $resultsData = $pictureRepository->search($data['keyword'], $data['minLikes'], $data['minDownloads'], $page);

        return $this->render('picture/home.html.twig', [
            'pictures' => $resultsData['results'],
            'totalResultsCount' => $resultsData['totalResultsCount'],
            'numberOfResultsPerPage' => $resultsData['numberOfResultsPerPage'],
            'searchForm' => $searchForm->createView(),
            'page' => $page //on passe la page actuelle à Twig pour nous aider à afficher un lien vers "Next page"
        ]);
    }

    /**
     * Affiche le détail d'une photo
     * @Route("/details/{id}", name="picture_detail")
     */
    public function detail(int $id, PictureRepository $pictureRepository): Response
    {
        //récupère la photo dont l'id est dans l'URL
        $picture = $pictureRepository->find($id);

        //récupère des photos similaires en fonction des tags
        $similarPictures = $pictureRepository->findSimilarPictures($picture);
        dump($similarPictures);

        return $this->render('picture/detail.html.twig', [
            'picture' => $picture,
            'similarPictures' => $similarPictures
        ]);
    }

    /**
     * Recherche d'un photographe, méthode appelée en AJAX
     * @Route("/photographer/search", name="search_photographer")
     */
    public function searchPhotographer(PictureRepository $pictureRepository, Request $request): Response
    {
        $searchedName = $request->query->get('photographer');

        $photographerNames = $pictureRepository->findPhotographer($searchedName);

        return $this->render('picture/photographer_ajax_search_results.html.twig', [
            'photographerNames' => $photographerNames,
        ]);
    }


    /**
     * Photos d'un photographe
     * @Route("/photographer/{name}", name="photographer_pictures")
     */
    public function photographerPictures(PictureRepository $pictureRepository, string $name): Response
    {
        $pictures = $pictureRepository->findBy(['photographer' => urldecode($name)]);

        return $this->render('picture/photographer_pictures.html.twig', [
            'pictures' => $pictures
        ]);
    }
}
