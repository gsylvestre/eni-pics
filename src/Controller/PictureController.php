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
     * @Route("/", name="picture_home")
     */
    public function home(PictureRepository $pictureRepository, Request $request): Response
    {
        //crée une instance du formulaire de recherche (il n'est pas associé à une entité)
        $searchForm = $this->createForm(SearchPictureType::class);

        //récupère les données soumises dans la requête
        $searchForm->handleRequest($request);

        //les données du form sont là (s'il a été soumis)
        $data = $searchForm->getData();
        dump($data);

        //récupère les photos (limit à 30 ici)
        $pictures = $pictureRepository->findBy([], [], 30);

        return $this->render('picture/home.html.twig', [
            'pictures' => $pictures,
            'searchForm' => $searchForm->createView()
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

        return $this->render('picture/detail.html.twig', [
            'picture' => $picture
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
