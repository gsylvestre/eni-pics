<?php

namespace App\Controller;

use App\Form\SearchPictureType;
use App\Repository\PictureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        //récupère les photos paginée (voir la méthode dans le PictureRepository)
        //on lui passe le numéro de page, et le reste du traitement se fera là-bas
        $pictures = $pictureRepository->findPaginatedPictures($page);

        return $this->render('picture/home.html.twig', [
            'pictures' => $pictures,
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

        return $this->render('picture/detail.html.twig', [
            'picture' => $picture
        ]);
    }
}
